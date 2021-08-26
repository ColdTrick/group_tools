<?php
/**
 * Apply default notification notification settings to all group members
 */

$guid = (int) get_input('group_guid');
if (empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$entity = get_entity($guid);
if (!$entity instanceof ElggGroup || !$entity->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// also apply the new settings to all group members
/* @var $members ElggBatch */
$members = $entity->getMembers([
	'limit' => false,
	'batch' => true,
]);
$methods = elgg_get_notification_methods();
$selected_methods = (array) get_input('group_tools_change_notification_settings', []);

/* @var $member ElggUser */
foreach ($members as $member) {
	foreach ($methods as $method) {
		if (in_array($method, $selected_methods)) {
			$entity->addSubscription($member->guid, $method);
		} else {
			$entity->removeSubscription($member->guid, $method);
		}
	}
}

return elgg_ok_response('', elgg_echo('save:success'), $entity->getURL());
