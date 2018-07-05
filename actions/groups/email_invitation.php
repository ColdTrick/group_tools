<?php
/**
 * Accept an email invitation
 */

$invitecode = get_input('invitecode');

$user = elgg_get_logged_in_user_entity();

if (empty($invitecode)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = group_tools_check_group_email_invitation($invitecode);

if (empty($group)) {
	return elgg_error_response(elgg_echo('group_tools:action:groups:email_invitation:error:code'), "groups/invitations/{$user->username}");
}

if (!groups_join_group($group, $user)) {
	return elgg_error_response(elgg_echo('group_tools:action:groups:email_invitation:error:join', [$group->getDisplayName()]), "groups/invitations/{$user->username}");
}

$invitecode = sanitise_string($invitecode);

$annotations = elgg_get_annotations([
	'guid' => $group->guid,
	'annotation_name' => 'email_invitation',
	'wheres' => [
		"(v.string = '{$invitecode}' OR v.string LIKE '{$invitecode}|%')",
	],
	'annotation_owner_guid' => $group->guid,
	'limit' => 1,
]);

if (!empty($annotations)) {
	$ia = elgg_set_ignore_access(true);
	$annotations[0]->delete();
	elgg_set_ignore_access($ia);
}

return elgg_ok_response('', elgg_echo('group_tools:action:groups:email_invitation:success'), $group->getURL());
