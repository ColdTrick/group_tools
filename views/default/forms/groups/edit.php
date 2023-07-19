<?php
/**
 * Group edit form
 *
 * @uses $vars['entity'] The group being edited (empty during creation)
 */

elgg_require_js('forms/groups/edit');

/* @var ElggGroup $entity */
$entity = elgg_extract('entity', $vars, false);

// context needed for input/access view
elgg_push_context('group-edit');

// build group edit tabs
$tabs = [];

// build the group profile fields
$tabs[] = [
	'name' => 'profile',
	'priority' => 100,
	'text' => elgg_echo('groups:edit:profile'),
	'content' => elgg_view('groups/edit/profile', $vars),
];

// build the group images
$tabs[] = [
	'name' => 'images',
	'priority' => 150,
	'text' => elgg_echo('groups:edit:images'),
	'content' => elgg_view('groups/edit/images', $vars),
];

// ask for a reason to approve the group
$admin_approve = elgg_get_plugin_setting('admin_approve', 'group_tools') === 'yes';
$admin_approve = $admin_approve && !elgg_is_admin_logged_in();
$ask_reason = (bool) elgg_get_plugin_setting('creation_reason', 'group_tools');
if (empty($entity) && $admin_approve && $ask_reason) {
	$tabs[] = [
		'name' => 'reason',
		'priority' => 150,
		'text' => elgg_echo('group_tools:group:edit:reason'),
		'content' => elgg_view('groups/edit/reason', $vars)
	];
}

// build the group access options
$tabs[] = [
	'name' => 'access',
	'priority' => 200,
	'text' => elgg_echo('groups:edit:access'),
	'content' => elgg_view('groups/edit/access', $vars),
];

// build the group tools options
$tabs[] = [
	'name' => 'tools',
	'priority' => 300,
	'text' => elgg_echo('groups:edit:tools'),
	'content' => elgg_view('groups/edit/tools', $vars),
];

// build the group settings options
$settings = elgg_view('groups/edit/settings', $vars);
if (!empty($settings)) {
	$tabs[] = [
		'name' => 'settings',
		'priority' => 400,
		'text' => elgg_echo('groups:edit:settings'),
		'content' => $settings,
	];
}

// show tabs
echo elgg_view('page/components/tabs', [
	'id' => 'groups-edit',
	'tabs' => $tabs,
]);

// display the save button and some additional form data
$footer = '';
$submit_classes = ['elgg-groups-edit-footer-submit'];
if ($entity instanceof \ElggGroup) {
	echo elgg_view('input/hidden', [
		'name' => 'group_guid',
		'value' => $entity->guid,
	]);
} else {
	elgg_require_js('forms/groups/create_navigation');
	
	$footer .= elgg_view_field([
		'#type' => 'fieldset',
		'#class' => 'elgg-groups-edit-footer-navigate',
		'fields' => [
			[
				'#type' => 'button',
				'id' => 'elgg-groups-edit-footer-navigate-next',
				'icon_alt' => 'chevron-right',
				'text' => elgg_echo('next'),
				'class' => 'elgg-button-action',
			],
		],
		'align' => 'horizontal',
	]);
	$submit_classes[] = 'hidden';
}

// build form footer
// display the save button and some additional form data
$buttons = [];

if ($admin_approve && (!$entity instanceof \ElggGroup || $entity->access_id === ACCESS_PRIVATE)) {
	$buttons[] = [
		'#type' => 'submit',
		'text' => elgg_echo('group_tools:group:edit:save:approve'),
	];
} else {
	$buttons[] = [
		'#type' => 'submit',
		'text' => elgg_echo('save'),
	];
}

if ((bool) elgg_get_plugin_setting('concept_groups', 'group_tools') && (!$entity instanceof \ElggGroup || (bool) $entity->is_concept)) {
	$buttons[] = [
		'#type' => 'submit',
		'name' => 'concept_group',
		'value' => 1,
		'text' => elgg_echo('group_tools:group:edit:save:concept'),
	];
}

$footer .= elgg_view_field([
	'#type' => 'fieldset',
	'#class' => $submit_classes,
	'fields' => $buttons,
	'align' => 'horizontal',
]);

elgg_set_form_footer($footer);

elgg_pop_context();
