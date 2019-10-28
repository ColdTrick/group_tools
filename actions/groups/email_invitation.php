<?php
/**
 * Accept an email invitation
 */

use Elgg\Database\QueryBuilder;

$invitecode = get_input('invitecode');

$user = elgg_get_logged_in_user_entity();

if (empty($invitecode)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = group_tools_check_group_email_invitation($invitecode);

if (empty($group)) {
	return elgg_error_response(elgg_echo('group_tools:action:groups:email_invitation:error:code'));
}

if (!$group->join($user)) {
	return elgg_error_response(elgg_echo('group_tools:action:groups:email_invitation:error:join', [$group->getDisplayName()]));
}

elgg_call(ELGG_IGNORE_ACCESS, function() use ($group, $invitecode) {
	elgg_delete_annotations([
		'guid' => $group->guid,
		'annotation_name' => 'email_invitation',
		'wheres' => [
			function(QueryBuilder $qb, $main_alias) use ($invitecode) {
				$ors = [
					$qb->compare("{$main_alias}.value", '=', $invitecode, ELGG_VALUE_STRING),
					$qb->compare("{$main_alias}.value", 'like', "{$invitecode}|%", ELGG_VALUE_STRING),
				];
				
				return $qb->merge($ors, 'OR');
			},
		],
		'annotation_owner_guid' => $group->guid,
		'limit' => false,
	]);
});

return elgg_ok_response('', elgg_echo('group_tools:action:groups:email_invitation:success'), $group->getURL());
