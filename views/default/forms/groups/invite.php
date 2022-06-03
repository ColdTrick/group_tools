<?php
/**
 * Elgg groups plugin
 *
 * @package ElggGroups
 */

$comment = elgg_get_sticky_value('group_invite', 'comment');
elgg_clear_sticky_form('group_invite');

$group = elgg_extract('entity', $vars, elgg_get_page_owner_entity());

// invite friends
$tabs = [];

if ((bool) elgg_extract('invite_friends', $vars)) {
	$tabs[] = [
		'text' => elgg_echo('friends'),
		'content' => elgg_view('group_tools/invite/friends', $vars),
	];
}

// invite site members
if ((bool) elgg_extract('invite', $vars)) {
	$tabs[] = [
		'text' => elgg_echo('group_tools:group:invite:users'),
		'content' => elgg_view('group_tools/invite/users', $vars),
	];
}

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
	'value' => $comment,
]);

// renotify existing invites
if ($group->canEdit()) {
	echo elgg_view_field([
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:group:invite:resend'),
		'name' => 'resend',
		'value' => 'yes',
	]);
}

echo elgg_view('input/hidden', ['name' => 'group_guid', 'value' => $group->guid]);

// show buttons
$footer_fields = [
	[
		'#type' => 'submit',
		'name' => 'submit',
		'value' => elgg_echo('invite'),
	],
];
if (elgg_is_admin_logged_in()) {
	$footer_fields[] = [
		'#type' => 'submit',
		'name' => 'submit',
		'value' => elgg_echo('group_tools:add_users'),
		'onclick' => 'return confirm("' . elgg_echo('group_tools:group:invite:add:confirm') . '");',
	];
}
$footer = elgg_view('input/fieldset', [
	'fields' => $footer_fields,
	'align' => 'horizontal',
]);
elgg_set_form_footer($footer);
