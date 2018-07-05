<?php
/**
 * A page to show (and add) related groups
 */

$group_guid = (int) elgg_extract('guid', $vars);

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);

// set page owner
elgg_set_page_owner_guid($group->getGUID());

// build breadcrumb
elgg_push_breadcrumb(elgg_echo('groups'), 'groups/all');
elgg_push_breadcrumb($group->name, $group->getURL());

$title_text = elgg_echo('group_tools:related_groups:title');
elgg_push_breadcrumb($title_text);

// page elements
$content = '';
if ($group->canEdit()) {
	$content .= elgg_view_form('group_tools/related_groups', [
		'class' => 'mbm',
	], [
		'entity' => $group,
	]);
}

$content .= elgg_list_entities([
	'type' => 'group',
	'relationship' => 'related_group',
	'relationship_guid' => $group->guid,
	'order_by_metadata' => [
		'name' => 'name',
		'direction' => 'ASC',
	],
	'no_results' => elgg_echo('groups_tools:related_groups:none'),
]);

// build page
$page_data = elgg_view_layout('content', [
	'title' => $title_text,
	'content' => $content,
	'filter' => false,
]);

// draw page
echo elgg_view_page($title_text, $page_data);
