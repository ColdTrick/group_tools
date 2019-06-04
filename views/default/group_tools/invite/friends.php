<?php
/**
 * Invite friends to a group
 *
 * used in /forms/groups/invite
 */

$user = elgg_get_logged_in_user_entity();
$friends_count = $user->getFriends(['count' => true]);

if (empty($friends_count)) {
	echo elgg_format_element('div', ['class' => 'group-tools-no-results'], elgg_echo('groups:nofriendsatall'));
	return;
}

echo elgg_view_field([
	'#type' => 'friendspicker',
	'#help' => elgg_echo('groups:invite:friends:help'),
	'name' => 'user_guid',
]);
