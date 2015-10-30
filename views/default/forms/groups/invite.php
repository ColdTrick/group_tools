<?php

/**
 * Elgg groups plugin
 *
 * @package ElggGroups
 */
elgg_require_js('forms/groups/invite');

$user = elgg_get_logged_in_user_entity();

$comment = elgg_get_sticky_value('group_invite', 'comment');
elgg_clear_sticky_form('group_invite');

$group = elgg_extract("entity", $vars, elgg_get_page_owner_entity());
$owner = $group->getOwnerEntity();

$invite_site_members = elgg_extract("invite", $vars, "no");
$invite_email = elgg_extract("invite_email", $vars, "no");
$invite_csv = elgg_extract("invite_csv", $vars, "no");

$forward_url = $group->getURL();

echo elgg_view('group_tools/invite/filter', $vars);

echo elgg_format_element('div', array(
	'id' => 'group-tools-invite-friends',
	'class' => 'group-tools-invite-form elgg-state-active',
		), elgg_view('group_tools/invite/friends', $vars));

// invite site members
if ($invite_site_members) {
	echo elgg_format_element('div', array(
	'id' => 'group-tools-invite-users',
	'class' => 'group-tools-invite-form hidden',
		), elgg_view('group_tools/invite/users', $vars));
}

// invite by email
if ($invite_email) {
	echo elgg_format_element('div', array(
	'id' => 'group-tools-invite-email',
	'class' => 'group-tools-invite-form hidden',
		), elgg_view('group_tools/invite/email', $vars));
}

// csv upload
if ($invite_csv) {
	echo elgg_format_element('div', array(
	'id' => 'group-tools-invite-csv',
	'class' => 'group-tools-invite-form hidden',
		), elgg_view('group_tools/invite/csv', $vars));
}

// comment
echo '<div>';
echo '<label>' . elgg_echo('group_tools:group:invite:text') . '</label>';
echo elgg_view('input/plaintext', array(
	'name' => 'comment',
));
echo '</div>';

// renotify existing invites
if ($group->canEdit()) {
	echo '<div>';
	echo '<label>' . elgg_view('input/checkbox', array(
		'name' => 'resend',
		'value' => 'yes',
	)) . elgg_echo('group_tools:group:invite:resend') . '</label>';
	echo '</div>';
}

if (elgg_is_admin_logged_in()) {
	echo '<div>';
	echo elgg_view('input/radio', array(
		'name' => 'invite_action',
		'value' => 'invite',
		'options' => array(
			elgg_echo('group_tools:invite:action:invite') => 'invite',
			elgg_echo('group_tools:invite:action:add') => 'add'
		)
	));
	echo '</div>';
}
echo elgg_view('input/hidden', array(
	'name' => 'forward_url',
	'value' => $forward_url
));
echo elgg_view('input/hidden', array(
	'name' => 'group_guid',
	'value' => $group->guid
));

// show buttons
echo '<div class="elgg-foot">';
echo elgg_view('input/submit', array(
	'name' => 'submit',
	'value' => elgg_echo('invite')
));
echo '</div>';
