<?php
/**
 * Group module to show related groups
 */

$group = elgg_extract('entity', $vars);
if (!$group instanceof ElggGroup) {
	return;
}

$all_link = elgg_view('output/url', [
	'href' => elgg_generate_url('collection:group:group:related', [
		'guid' => $group->guid,
	]),
	'text' => elgg_echo('link:view:all'),
	'is_trusted' => true,
]);

$content = elgg_list_entities([
	'type' => 'group',
	'limit' => 4,
	'relationship' => 'related_group',
	'relationship_guid' => $group->guid,
	'full_view' => false,
	'sort_by' => [
		'property' => 'name',
		'direction' => 'ASC',
	],
	'no_results' => elgg_echo('groups_tools:related_groups:none'),
]);

echo elgg_view('groups/profile/module', [
	'title' => elgg_echo('widgets:group_related:name'),
	'content' => $content,
	'all_link' => $all_link,
]);
