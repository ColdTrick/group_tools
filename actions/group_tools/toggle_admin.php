<?php
/**
 * add/remove a user as a group admin
 */

$group_guid = (int) get_input('group_guid');
$user_guid = (int) get_input('user_guid');

$group = get_entity($group_guid);
$user = get_user($user_guid);

if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

if (!$group->isMember($user) || ($group->owner_guid === $user->guid)) {
	return elgg_error_response(elgg_echo('group_tools:action:toggle_admin:error:group'));
}

if (!$user->hasRelationship($group->guid, 'group_admin')) {
	// user is admin, so remove
	if ($user->addRelationship($group->guid, 'group_admin')) {
		return elgg_ok_response('', elgg_echo('group_tools:action:toggle_admin:success:add'));
	}
	
	return elgg_error_response(elgg_echo('group_tools:action:toggle_admin:error:add'));
}

// user is not admin, so add
if ($user->removeRelationship($group->guid, 'group_admin')) {
	return elgg_ok_response('', elgg_echo('group_tools:action:toggle_admin:success:remove'));
}

return elgg_error_response(elgg_echo('group_tools:action:toggle_admin:error:remove'));
