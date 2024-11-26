<?php
/**
 * Elgg groups invite form
 */

$group = elgg_extract('entity', $vars);
if (!$group instanceof \ElggGroup) {
	return;
}

// invite friends
$tabs = [];

// invite site members
$tabs[] = [
	'text' => elgg_echo('group_tools:group:invite:users'),
	'content' => elgg_view('group_tools/invite/users', $vars),
];

// invite email
if ((bool) elgg_extract('invite_email', $vars)) {
	$tabs[] = [
		'text' => elgg_echo('group_tools:group:invite:email'),
		'content' => elgg_view('group_tools/invite/email', $vars),
	];
}

// invite csv
if ((bool) elgg_extract('invite_csv', $vars)) {
	$tabs[] = [
		'text' => elgg_echo('group_tools:group:invite:csv'),
		'content' => elgg_view('group_tools/invite/csv', $vars),
	];
}

if (count($tabs) === 1) {
	echo $tabs[0]['content'];
} else {
	echo elgg_view('page/components/tabs', [
		'tabs' => $tabs,
	]);
}

// optional text
echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('group_tools:group:invite:text'),
	'name' => 'comment',
	'value' => elgg_extract('comment', $vars),
]);

// renotify existing invites
if ($group->canEdit()) {
	echo elgg_view_field([
		'#type' => 'checkbox',
		'#label' => elgg_echo('groups:invite:resend'),
		'name' => 'resend',
		'value' => 1,
		'switch' => true,
	]);
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'group_guid',
	'value' => $group->guid,
]);

// show buttons
$footer_fields = [
	[
		'#type' => 'submit',
		'name' => 'submit',
		'value' => 0,
		'icon' => 'envelope',
		'text' => elgg_echo('invite'),
	],
];
if (elgg_is_admin_logged_in()) {
	$footer_fields[] = [
		'#type' => 'submit',
		'name' => 'submit',
		'value' => 1,
		'icon' => 'plus',
		'text' => elgg_echo('group_tools:add_users'),
		'data-confirm' => elgg_echo('group_tools:group:invite:add:confirm'),
	];
}

$footer = elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => $footer_fields,
]);
elgg_set_form_footer($footer);
