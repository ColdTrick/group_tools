<?php

namespace ColdTrick\GroupTools\Plugins;

use Elgg\Database\QueryBuilder;

/**
 * Support for the CSVExporter plugin
 */
class CSVExporter {
	
	/**
	 * Add group admins to the exportable values
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return null|array
	 */
	public static function addGroupAdminsToGroups(\Elgg\Event $event): ?array {
		$content_type = $event->getParam('type');
		if ($content_type !== 'group') {
			return null;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return null;
		}
		
		$fields = [
			elgg_echo('group_tools:csv_exporter:group_admin:name') => 'group_tools_group_admin_name',
			elgg_echo('group_tools:csv_exporter:group_admin:email') => 'group_tools_group_admin_email',
			elgg_echo('group_tools:csv_exporter:group_admin:url') => 'group_tools_group_admin_url',
		];
		
		if (!(bool) $event->getParam('readable', false)) {
			$fields = array_values($fields);
		}
		
		$return = $event->getValue();
		
		return array_merge($return, $fields);
	}
	
	/**
	 * Supply the group admin information for the export
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return null|array
	 */
	public static function exportGroupAdminsForGroups(\Elgg\Event $event): ?array {
		$return = $event->getValue();
		if (!is_null($return)) {
			// someone already provided output
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return null;
		}
		
		$group_admin_options = [
			'type' => 'user',
			'limit' => false,
			'relationship' => 'group_admin',
			'relationship_guid' => $entity->guid,
			'inverse_relationship' => true,
			'wheres' => [
				function (QueryBuilder $qb, $main_alias) use ($entity) {
					return $qb->compare("{$main_alias}.guid", '!=', $entity->owner_guid, ELGG_VALUE_GUID);
				},
			],
		];
		
		$exportable_value = $event->getParam('exportable_value');
		switch ($exportable_value) {
			case 'group_tools_group_admin_name':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = "\"{$group_admin->getDisplayName()}\"";
				}
				return $result;
				
			case 'group_tools_group_admin_email':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = $group_admin->email;
				}
				return $result;
				
