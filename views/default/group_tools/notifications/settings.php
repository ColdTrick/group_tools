<?php
/**
 * Add a notification setting for admins on the notification settings page
 *
 * @uses $vars['entity'] the user for which to set the settings
 */

$user = elgg_extract('entity', $vars);
if (!$user instanceof \ElggUser || !$user->isAdmin()) {
	return;
}

if (elgg_get_plugin_setting('admin_approve', 'group_tools') !== 'yes') {
	return;
}

$params = $vars;
$params['description'] = elgg_echo('group_tools:notifications::admin:notify_approval');
$params['purpose'] = 'group_tools_group_approval';

echo elgg_view('notifications/settings/record', $params);
