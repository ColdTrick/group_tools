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
if (!($group instanceof ElggGroup)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$user = elgg_get_logged_in_user_entity();

// create membership request
add_entity_relationship($user->guid, 'membership_request', $group->guid);

// add motivation
$group_acl = _groups_get_group_acl($group);
$access_id = ($group_acl instanceof ElggAccessCollection) ? (int) $group_acl->id : ACCESS_LOGGED_IN;
$group->annotate('join_motivation', $motivation, $access_id, $user->guid);

// notify owner
$owner = $group->getOwnerEntity();

$url = elgg_normalize_url("groups/requests/{$group->guid}");

$subject = elgg_echo('group_tools:join_motivation:notification:subject', [
	$user->getDisplayName(),
	$group->getDisplayName(),
], $owner->language);
$summary = elgg_echo('group_tools:join_motivation:notification:summary', [
	$user->getDisplayName(),
	$group->getDisplayName(),
], $owner->language);

$body = elgg_echo('group_tools:join_motivation:notification:body', [
	$owner->getDisplayName(),
	$user->getDisplayName(),
	$group->getDisplayName(),
	$motivation,
	$user->getURL(),
	$url,
], $owner->language);

$params = [
	'action' => 'membership_request',
	'object' => $group,
	'summary' => $summary,
];

// Notify group owner
if (!notify_user($owner->guid, $user->guid, $subject, $body, $params)) {
	return elgg_error_response(elgg_echo('groups:joinrequestnotmade'));
}

return elgg_ok_response('', elgg_echo('groups:joinrequestmade'));
