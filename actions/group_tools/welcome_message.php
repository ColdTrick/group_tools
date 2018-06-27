<?php
/**
 * Save the group welcome message
 */

$group_guid = (int) get_input('group_guid');
$welcome_message = get_input('welcome_message');

if (empty($group_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);
if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

$check_message = trim(strip_tags($welcome_message));

if (!empty($check_message)) {
	$group->setPrivateSetting('group_tools:welcome_message', $welcome_message);
} else {
	$group->removePrivateSetting('group_tools:welcome_message');
}

return elgg_ok_response('', elgg_echo('group_tools:action:welcome_message:success'), $group->getURL());
