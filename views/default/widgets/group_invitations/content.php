<?php
/**
 * content for the open group invitations widget
 */

$user = elgg_get_logged_in_user_entity();
if (empty($user)) {
	echo elgg_view_message('notice', elgg_echo('loggedinrequired'), ['title' => false]);
	return;
}

echo elgg_call(ELGG_IGNORE_ACCESS, function() use ($user) {
	return elgg_list_relationships([
		'relationship' => 'invited',
		'relationship_guid' => $user->guid,
		'inverse_relationship' => true,
		'no_results' => elgg_echo('groups:invitations:none'),
	]);
});
