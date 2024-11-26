<?php
/**
 * Invite users to a group
 *
 * used in /forms/groups/invite
 */

$group = elgg_extract('entity', $vars);
if (!$group instanceof \ElggGroup) {
	return;
}

echo elgg_view_field([
	'#type' => 'userpicker',
	'#label' => elgg_echo('groups:invite:friends:help'),
	'name' => 'user_guid',
	'options' => [
		'item_view' => 'livesearch/user/group_invite',
		'group_guid' => $group->guid,
	],
]);

if (elgg_is_admin_logged_in()) {
	echo elgg_view_field([
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:group:invite:users:all'),
		'name' => 'all_users',
		'value' => 'yes',
		'switch' => true,
	]);
}
