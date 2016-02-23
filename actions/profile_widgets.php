<?php
/**
 * save setting to show widgets on closed groups
 */

$group_guid = (int) get_input('group_guid');
$profile_widgets = get_input('profile_widgets', 'no');

if (empty($group_guid)) {
	register_error(elgg_echo('error:missing_data'));
	forward(REFERER);
}

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);

if (!$group->canEdit()) {
	register_error(elgg_echo('actionunauthorized'));
	forward(REFERER);
}

$group->profile_widgets = $profile_widgets;
if ($group->save()) {
	system_message(elgg_echo('group_tools:action:success'));
	forward($group->getURL());
} else {
	register_error(elgg_echo('group_tools:action:error:save'));
}

forward(REFERER);
