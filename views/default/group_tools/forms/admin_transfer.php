<?php
/**
 * group owner tranfser form
 *
 * @uses $vars['entity']                    the group to transfer
 * @uses $vars['show_group_owner_transfer'] is transfer allowed to show
 */

$group = elgg_extract('entity', $vars);
$user = elgg_get_logged_in_user_entity();
$show_group_owner_transfer = (bool) elgg_extract('show_group_owner_transfer', $vars, true);

if (!$show_group_owner_transfer || !$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
	return;
}

// don't check canEdit() because group admins can do that
if ($group->owner_guid !== $user->guid && !$user->isAdmin()) {
	return;
}

// transfer owner
$owner_guid_options = [
	'#type' => 'userpicker',
	'#label' => elgg_echo('groups:owner'),
	'name' => 'owner_guid',
	'value' => $group->owner_guid,
	'placeholder' => elgg_echo('groups:owner:placeholder'),
	'limit' => 1,
	'match_on' => 'group_members',
	'show_friends' => false,
	'options' => [
		'group_guid' => $group->guid,
	],
];

if ($group->owner_guid === $user->guid) {
	$owner_guid_options['#help'] = elgg_echo('groups:owner:warning');
}

echo elgg_view_field($owner_guid_options);

// stay admin
if (group_tools_multiple_admin_enabled() && $group->owner_guid === $user->guid) {
	echo elgg_view_field([
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:admin_transfer:remain_admin'),
		'#help' => elgg_echo('group_tools:admin_transfer:remain_admin:help'),
		'#class' => 'elgg-divide-left plm',
		'name' => 'admin_transfer_remain',
		'value' => 1,
		'checked' => true,
		'switch' => true,
	]);
}
