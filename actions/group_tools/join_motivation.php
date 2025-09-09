<?php
/**
 * Join a group with motivation provided
 *
 */

$group_guid = (int) get_input('group_guid');
$motivation = get_input('motivation');

if (empty($group_guid) || empty($motivation)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = get_entity($group_guid);
if (!$group instanceof \ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$user = elgg_get_logged_in_user_entity();

// create membership request
$user->addRelationship($group->guid, 'membership_request');

// add motivation
$group_acl = $group->getOwnedAccessCollection('group_acl');
$access_id = ($group_acl instanceof \ElggAccessCollection) ? (int) $group_acl->id : ACCESS_LOGGED_IN;
$group->annotate('join_motivation', $motivation, $access_id, $user->guid);

return elgg_ok_response('', elgg_echo('groups:joinrequestmade'));
