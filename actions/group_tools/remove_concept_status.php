<?php

$guid = (int) get_input('guid');
$group = get_entity($guid);
if (!$group instanceof ElggGroup || !(bool) $group->is_concept) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// should the group be approved by and admin?
if (!elgg_is_admin_logged_in() && (elgg_get_plugin_setting('admin_approve', 'group_tools') === 'yes')) {
	// request approval
	unset($group->is_concept);
	
	// prevent advanced notifications from preventing the enqueing of this event
	elgg_unregister_plugin_hook_handler('enqueue', 'notification', 'ColdTrick\AdvancedNotifications\Enqueue::preventPrivateNotifications');
	
	elgg_trigger_event('admin_approval', 'group', $group);
	
	return elgg_ok_response('', elgg_echo('group_tools:action:remove_concept_status:success:approval'));
}

// make visible
$access_id = ACCESS_PUBLIC;
if (group_tools_allow_hidden_groups()) {
	$intended_access_id = $group->intended_access_id;
	if ($intended_access_id !== null) {
		$access_id = (int) $intended_access_id;
	}
	
	if ($access_id === ACCESS_PRIVATE) {
		$group_acl = _groups_get_group_acl($group);
		
		$access_id = ($group_acl instanceof ElggAccessCollection) ? (int) $group_acl->id : ACCESS_LOGGED_IN;
	}
}

// save access
$group->access_id = $access_id;
$group->save();

// unset temp access
unset($group->intended_access_id);

return elgg_ok_response('', elgg_echo('group_tools:action:remove_concept_status:success:published'));
