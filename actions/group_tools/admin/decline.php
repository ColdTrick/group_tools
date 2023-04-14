<?php
/**
 * Decline a new group
 */

$group_guid = (int) get_input('guid');
$reason = get_input('reason');
$group = get_entity($group_guid);
if (!$group instanceof \ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// notify owner
/* @var $owner \ElggUser */
$owner = $group->getOwnerEntity();

$subject = elgg_echo('group_tools:group:admin_approve:decline:subject', [$group->getDisplayName()], $owner->getLanguage());
$summary = elgg_echo('group_tools:group:admin_approve:decline:summary', [$group->getDisplayName()], $owner->getLanguage());
$message = elgg_echo('group_tools:group:admin_approve:decline:message', [
	$group->getDisplayName(),
	$reason,
], $owner->getLanguage());

$params = [
	'summary' => $summary,
];
notify_user($owner->guid, elgg_get_logged_in_user_guid(), $subject, $message, $params);

// correct forward url
$forward_url = REFERRER;
if (stristr($_SERVER['HTTP_REFERER'], $group->getURL()) !== false) {
	$forward_url = elgg_generate_url('default:group:group');
}

// delete group
$group->delete();

// report success
return elgg_ok_response('', elgg_echo('group_tools:group:admin_approve:decline:success'), $forward_url);
