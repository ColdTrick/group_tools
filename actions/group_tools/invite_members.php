<?php
/**
 * Invite users to the group
 */

$invite_members = get_input('invite_members');
$group_guid = (int) get_input('group_guid');

if (empty($group_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$group->invite_members = $invite_members;

return elgg_ok_response('', elgg_echo('admin:configuration:success'), $group->getURL());
