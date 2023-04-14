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

// notify owner
/* @var $owner \ElggUser */
$owner = $group->getOwnerEntity();

$url = elgg_generate_url('requests:group:group', [
	'guid' => $group->guid,
]);

$subject = elgg_echo('group_tools:join_motivation:notification:subject', [
	$user->getDisplayName(),
	$group->getDisplayName(),
], $owner->getLanguage());
$summary = elgg_echo('group_tools:join_motivation:notification:summary', [
	$user->getDisplayName(),
	$group->getDisplayName(),
], $owner->getLanguage());

$body = elgg_echo('group_tools:join_motivation:notification:body', [
	$user->getDisplayName(),
	$group->getDisplayName(),
	$motivation,
	$user->getURL(),
	$url,
], $owner->getLanguage());

$params = [
	'action' => 'membership_request',
	'object' => $group,
	'summary' => $summary,
];

// Notify group owner
notify_user($owner->guid, $user->guid, $subject, $body, $params);

return elgg_ok_response('', elgg_echo('groups:joinrequestmade'));
