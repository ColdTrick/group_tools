<?php
/**
 * Group edit form
 *
 * @package ElggGroups
 */

/* @var ElggGroup $entity */
$entity = elgg_extract('entity', $vars, false);
if ($entity) {
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'group_guid',
		'value' => $entity->getGUID(),
	]);
}

// context needed for input/access view
elgg_push_context('group-edit');

// build the group profile fields
echo elgg_format_element('div', [
	'id' => 'group-tools-group-edit-profile',
	'class' => 'group-tools-group-edit-section',
], elgg_view('groups/edit/profile', $vars));

// build the group access options
$access_view = 'groups/edit/access';
if (!$entity && (elgg_get_plugin_setting('simple_access_tab', 'group_tools') === 'yes')) {
	$access_view = 'groups/edit/access_simplified';
}
echo elgg_format_element('div', [
	'id' => 'group-tools-group-edit-access',
	'class' => 'group-tools-group-edit-section hidden',
], elgg_view($access_view, $vars));

// build the group tools options
echo elgg_format_element('div', [
	'id' => 'group-tools-group-edit-tools',
	'class' => 'group-tools-group-edit-section hidden',
], elgg_view('groups/edit/tools', $vars));

// display the save button and some additional form data
$footer = elgg_view('input/submit', ['value' => elgg_echo('save')]);

if ($entity) {
	$delete_url = 'action/groups/delete?guid=' . $entity->getGUID();
	$footer .= elgg_view('output/url', [
		'text' => elgg_echo('groups:delete'),
		'href' => $delete_url,
		'confirm' => elgg_echo('groups:deletewarning'),
		'class' => 'elgg-button elgg-button-delete float-alt',
	]);
}

elgg_set_form_footer($footer);

elgg_pop_context();

