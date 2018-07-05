<?php


// fix some problems with groups
$rows = [];

// check missing acl members
$missing_acl_members = group_tools_get_missing_acl_users();
$rows[] = [
	elgg_echo('group_tools:settings:fix:missing', [count($missing_acl_members)]),
	elgg_view('output/url', [
		'href' => 'action/group_tools/admin/fix_acl?fix=missing',
		'text' => elgg_echo('group_tools:settings:fix_it'),
		'class' => 'elgg-button elgg-button-action',
		'is_action' => true,
		'style' => 'white-space: nowrap;',
		'confirm' => true,
	]),
];

// check excess acl members
$excess_acl_members = group_tools_get_excess_acl_users();
$rows[] = [
	elgg_echo('group_tools:settings:fix:excess', [count($excess_acl_members)]),
	elgg_view('output/url', [
		'href' => 'action/group_tools/admin/fix_acl?fix=excess',
		'text' => elgg_echo('group_tools:settings:fix_it'),
		'class' => 'elgg-button elgg-button-action',
		'is_action' => true,
		'style' => 'white-space: nowrap;',
		'confirm' => true,
	]),
];

// check groups without acl
$wrong_groups = group_tools_get_groups_without_acl();
$rows[] = [
	elgg_echo('group_tools:settings:fix:without', [count($wrong_groups)]),
	elgg_view('output/url', [
		'href' => 'action/group_tools/admin/fix_acl?fix=without',
		'text' => elgg_echo('group_tools:settings:fix_it'),
		'class' => 'elgg-button elgg-button-action',
		'is_action' => true,
		'style' => 'white-space: nowrap;',
		'confirm' => true,
	]),
];

// fix everything at once
$rows[] = [
	elgg_echo('group_tools:settings:fix:all:description'),
	elgg_view('output/url', [
		'href' => 'action/group_tools/admin/fix_acl?fix=all',
		'text' => elgg_echo('group_tools:settings:fix:all'),
		'class' => 'elgg-button elgg-button-action',
		'is_action' => true,
		'style' => 'white-space: nowrap;',
		'confirm' => true,
	]),
];

$content = '<table class="elgg-table">';

foreach ($rows as $row) {
	$content .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
}

$content .= '</table>';
	
echo elgg_view_module('info', elgg_echo('group_tools:settings:fix:title'), $content);
