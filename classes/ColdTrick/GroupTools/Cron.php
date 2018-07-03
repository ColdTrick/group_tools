<?php

namespace ColdTrick\GroupTools;

use Elgg\Database\QueryBuilder;
use Elgg\Database\Select;

class Cron {
	
	/**
	 * Find the new stale groups and notify the owner
	 *
	 * @param \Elgg\Hook $hook 'cron', 'daily'
	 *
	 * @return void
	 */
	public static function notifyStaleGroupOwners(\Elgg\Hook $hook) {
		
		echo 'Starting GroupTools stale group owners' . PHP_EOL;
		elgg_log('Starting GroupTools stale group owners', 'NOTICE');
		
		$time = (int) $hook->getParam('time', time());
		
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
	 * @return false|\ElggGroup[]
	 * @todo revisit subquery
	 */
	protected static function findStaleGroups($ts) {
		
		if (empty($ts)) {
			return false;
		}
		
		$stale_timeout = (int) elgg_get_plugin_setting('stale_timeout', 'group_tools');
		if ($stale_timeout < 1) {
			return false;
		}
		
		$compare_ts_upper = strtotime("-{$stale_timeout} days", $ts);
		$compare_ts_lower = strtotime("-1 day", $compare_ts_upper);
		
		$dbprefix = elgg_get_config('dbprefix');
		
		$row_to_guid = function ($row) {
			return (int) $row->guid;
		};
		
		$group_guids = [];
		
		// groups created in timespace
		$options = [
			'type' => 'group',
			'limit' => false,
			'created_before' => $compare_ts_upper,
			'created_after' => $compare_ts_lower,
			'callback' => $row_to_guid,
		];
		$groups_created = elgg_get_entities($options);
		if (!empty($groups_created)) {
			$group_guids = array_merge($group_guids, $groups_created);
		}
		
		// groups with touch in timespace
		$options = [
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
					
					return $qb->compare("{$main_alias}.guid", 'IN', $select);
				},
			],
		];
		
		$groups_touch_ts = elgg_get_entities($options);
		if (!empty($groups_touch_ts)) {
			$group_guids = array_merge($group_guids, $groups_touch_ts);
		}
		
		// groups with last content in timespace
		$searchable_objects = StaleInfo::getObjectSubtypes();
		$object_subtypes = [];
		foreach ($searchable_objects as $index => $subtype) {
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
			$options = [
				'type' => 'group',
				'limit' => false,
				'created_before' => $compare_ts_upper,
				'callback' => $row_to_guid,
				'wheres' => [
					"e.guid IN (
						SELECT container_guid
						FROM (
							SELECT container_guid, max(time_updated) as time_updated
							FROM {$dbprefix}entities
							WHERE type = 'object'
							AND subtype IN ('" . implode("', '", $object_subtypes) . "')
							GROUP BY container_guid
						) as content
						WHERE content.time_updated > {$compare_ts_lower}
						AND content.time_updated < {$compare_ts_upper}
					)",
				],
			];
			
			$group_content_ts = elgg_get_entities($options);
			if (!empty($group_content_ts)) {
				$group_guids = array_merge($group_guids, $group_content_ts);
			}
		}
		
		// groups with last comments/discussion_replies in timespace
		$options = [
			'type' => 'group',
			'limit' => false,
			'created_time_upper' => $compare_ts_upper,
			'callback' => $row_to_guid,
			'wheres' => [
				"e.guid IN (
					SELECT container_guid
					FROM (
						SELECT ce.container_guid, max(re.time_updated) as time_updated
						FROM {$dbprefix}entities re
						JOIN {$dbprefix}entities ce ON re.container_guid = ce.guid
						WHERE re.type = 'object'
						AND re.subtype = 'comment'
						GROUP BY ce.container_guid
					) as comments
					WHERE comments.time_updated > {$compare_ts_lower}
					AND comments.time_updated < {$compare_ts_upper}
				)",
			],
		];
		
		$group_comments_ts = elgg_get_entities($options);
		if (!empty($group_comments_ts)) {
			$group_guids = array_merge($group_guids, $group_comments_ts);
		}
		
		$group_guids = array_unique($group_guids);
		if (empty($group_guids)) {
			return false;
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
	 * @param \ElggGroup $entity
	 *
	 * @return void
	 */
	protected static function notifyStaleGroupOwner(\ElggGroup $entity) {
		
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		$owner = $entity->getOwnerEntity();
		if (!$owner instanceof \ElggUser) {
			return;
		}
		
		$site = elgg_get_site_entity();
		
		$subject = elgg_echo('groups_tools:state_info:notification:subject', [$entity->getDisplayName()]);
		$message = elgg_echo('groups_tools:state_info:notification:message', [
			$owner->getDisplayName(),
			$entity->getDisplayName(),
			$entity->getURL(),
		]);
		
		$mail_params = [
			'object' => $entity,
			'action' => 'group_tools:stale',
			'summary' => elgg_echo('groups_tools:state_info:notification:summary', [$entity->getDisplayName()]),
		];
		
		notify_user($owner->guid, $site->guid, $subject, $message, $mail_params);
	}
}
