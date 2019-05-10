<?php

$entity = elgg_extract('entity', $vars);
if (empty($entity->guid) && elgg_is_admin_logged_in()) {
	// group create form
	return;
}

if (elgg_get_plugin_setting('admin_approve', 'group_tools') !== 'yes') {
	return;
}

if (empty($entity->guid) || ($entity->access_id === ACCESS_PRIVATE)) {
	echo elgg_view_message('notice', elgg_echo('group_tools:group:admin_approve:notice'));
}