			case 'group_tools_group_admin_url':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = $group_admin->getURL();
				}
				return $result;
		}
		
		return null;
	}
	
	/**
	 * Add the groups a user is admin of to the exportable values
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return null|array
	 */
	public static function addGroupAdminsToUsers(\Elgg\Event $event): ?array {
		$content_type = $event->getParam('type');
		if ($content_type !== 'user') {
			return null;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return null;
		}
		
		$postfix = elgg_echo('csv_exporter:exportable_value:group:postfix');
		
		$fields = [
			elgg_echo('group_tools:csv_exporter:user:group_admin:name') => 'group_tools_user_group_admin_name',
			elgg_echo('group_tools:csv_exporter:user:group_admin:url') => 'group_tools_user_group_admin_url',
			
			// group only values
			elgg_echo('group_tools:csv_exporter:user:group_role') . $postfix => 'group_tools_user_group_role',
		];
		
		if (!(bool) $event->getParam('readable', false)) {
			$fields = array_values($fields);
		}
		
		$return = $event->getValue();
		
		return array_merge($return, $fields);
	}
	
	/**
	 * Allow additional user values to be exported during group member export
	 *
	 * @param \Elgg\Event $event 'get_exportable_values:group', 'csv_exporter'
	 *
	 * @return null|array
	 */
	public static function allowUserGroupValues(\Elgg\Event $event): ?array {
		$content_type = $event->getParam('type');
		if ($content_type !== 'user') {
			return null;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return null;
		}
		
		$return = $event->getValue();
		
		$return[] = 'group_tools_user_group_role';
		
		return $return;
	}
	
	/**
	 * Supply the group admin information for the export
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return null|array|string
	 */
	public static function exportGroupAdminsForUsers(\Elgg\Event $event) {
		$return = $event->getValue();
		if (!is_null($return)) {
			// someone already provided output
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggUser) {
			return null;
		}
		
		$group_admin_options = [
			'type' => 'group',
			'limit' => false,
			'relationship' => 'group_admin',
			'relationship_guid' => $entity->guid,
			'wheres' => [
				function (QueryBuilder $qb, $main_alias) use ($entity) {
					return $qb->compare("{$main_alias}.guid", '!=', $entity->owner_guid, ELGG_VALUE_GUID);
				},
			],
		];
		
		$exportable_value = $event->getParam('exportable_value');
		switch ($exportable_value) {
			case 'group_tools_user_group_admin_name':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = "\"{$group_admin->getDisplayName()}\"";
				}
				return $result;
				
			case 'group_tools_user_group_admin_url':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = $group_admin->getURL();
				}
				return $result;
				
			case 'group_tools_user_group_role':
				$export = $event->getParam('csv_export');
				if (!$export instanceof \CSVExport) {
					return '';
				}
				
				$group = $export->getContainerEntity();
				if (!$group instanceof \ElggGroup) {
					return '';
				}
				
				if ($entity->guid === $group->owner_guid) {
					return 'owner';
				} elseif ($entity->hasRelationship($group->guid, 'group_admin')) {
					return 'group admin';
				}
				return 'member';
		}
		
		return null;
	}
	
	/**
	 * Add stale information to the exportable values
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return null|array
	 */
	public static function addStaleInfo(\Elgg\Event $event): ?array {
		$content_type = $event->getParam('type');
		if ($content_type !== 'group') {
			return null;
		}
		
		if ((int) elgg_get_plugin_setting('stale_timeout', 'group_tools') < 1) {
			return null;
		}
		
		$fields = [
			elgg_echo('group_tools:csv_exporter:stale_info:is_stale') => 'group_tools_stale_info_is_stale',
			elgg_echo('group_tools:csv_exporter:stale_info:timestamp') => 'group_tools_stale_info_timestamp',
			elgg_echo('group_tools:csv_exporter:stale_info:timestamp:readable') => 'group_tools_stale_info_timestamp_reabable',
		];
		
		if (!(bool) $event->getParam('readable', false)) {
			$fields = array_values($fields);
		}
		
		$return = $event->getValue();
		
		return array_merge($return, $fields);
	}
	
	/**
	 * Export stale information about the group
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return null|int|string
	 */
	public static function exportStaleInfo(\Elgg\Event $event) {
		$return = $event->getValue();
		if (!is_null($return)) {
			// someone already provided output
			return null;
		}
		
		$entity = $event->getEntityParam('entity');
		if (!$entity instanceof \ElggGroup) {
			return null;
		}
		
		$stale_info = group_tools_get_stale_info($entity);
		if (empty($stale_info)) {
			return null;
		}
		
		$exportable_value = $event->getParam('exportable_value');
		switch ($exportable_value) {
			case 'group_tools_stale_info_is_stale':
				if ($stale_info->isStale()) {
					return 'yes';
				}
				return 'no';
				
			case 'group_tools_stale_info_timestamp':
				$ts = $stale_info->getTimestamp();
				if (empty($ts)) {
					return null;
				}
				return $ts;
				
			case 'group_tools_stale_info_timestamp_reabable':
				$ts = $stale_info->getTimestamp();
				if (empty($ts)) {
					return null;
				}
				return csv_exported_get_readable_timestamp($ts);
		}
		
		return null;
	}
	
	/**
	 * Add exportable values
	 *
	 * @param \Elgg\Event $event 'get_exportable_values', 'csv_exporter'
	 *
	 * @return null|array
	 */
	public static function addApprovalReasons(\Elgg\Event $event): ?array {
		$content_type = $event->getParam('type');
		if ($content_type !== 'group' || !(bool) elgg_get_plugin_setting('creation_reason', 'group_tools')) {
			return null;
		}
		
		$readable = (bool) $event->getParam('readable', false);
		
		$fields = [
			elgg_echo('group_tools:group:admin_approve:menu') . ': ' . elgg_echo('group_tools:group:edit:reason:question') => 'group_tools_admin_approval_reason_question',
		];
		
		$return = $event->getValue();
		
		// which version did we want
		if (!$readable) {
			$fields = array_values($fields);
		}
		
		return array_merge($return, $fields);
	}
	
	/**
	 * Export a single value for a group
	 *
	 * @param \Elgg\Event $event 'export_value', 'csv_exporter'
	 *
	 * @return null|mixed
	 */
	public static function exportApprovalReasons(\Elgg\Event $event) {
		$return = $event->getValue();
		if (!is_null($return)) {
			// someone already provided output
			return null;
		}
		
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return null;
		}
		
		$get_annotation_value = function(string $name) use ($entity) {
			$annotations = $entity->getAnnotations([
				'annotation_name' => "approval_reason:{$name}",
				'limit' => 1,
			]);
			
			if (empty($annotations)) {
				return '';
			}
			
			$value = $annotations[0]->value;
			
			return unserialize($value);
		};
		
		$exportable_value = $event->getParam('exportable_value');
		switch ($exportable_value) {
			case 'group_tools_admin_approval_reason_question':
				return $get_annotation_value('question');
		}
	}
}
