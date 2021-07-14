<?php
/**
 * Configure a welcome message to send to a new member when he/she joins the group
 */

$content = '';
$content .= elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:welcome_message:description'),
]);

$help = null;
$value = '';

$group = elgg_extract('entity', $vars);
if ($group instanceof ElggGroup) {
	$help = elgg_echo('group_tools:welcome_message:explain', [
		elgg_get_logged_in_user_entity()->getDisplayName(),
		$group->getDisplayName(),
		$group->getURL(),
	]);
	
	$value = $group->getPluginSetting('group_tools', 'welcome_message');
}

$content .= elgg_view_field([
	'#type' => 'longtext',
	'#help' => $help,
	'name' => 'settings[group_tools][welcome_message]',
	'value' => $value,
]);

echo elgg_view_module('info', elgg_echo('group_tools:welcome_message:title'), $content);
