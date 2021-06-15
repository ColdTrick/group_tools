<?php
/**
 * Show tabs on the group edit form
 */
$group = elgg_extract('entity', $vars);

if (!$group && elgg_get_plugin_setting('simple_create_form', 'group_tools') == 'yes') {
	return;
}

// load js
elgg_require_js('group_tools/group_edit_tabbed');
elgg_require_css('group_tools/group_edit_tabbed');

// make tabs
$tabs = [
	'profile' => [
		'text' => elgg_echo('group_tools:group:edit:profile'),
		'href' => '#group-tools-group-edit-profile',
		'priority' => 100,
		'selected' => true,
	],
	'access' => [
		'text' => elgg_echo('group_tools:group:edit:access'),
		'href' => '#group-tools-group-edit-access',
		'priority' => 150,
	],
];

$admin_approve = elgg_get_plugin_setting('admin_approve', 'group_tools') === 'yes';
$admin_approve = $admin_approve && !elgg_is_admin_logged_in();
$ask_reason = (bool) elgg_get_plugin_setting('creation_reason', 'group_tools');
if (empty($group) && $admin_approve && $ask_reason) {
	$tabs['reason'] = [
		'text' => elgg_echo('group_tools:group:edit:reason'),
		'href' => '#group-tools-group-edit-reason',
		'priority' => 110,
	];
}

if (group_tools_show_tools_on_edit()) {
	$tabs['tools'] = [
		'text' => elgg_echo('group_tools:group:edit:tools'),
		'href' => '#group-tools-group-edit-tools',
		'priority' => 200,
	];
}

if ($group instanceof ElggGroup) {
	$tabs['other'] = [
		'text' => elgg_echo('group_tools:group:edit:other'),
		'href' => '#other',
		'priority' => 300,
	];
}

// register menu items
foreach ($tabs as $name => $tab) {
	$tab['name'] = $name;
	
	elgg_register_menu_item('filter', $tab);
}
