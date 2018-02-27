<?php

$group_guid = (int) get_input('group_guid');
$group = get_entity($group_guid);
if (!($group instanceof ElggGroup)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$user = elgg_get_logged_in_user_entity();

$notifications_enabled = \ColdTrick\GroupTools\Membership::notificationsEnabledForGroup($user, $group);

if ($notifications_enabled) {
	// user has notifications enabled, but wishes to disable this
	$methods = elgg_get_notification_methods();
	foreach ($methods as $method) {
		elgg_remove_subscription($user->guid, $method, $group->guid);
	}
	
	return elgg_ok_response('', elgg_echo('group_tools:action:toggle_notifications:disabled', [$group->getDisplayName()]));
}

// user has no notification settings for this group and wishes to enable this
$user_settings = get_user_notification_settings($user->guid);

$supported_notifications = elgg_get_notification_methods();
$found = [];
if (!empty($user_settings)) {
	// check current user settings
	foreach ($user_settings as $method => $value) {
		if (!in_array($method, $supported_notifications)) {
			continue;
		}
		
		if (!empty($value)) {
			$found[] = $method;
		}
	}
}

// user has no base nofitication settings
if (empty($found)) {
	$found = $supported_notifications;
}

foreach ($found as $method) {
	elgg_add_subscription($user->guid, $method, $group->guid);
}

return elgg_ok_response('', elgg_echo('group_tools:action:toggle_notifications:enabled', [$group->getDisplayName()]));
