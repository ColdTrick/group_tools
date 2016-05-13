<?php

namespace ColdTrick\GroupTools;

class CSVExporter {
	
	/**
	 * Add group admins to the exportable values
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void|array
	 */
	public static function getExportableValues($hook, $type, $return_value, $params) {
		
		$content_type = elgg_extract('type', $params);
		if ($content_type !== 'group') {
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return;
		}
		
		$readable = (bool) elgg_extract('readable', $params, false);
		
		$fields = [
			elgg_echo('group_tools:csv_exporter:group_admin:name') => 'group_tools_group_admin_name',
			elgg_echo('group_tools:csv_exporter:group_admin:email') => 'group_tools_group_admin_email',
			elgg_echo('group_tools:csv_exporter:group_admin:url') => 'group_tools_group_admin_url',
		];
		
		if (!$readable) {
			$fields = array_values($fields);
		}
		
		return array_merge($return_value, $fields);
	}
	
	/**
	 * Supply the group admin information for the export
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param array  $params       supplied params
	 *
	 * @return void|array
	 */
	public static function exportGroupAdmins($hook, $type, $return_value, $params) {
		
		if (!is_null($return_value)) {
			// someone already provided output
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		if (!($entity instanceof \ElggGroup)) {
			return;
		}
		
		$group_admin_options = [
			'type' => 'user',
			'limit' => false,
			'relationship' => 'group_admin',
			'relationship_guid' => $entity->getGUID(),
			'inverse_relationship' => true,
			'wheres' => [
				"e.guid <> {$entity->getOwnerGUID()}",
			],
		];
		
		$exportable_value = elgg_extract('exportable_value', $params);
		switch ($exportable_value) {
			case 'group_tools_group_admin_name':
				$result = [];
				$batch = new \ElggBatch('elgg_get_entities_from_relationship', $group_admin_options);
				/* @var $group_admin \ElggUser */
				foreach ($batch as $group_admin) {
					$result[] = $group_admin->name;
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
}
