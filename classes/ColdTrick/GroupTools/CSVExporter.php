<?php

namespace ColdTrick\GroupTools;

class CSVExporter {
	
	/**
	 * Add group admins to the exportable values
	 *
	 * @param \Elgg\Hook $hook 'get_exportable_values', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function addGroupAdminsToGroups(\Elgg\Hook $hook) {
		
		$content_type = $hook->getParam('type');
		if ($content_type !== 'group') {
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return;
		}
		
		$fields = [
			elgg_echo('group_tools:csv_exporter:group_admin:name') => 'group_tools_group_admin_name',
			elgg_echo('group_tools:csv_exporter:group_admin:email') => 'group_tools_group_admin_email',
			elgg_echo('group_tools:csv_exporter:group_admin:url') => 'group_tools_group_admin_url',
		];
		
		if (!(bool) $hook->getParam('readable', false)) {
			$fields = array_values($fields);
		}
		
		$return = $hook->getValue();
		
		return array_merge($return, $fields);
	}
	
	/**
	 * Supply the group admin information for the export
	 *
	 * @param \Elgg\Hook $hook 'export_value', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function exportGroupAdminsForGroups(\Elgg\Hook $hook) {
		
		$return = $hook->getValue();
		if (!is_null($return)) {
			// someone already provided output
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		$group_admin_options = [
			'type' => 'user',
			'limit' => false,
			'relationship' => 'group_admin',
			'relationship_guid' => $entity->guid,
			'inverse_relationship' => true,
			'wheres' => [
				"e.guid <> {$entity->owner_guid}",
			],
		];
		
		$exportable_value = $hook->getParam('exportable_value');
		switch ($exportable_value) {
			case 'group_tools_group_admin_name':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities_from_relationship', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = "\"{$group_admin->getDisplayName()}\"";
				}
				return $result;
				break;
			case 'group_tools_group_admin_email':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities_from_relationship', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = $group_admin->email;
				}
				return $result;
				break;
			case 'group_tools_group_admin_url':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities_from_relationship', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = $group_admin->getURL();
				}
				return $result;
				break;
		}
	}
	
	/**
	 * Add the groups a user is admin of to the exportable values
	 *
	 * @param \Elgg\Hook $hook 'get_exportable_values', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function addGroupAdminsToUsers(\Elgg\Hook $hook) {
		
		$content_type = $hook->getParam('type');
		if ($content_type !== 'user') {
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return;
		}
		
		$fields = [
			elgg_echo('group_tools:csv_exporter:user:group_admin:name') => 'group_tools_user_group_admin_name',
			elgg_echo('group_tools:csv_exporter:user:group_admin:url') => 'group_tools_user_group_admin_url',
		];
		
		if (!(bool) $hook->getParam('readable', false)) {
			$fields = array_values($fields);
		}
		
		$return = $hook->getValue();
		
		return array_merge($return, $fields);
	}
	
	/**
	 * Supply the group admin information for the export
	 *
	 * @param \Elgg\Hook $hook 'export_value', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function exportGroupAdminsForUsers(\Elgg\Hook $hook) {
		
		$return = $hook->getValue();
		if (!is_null($return)) {
			// someone already provided output
			return;
		}
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggUser) {
			return;
		}
		
		$group_admin_options = [
			'type' => 'group',
			'limit' => false,
			'relationship' => 'group_admin',
			'relationship_guid' => $entity->guid,
			'wheres' => [
				"e.guid <> {$entity->owner_guid}",
			],
		];
		
		$exportable_value = $hook->getParam('exportable_value');
		switch ($exportable_value) {
			case 'group_tools_user_group_admin_name':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities_from_relationship', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = "\"{$group_admin->getDisplayName()}\"";
				}
				return $result;
				break;
			case 'group_tools_user_group_admin_url':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities_from_relationship', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = $group_admin->getURL();
				}
				return $result;
				break;
		}
	}
	
	/**
	 * Add stale information to the exportable values
	 *
	 * @param \Elgg\Hook $hook 'get_exportable_values', 'csv_exporter'
	 *
	 * @return void|array
	 */
	public static function addStaleInfo(\Elgg\Hook $hook) {
		
		$content_type = $hook->getParam('type');
		if ($content_type !== 'group') {
			return;
		}
		
		if ((int) elgg_get_plugin_setting('stale_timeout', 'group_tools') < 1) {
			return;
		}
		
		$readable = (bool) elgg_extract('readable', $params, false);
		
		$fields = [
			elgg_echo('group_tools:csv_exporter:stale_info:is_stale') => 'group_tools_stale_info_is_stale',
			elgg_echo('group_tools:csv_exporter:stale_info:timestamp') => 'group_tools_stale_info_timestamp',
			elgg_echo('group_tools:csv_exporter:stale_info:timestamp:readable') => 'group_tools_stale_info_timestamp_reabable',
		];
		
		if (!(bool) $hook->getParam('readable', false)) {
			$fields = array_values($fields);
		}
		
		$return = $hook->getValue();
		
		return array_merge($return, $fields);
	}
	
	/**
	 * Export stale information about the group
	 *
	 * @param \Elgg\Hook $hook 'export_value', 'csv_exporter'
	 *
	 * @return void|int|string
	 */
	public static function exportStaleInfo(\Elgg\Hook $hook) {
		
		$return = $hook->getValue();
		if (!is_null($return)) {
			// someone already provided output
			return;
		}
		
		$entity = $hook->getEntityParam('entity');
		if (!$entity instanceof \ElggGroup) {
			return;
		}
		
		$stale_info = group_tools_get_stale_info($entity);
		if (empty($stale_info)) {
			return;
		}
		
		$exportable_value = $hook->getParam('exportable_value');
		switch ($exportable_value) {
			case 'group_tools_stale_info_is_stale':
				if ($stale_info->isStale()) {
					return 'yes';
				}
				return 'no';
				break;
			case 'group_tools_stale_info_timestamp':
				$ts = $stale_info->getTimestamp();
				if (empty($ts)) {
					return;
				}
				return $ts;
				break;
			case 'group_tools_stale_info_timestamp_reabable':
				$ts = $stale_info->getTimestamp();
				if (empty($ts)) {
					return;
				}
				return csv_exported_get_readable_timestamp($ts);
				break;
		}
	}
}
