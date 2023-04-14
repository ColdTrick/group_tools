<?php
/**
 * A page to show (and add) related groups
 */

elgg_group_tool_gatekeeper('related_groups');

$group = elgg_get_page_owner_entity();

// build breadcrumb
elgg_push_breadcrumb(elgg_echo('groups'), elgg_generate_url('collection:group:group:all'));
elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());

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
	'sort_by' => [
		'property' => 'name',
		'direction' => 'ASC',
	],
	'no_results' => elgg_echo('groups_tools:related_groups:none'),
]);

// draw page
echo elgg_view_page(elgg_echo('group_tools:related_groups:title'), [
	'content' => $content,
	'filter_id' => 'groups/related',
	'filter_value' => 'related',
]);
