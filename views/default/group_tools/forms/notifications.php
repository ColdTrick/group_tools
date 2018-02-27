<?php
/**
 * Set notification settings of group members
 */

$user = elgg_get_logged_in_user_entity();
if (empty($user) || !$user->isAdmin()) {
	// only site admins can do this
	return;
}

$group = elgg_extract('entity', $vars);
if (!($group instanceof ElggGroup)) {
	return;
}

// start building content
$title = elgg_echo('group_tools:notifications:title');

// notification settings
$content = elgg_view_form('group_tools/admin/notifications', [], [
	'entity' => $group,
]);

// echo content
echo elgg_view_module('info', $title, $content);
