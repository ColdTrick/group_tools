<?php
/**
 * Content view of the related groups widget
 */

$widget = elgg_extract('entity', $vars);

$num_display = (int) $widget->num_display ?: 4;

echo elgg_list_entities([
	'type' => 'group',
	'limit' => $num_display,
	'relationship' => 'related_group',
	'relationship_guid' => $widget->owner_guid,
	'full_view' => false,
	'pagination' => false,
	'sort_by' => [
		'property' => 'name',
		'direction' => 'ASC',
	],
	'no_results' => elgg_echo('groups_tools:related_groups:none'),
]);
