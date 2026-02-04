<?php
/**
 * Approve a new group for use
 */

$group_guid = (int) get_input('guid');

$group = get_entity($group_guid);
if (!$group instanceof \ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// get access_id
$access_id = ACCESS_PUBLIC;
if (group_tools_allow_hidden_groups()) {
	$intended_access_id = $group->intended_access_id;
	if ($intended_access_id !== null) {
		$access_id = (int) $intended_access_id;
	}
	
	if ($access_id === ACCESS_PRIVATE) {
		$group_acl = $group->getOwnedAccessCollection('group_acl');
		
		$access_id = ($group_acl instanceof \ElggAccessCollection) ? (int) $group_acl->id : ACCESS_LOGGED_IN;
	}
}

// save access
$group->access_id = $access_id;
$group->save();

// unset temp access
unset($group->intended_access_id);
unset($group->admin_approval);

// notify owner
/** @var \ElggUser $owner */
$owner = $group->getOwnerEntity();

$owner->notify('approve', $group, [], elgg_get_logged_in_user_entity());

// report success
return elgg_ok_response('', elgg_echo('group_tools:group:admin_approve:approve:success'));
