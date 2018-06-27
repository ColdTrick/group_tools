<?php
/**
 * Enable or disable group notifications for all members
 */

$guid = (int) get_input('guid');
if (empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = get_entity($guid);
if (!$group instanceof ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// get group members
$members_count = $group->getMembers([
	'count' => true,
]);
if (empty($members_count)) {
	return elgg_ok_response('', '', $group->getURL());
}

/* @var $members ElggBatch */
$members = $group->getMembers([
	'limit' => false,
	'batch' => true,
]);

$methods = elgg_get_notification_methods();
// disable notification for everyone
/* @var $member ElggUser */
foreach ($members as $member) {
	foreach ($methods as $method) {
		elgg_remove_subscription($member->guid, $method, $group->guid);
	}
}

return elgg_ok_response('', elgg_echo('group_tools:action:notifications:success:disable'), $group->getURL());
