<?php
/**
 * Decline an email invitation
 */

use Elgg\Database\QueryBuilder;

$invitecode = get_input('invitecode');
if (empty($invitecode)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

// ignore access in order to cleanup the invitation
$deleted = elgg_call(ELGG_IGNORE_ACCESS, function() use ($invitecode) {
	return elgg_delete_annotations([
		'annotation_name' => 'email_invitation',
		'wheres' => [
			function(QueryBuilder $qb, $main_alias) use ($invitecode) {
				$ors = [
					$qb->compare("{$main_alias}.string", '=', $invitecode, ELGG_VALUE_STRING),
					$qb->compare("{$main_alias}.string", 'like', "{$invitecode}|%", ELGG_VALUE_STRING),
				];
				
				return $qb->merge($ors, 'OR');
			},
		],
		'limit' => false,
	]);
});

$forward_url = elgg_generate_url('collection:group:group:invitations', [
	'username' => elgg_get_logged_in_user_entity()->username,
]);

//forwarding to groups invitations page to remove invitecode query string
if (!$deleted) {
	return elgg_error_response(elgg_echo('group_tools:action:groups:decline_email_invitation:error:delete'), $forward_url);
}

return elgg_ok_response('', elgg_echo('groups:invitekilled'), $forward_url);
