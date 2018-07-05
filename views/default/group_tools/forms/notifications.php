<?php
/**
 * Set notification settings of group members
 */

if (!elgg_is_admin_logged_in()) {
	return;
}

$group = elgg_extract('entity', $vars);
if (!$group instanceof \ElggGroup) {
	return;
}

$content = elgg_view_form('group_tools/admin/notifications', [], $vars);

echo elgg_view_module('info', elgg_echo('group_tools:notifications:title'), $content);
