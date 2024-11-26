<?php

$group = elgg_get_page_owner_entity();

// get plugin settings
$invite_email = elgg_get_plugin_setting('invite_email', 'group_tools') === 'yes';
$invite_csv = elgg_get_plugin_setting('invite_csv', 'group_tools') === 'yes';

elgg_push_entity_breadcrumbs($group);

$content = elgg_view_form('groups/invite', [
	'id' => 'invite_to_group',
	'class' => 'mtm',
	'prevent_double_submit' => false,
	'sticky_enabled' => true,
], [
	'entity' => $group,
	'invite_email' => $invite_email,
	'invite_csv' => $invite_csv,
]);

echo elgg_view_page(elgg_echo('groups:invite:title'), [
	'content' => $content,
	'filter_id' => 'groups/invite',
	'filter_value' => 'invite',
]);
