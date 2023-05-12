<?php

namespace ColdTrick\GroupTools;

use Doctrine\DBAL\Query\QueryBuilder as DBalQueryBuilder;
use Elgg\Database\QueryBuilder;
use Elgg\Values;

/**
 * Cron handler
 */
class Cron {
	
	/**
	 * Find the new stale groups and notify the owner
	 *
	 * @param \Elgg\Event $event 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function notifyStaleGroupOwners(\Elgg\Event $event): void {
		echo 'Starting GroupTools stale group owners' . PHP_EOL;
		elgg_log('Starting GroupTools stale group owners', 'NOTICE');
		
		$time = (int) $event->getParam('time', time());
		
		// get stale groups
		$groups = elgg_call(ELGG_IGNORE_ACCESS, function() use ($time) {
			return self::findStaleGroups($time);
		});
		if (empty($groups)) {
			// non found
			echo 'Done with GroupTools stale group owners' . PHP_EOL;
			elgg_log('Done with GroupTools stale group owners', 'NOTICE');
			
			return;
		}
		
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($groups) {
			// process groups
			foreach ($groups as $group) {
				$stale_info = group_tools_get_stale_info($group);
				if (empty($stale_info)) {
					// error
					continue;
				}
				
				if (!$stale_info->isStale()) {
					// not stale
					continue;
				}
				
				self::notifyStaleGroupOwner($group);
			}
		});
		
		echo 'Done with GroupTools stale group owners' . PHP_EOL;
		elgg_log('Done with GroupTools stale group owners', 'NOTICE');
	}
	
	/**
	 * Get all stale group, which became stale today
	 *
	 * @param int $ts timestamp to compare to
	 *
	 * @return \ElggGroup[]
	 */
	protected static function findStaleGroups($ts) {
		if (empty($ts)) {
			return [];
		}
		
		$stale_timeout = (int) elgg_get_plugin_setting('stale_timeout', 'group_tools');
		if ($stale_timeout < 1) {
			return [];
		}
		
		$compare_ts_upper = strtotime("-{$stale_timeout} days", $ts);
		$compare_ts_lower = strtotime('-1 day', $compare_ts_upper);
		
		$row_to_guid = function ($row) {
			return (int) $row->guid;
		};
		
		$group_guids = [];
		
		// groups created in time window
		$groups_created = elgg_get_entities([
			'type' => 'group',
			'limit' => false,
			'created_before' => $compare_ts_upper,
			'created_after' => $compare_ts_lower,
			'callback' => $row_to_guid,
		]);
		if (!empty($groups_created)) {
			$group_guids = array_merge($group_guids, $groups_created);
		}
		
		// groups with touch in time window
		$groups_touch_ts = elgg_get_entities([
			'type' => 'group',
			'limit' => false,
			'created_time_upper' => $compare_ts_upper,
			'callback' => $row_to_guid,
			'wheres' => [
				function (QueryBuilder $qb, $main_alias) use ($compare_ts_lower, $compare_ts_upper) {
					$select = $qb->subquery('metadata', 'tmd');
					$select->select('tmd.entity_guid')
						->andWhere($qb->compare('tmd.name', '=', 'group_tools_stale_touch_ts', ELGG_VALUE_STRING))
						->andWhere($qb->compare('tmd.value', '>', $compare_ts_lower, ELGG_VALUE_INTEGER))
						->andWhere($qb->compare('tmd.value', '<', $compare_ts_upper, ELGG_VALUE_INTEGER));
					
					return $qb->compare("{$main_alias}.guid", 'IN', $select->getSQL());
				},
			],
		]);
		if (!empty($groups_touch_ts)) {
			$group_guids = array_merge($group_guids, $groups_touch_ts);
		}
		
		// groups with last content in time window
		$searchable_objects = StaleInfo::getObjectSubtypes();
		$object_subtypes = [];
		foreach ($searchable_objects as $subtype) {
			switch ($subtype) {
				case 'comment':
					// don't do these yet
					break;
				default:
					$object_subtypes[] = $subtype;
					break;
			}
		}
		
		$object_subtypes = array_filter($object_subtypes);
		$object_subtypes = array_unique($object_subtypes);
		
		if (!empty($object_subtypes)) {
			$group_content_ts = elgg_get_entities([
				'type' => 'group',
				'limit' => false,
				'created_before' => $compare_ts_upper,
				'callback' => $row_to_guid,
				'wheres' => [
					function (QueryBuilder $qb, $main_alias) use ($object_subtypes, $compare_ts_lower, $compare_ts_upper) {
						$content_sub = $qb->subquery('entities');
						$content_sub->select('container_guid', 'max(time_updated) as time_updated')
							->where($qb->compare('type', '=', 'object', ELGG_VALUE_STRING))
							->andWhere($qb->compare('subtype', 'in', $object_subtypes, ELGG_VALUE_STRING))
							->groupBy('container_guid');
						
						$container_sub = new DBalQueryBuilder($qb->getConnection());
						$container_sub->select('container_guid')
							->from("({$content_sub->getSQL()})", 'content')
							->where($qb->compare('content.time_updated', '>', $compare_ts_lower, ELGG_VALUE_TIMESTAMP))
							->andWhere($qb->compare('content.time_updated', '<', $compare_ts_upper, ELGG_VALUE_TIMESTAMP));
						
						return $qb->compare("{$main_alias}.guid", 'in', $container_sub->getSQL());
					},
				],
			]);
			if (!empty($group_content_ts)) {
				$group_guids = array_merge($group_guids, $group_content_ts);
			}
		}
		
		// groups with last comments/discussion_replies in time window
		$group_comments_ts = elgg_get_entities([
			'type' => 'group',
			'limit' => false,
			'created_time_upper' => $compare_ts_upper,
			'callback' => $row_to_guid,
			'wheres' => [
				function (QueryBuilder $qb, $main_alias) use ($compare_ts_lower, $compare_ts_upper) {
					$comments_sub = $qb->subquery('entities', 're');
					$comments_sub->joinEntitiesTable('re', 'container_guid', 'inner', 'ce');
					$comments_sub->select('ce.container_guid', 'max(re.time_updated) as time_updated')
						->where($qb->compare('re.type', '=', 'object', ELGG_VALUE_STRING))
						->andWhere($qb->compare('re.subtype', '=', 'comment', ELGG_VALUE_STRING))
						->groupBy('ce.container_guid');
					
					$container_sub = new DBalQueryBuilder($qb->getConnection());
					$container_sub->select('container_guid')
						->from("({$comments_sub->getSQL()})", 'comments')
						->where($qb->compare('comments.time_updated', '>', $compare_ts_lower, ELGG_VALUE_TIMESTAMP))
						->andWhere($qb->compare('comments.time_updated', '<', $compare_ts_upper, ELGG_VALUE_TIMESTAMP));
					
					return $qb->compare("{$main_alias}.guid", 'in', $container_sub->getSQL());
				},
			],
		]);
		if (!empty($group_comments_ts)) {
			$group_guids = array_merge($group_guids, $group_comments_ts);
		}
		
		$group_guids = array_unique($group_guids);
		if (empty($group_guids)) {
			return [];
		}
		
		return elgg_get_entities([
			'type' => 'group',
			'limit' => false,
			'guids' => $group_guids,
			'batch' => true,
		]);
	}
	
