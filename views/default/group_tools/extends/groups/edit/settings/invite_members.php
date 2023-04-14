<?php
/**
 * who can invite group members
 */

$setting = elgg_get_plugin_setting('invite_members', 'group_tools');
if (!in_array($setting, ['yes_off', 'yes_on'])) {
	// plugin settings don't allow this
	return;
}

$invite_members = $setting === 'yes_off' ? 'no' : 'yes';
$group = elgg_extract('entity', $vars);
if ($group instanceof \ElggGroup) {
	$invite_members = $group->getPluginSetting('group_tools', 'invite_members', $invite_members);
}

// build form
$content = elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('group_tools:invite_members:description'),
	'#help' => elgg_echo('group_tools:invite_members:disclaimer'),
	'name' => 'settings[group_tools][invite_members]',
	'value' => $invite_members,
	'options_values' => [
		'no' => elgg_echo('option:no'),
		'yes' => elgg_echo('option:yes'),
	],
]);

// draw content
echo elgg_view_module('info', elgg_echo('group_tools:invite_members:title'), $content);
