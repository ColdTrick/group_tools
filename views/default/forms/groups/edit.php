<?php
/**
 * Group edit form
 *
 * @package ElggGroups
 */

elgg_require_js('group_tools/group_edit');

/* @var ElggGroup $entity */
$entity = elgg_extract('entity', $vars, false);
if ($entity) {
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'group_guid',
		'value' => $entity->guid,
	]);
}

$simple_create_form = false;
if (!$entity && elgg_get_plugin_setting('simple_create_form', 'group_tools') == 'yes') {
	$simple_create_form = true;
}

// context needed for input/access view
elgg_push_context('group-edit');

// build the group profile fields
$classes = ['group-tools-group-edit-section'];

$profile_section = elgg_format_element('div', [
	'id' => 'group-tools-group-edit-profile',
	'class' => $classes,
], elgg_view('groups/edit/profile', $vars));

if (!$simple_create_form) {
	$classes[] = 'hidden';
}

// build the group access options
$access_view = 'groups/edit/access';
if (!$entity && (elgg_get_plugin_setting('simple_access_tab', 'group_tools') === 'yes')) {
	$access_view = 'groups/edit/access_simplified';
}
$access_section = elgg_format_element('div', [
	'id' => 'group-tools-group-edit-access',
	'class' => $classes,
], elgg_view($access_view, $vars));

// build the group tools options
$tool_section_vars = [];
if (!group_tools_show_tools_on_edit()) {
	$tool_section_vars['class'] = ['hidden'];
}
$tools_section = elgg_format_element('div', [
	'id' => 'group-tools-group-edit-tools',
	'class' => $classes,
], elgg_view('groups/edit/tools', $vars));

if ($simple_create_form) {
	echo elgg_view_module('info', elgg_echo('group_tools:group:edit:profile'), $profile_section);
	echo elgg_view_module('info', elgg_echo('group_tools:group:edit:access'), $access_section);
	echo elgg_view_module('info', elgg_echo('group_tools:group:edit:tools'), $tools_section, $tool_section_vars);
} else {
	echo $profile_section;
	echo $access_section;
	echo $tools_section;
}

// display the save button and some additional form data
$footer = elgg_view('input/submit', ['value' => elgg_echo('save')]);

elgg_set_form_footer($footer);

elgg_pop_context();
