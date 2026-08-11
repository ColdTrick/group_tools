<?php
/**
 * Allow a group owner to configure if other group admins can assign group admins
 */

if (!group_tools_multiple_admin_enabled()) {
	return;
}

$value = false;
$group = elgg_extract('entity', $vars);
if ($group instanceof \ElggGroup) {
	if ($group->owner_guid !== elgg_get_logged_in_user_guid() && !elgg_is_admin_logged_in()) {
		// only the group owner is allowed to configure this
		return;
	}
	
	$value = (bool) $group->getPluginSetting('group_tools', 'assign_group_admins', $value);
}

$content = elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:edit:group:settings:group_admins:description'),
]);

$content .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:edit:group:settings:group_admins:assign'),
	'name' => 'settings[group_tools][assign_group_admins]',
	'value' => $value,
]);

echo elgg_view_module('info', elgg_echo('group_tools:edit:group:settings:group_admins:title'), $content);
