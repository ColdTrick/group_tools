<?php
/**
 * User settings for group tools
 */

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);
$user_guid = (int) elgg_extract('user_guid', $vars);

$user = get_user($user_guid);
if (!$user instanceof ElggUser || !$user->isAdmin()) {
	return;
}

if ($plugin->admin_approve !== 'yes') {
	return;
}

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:usersettings:admin:notify_approval'),
	'name' => 'params[notify_approval]',
	'default' => 0,
	'value' => 1,
	'checked' => (bool) $plugin->getUserSetting('notify_approval', $user->guid),
	'switch' => true,
]);
