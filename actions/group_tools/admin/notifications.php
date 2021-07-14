<?php
/**
 * Apply default notification notification settings to all group members
 */

$guid = (int) get_input('guid');
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
$selected_methods = group_tools_get_default_group_notification_settings($entity);

/* @var $member ElggUser */
foreach ($members as $member) {
	foreach ($methods as $method) {
		if (in_array($method, $selected_methods)) {
			elgg_add_subscription($member->guid, $method, $entity->guid);
		} else {
			elgg_remove_subscription($member->guid, $method, $entity->guid);
		}
	}
}

return elgg_ok_response('', elgg_echo('save:success'), $entity->getURL());
