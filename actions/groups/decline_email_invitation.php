<?php
/**
 * Decline an email invitation
 */

$invitecode = get_input('invitecode');
if (empty($invitecode)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
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
$deleted = elgg_delete_annotations($options);
elgg_set_ignore_access($ia);

//forwarding to groups invitations page to remove invitecode query string

if (!$deleted) {
	return elgg_error_response(elgg_echo('group_tools:action:groups:decline_email_invitation:error:delete'), 'groups/invitations');
}

return elgg_ok_response('', elgg_echo('groups:invitekilled'), 'groups/invitations');