	/**
	 * Notify the owner of the group
	 *
	 * @param \ElggGroup $entity the group to notify the owner of
	 *
	 * @return void
	 */
	protected static function notifyStaleGroupOwner(\ElggGroup $entity): void {
		$owner = $entity->getOwnerEntity();
		if (!$owner instanceof \ElggUser) {
			return;
		}
		
		$site = elgg_get_site_entity();
		
		$subject = elgg_echo('groups_tools:state_info:notification:subject', [$entity->getDisplayName()], $owner->getLanguage());
		$message = elgg_echo('groups_tools:state_info:notification:message', [
			$entity->getDisplayName(),
			$entity->getURL(),
		], $owner->getLanguage());
		
		$mail_params = [
			'object' => $entity,
			'action' => 'group_tools:stale',
			'summary' => elgg_echo('groups_tools:state_info:notification:summary', [$entity->getDisplayName()], $owner->getLanguage()),
		];
		
		notify_user($owner->guid, $site->guid, $subject, $message, $mail_params);
	}
	
	/**
	 * Remove the expired concept groups
	 *
	 * @param \Elgg\Event $event 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function removeExpiredConceptGroups(\Elgg\Event $event): void {
		$days = (int) elgg_get_plugin_setting('concept_groups_retention', 'group_tools');
		if ($days < 1) {
			return;
		}
		
		echo 'Starting concept group cleanup' . PHP_EOL;
		elgg_log('Starting concept group cleanup', 'NOTICE');
		
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($days) {
			/* @var $groups \ElggBatch */
			$groups = elgg_get_entities([
				'type' => 'group',
				'metadata_name_value_pairs' => [
					'name' => 'is_concept',
					'value' => true,
				],
				'access_id' => ACCESS_PRIVATE,
				'created_before' => "-{$days} days",
				'limit' => false,
				'batch' => true,
				'batch_inc_offset' => false,
			]);
			/* @var $group \ElggGroup */
			foreach ($groups as $group) {
				if (!$group->delete()) {
					$groups->reportFailure();
				}
			}
		});
		
		echo 'Done with concept group cleanup' . PHP_EOL;
		elgg_log('Done with concept group cleanup', 'NOTICE');
	}
	
	/**
	 * Notify the owners of concept groups to make their group public
	 *
	 * @param \Elgg\Event $event 'cron', 'weekly'
	 *
	 * @return void
	 */
	public static function notifyConceptGroupOwners(\Elgg\Event $event): void {
		if (!(bool) elgg_get_plugin_setting('concept_groups', 'group_tools')) {
			return;
		}
		
		$days = (int) elgg_get_plugin_setting('concept_groups_retention', 'group_tools');
		
		echo 'Starting concept group owner notification' . PHP_EOL;
		elgg_log('Starting concept group owner notification', 'NOTICE');
		
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($days) {
			$site = elgg_get_site_entity();
			$current_language = elgg()->translator->getCurrentLanguage();
			
			/* @var $groups \ElggBatch */
			$groups = elgg_get_entities([
				'type' => 'group',
				'metadata_name_value_pairs' => [
					'name' => 'is_concept',
					'value' => true,
				],
				'access_id' => ACCESS_PRIVATE,
				'limit' => false,
				'batch' => true,
			]);
			/* @var $group \ElggGroup */
			foreach ($groups as $group) {
				/* @var $owner \ElggUser */
				$owner = $group->getOwnerEntity();
				
				// setting language to make sure friendly time is in the correct language
				elgg()->translator->setCurrentLanguage($owner->getLanguage());
				
				if ($days > 0) {
					$expires = Values::normalizeTime($group->time_created);
					$expires->modify("+{$days} days");
					
					$subject = elgg_echo('group_tools:notification:concept_group:expires:subject', [$group->getDisplayName()], $owner->getLanguage());
					$message = elgg_echo('group_tools:notification:concept_group:expires:message', [
						$group->getDisplayName(),
						elgg_get_friendly_time($expires->getTimestamp()),
						$group->getURL(),
					], $owner->getLanguage());
				} else {
					$subject = elgg_echo('group_tools:notification:concept_group:subject', [$group->getDisplayName()]);
					$message = elgg_echo('group_tools:notification:concept_group:message', [
						$group->getDisplayName(),
						$group->getURL(),
					], $owner->getLanguage());
				}
				
				$params = [
					'object' => $group,
					'action' => 'concept_group_reminder',
				];
				
				notify_user($owner->guid, $site->guid, $subject, $message, $params, ['email']);
			}
			
			// restore language
			elgg()->translator->setCurrentLanguage($current_language);
		});
		
		echo 'Done with concept group owner notification' . PHP_EOL;
		elgg_log('Done with concept group owner notification', 'NOTICE');
	}
}
