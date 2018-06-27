<?php
/**
 * add/remove a user as a group admin
 */

$group_guid = (int) get_input('group_guid');
$user_guid = (int) get_input('user_guid');

elgg_entity_gatekeeper($group_guid, 'group');
elgg_entity_gatekeeper($user_guid, 'user');

$group = get_entity($group_guid);
$user = get_user($user_guid);

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

if (!$group->isMember($user) || ($group->getOwnerGUID() === $user->getGUID())) {
	return elgg_error_response(elgg_echo('group_tools:action:toggle_admin:error:group'));
}

if (!check_entity_relationship($user->getGUID(), 'group_admin', $group->getGUID())) {
	// user is admin, so remove
	if (add_entity_relationship($user->getGUID(), 'group_admin', $group->getGUID())) {
		return elgg_ok_response('', elgg_echo('group_tools:action:toggle_admin:success:add'));
	} else {
		return elgg_error_response(elgg_echo('group_tools:action:toggle_admin:error:add'));
	}
} else {
	// user is not admin, so add
	if (remove_entity_relationship($user->getGUID(), 'group_admin', $group->getGUID())) {
		return elgg_ok_response('', elgg_echo('group_tools:action:toggle_admin:success:remove'));
	} else {
		return elgg_error_response(elgg_echo('group_tools:action:toggle_admin:error:remove'));
	}
}
