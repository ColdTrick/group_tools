<?php
/**
 * Invite users to a group
 *
 * used in /forms/groups/invite
 */

$group = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'userpicker',
	'#label' => elgg_echo('group_tools:group:invite:users:description'),
	'name' => 'non_group_members',
	'match_on' => 'non_group_members',
	'show_friends' => false,
	'options' => [
		'group_guid' => $group->guid,
	],
]);


if (elgg_is_admin_logged_in()) {
	echo elgg_view('input/checkbox', [
		'name' => 'all_users',
		'value' => 'yes',
		'label' => elgg_echo('group_tools:group:invite:users:all'),
	]);
}
