<?php
/**
 * Decline an email invitation
 */

$invitecode = get_input('invitecode');
if (empty($invitecode)) {
	register_error(elgg_echo('error:missing_data'));
	forward(REFERER);
}

$options = [
	'annotation_name' => 'email_invitation',
	'wheres' => [
		"(v.string = '{$invitecode}' OR v.string LIKE '{$invitecode}|%')",
	],
	'limit' => false,
];

// ignore access in order to cleanup the invitation
$ia = elgg_set_ignore_access(true);

if (elgg_delete_annotations($options)) {
	system_message(elgg_echo('groups:invitekilled'));
} else {
	register_error(elgg_echo('group_tools:action:groups:decline_email_invitation:error:delete'));
}
// restore access
elgg_set_ignore_access($ia);

//forward to groups invitations page to remove invitecode query string
forward("groups/invitations");
