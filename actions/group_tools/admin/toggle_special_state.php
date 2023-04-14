<?php
/**
 * (un)Mark a group a as special
 */

$group_guid = (int) get_input('group_guid');
$state = get_input('state');

if (empty($group_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = get_entity($group_guid);
if (!$group instanceof \ElggGroup || !$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$plugin = elgg_get_plugin_from_id('group_tools');

$result = false;
$success_message = '';
$error_message = '';

switch ($state) {
	case 'suggested':
		$suggested_groups = [];
		$suggested_setting = $plugin->getSetting('suggested_groups');
		if (!empty($suggested_setting)) {
			$suggested_groups = elgg_string_to_array($suggested_setting);
		}
		
		$key = array_search($group_guid, $suggested_groups);
		if ($key !== false) {
			unset($suggested_groups[$key]);
		} else {
			$suggested_groups[] = $group_guid;
		}
		
		if (!empty($suggested_groups)) {
			$result = $plugin->setSetting('suggested_groups', implode(',', $suggested_groups));
		} else {
			$result = $plugin->unsetSetting('suggested_groups');
		}
		
		$success_message = elgg_echo('group_tools:action:toggle_special_state:suggested');
		$error_message = elgg_echo('group_tools:action:toggle_special_state:error:suggested');
		
		break;
	default:
		$error_message = elgg_echo('group_tools:action:toggle_special_state:error:state');
		break;
}

if (!$result) {
	return elgg_error_response($error_message);
}

return elgg_ok_response('', $success_message);
