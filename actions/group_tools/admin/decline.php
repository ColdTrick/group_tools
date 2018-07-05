<?php
/**
 * Decline a new group
 */

$group_guid = (int) get_input('guid');
elgg_entity_gatekeeper($group_guid, 'group');

$group = get_entity($group_guid);

// notify owner
$owner = $group->getOwnerEntity();

$subject = elgg_echo('group_tools:group:admin_approve:decline:subject', [$group->getDisplayName()], $owner->language);
$summary = elgg_echo('group_tools:group:admin_approve:decline:summary', [$group->getDisplayName()], $owner->language);
$message = elgg_echo('group_tools:group:admin_approve:decline:message', [
	$owner->getDisplayName(),
	$group->getDisplayName(),
], $owner->language);

$params = [
	'summary' => $summary,
];
notify_user($owner->guid, elgg_get_logged_in_user_guid(), $subject, $message, $params);

// correct forward url
$forward_url = REFERER;
if ($_SERVER['HTTP_REFERER'] === $group->getURL()) {
	$forward_url = 'groups/all';
}

// delete group
$group->delete();

// report success
return elgg_ok_response('', elgg_echo('group_tools:group:admin_approve:decline:success'), $forward_url);
