<?php
/**
 * content of the group members widget
 */

/** @var \ElggWidget $widget */
$widget = elgg_extract('entity', $vars);

$count = (int) $widget->num_display ?: 5;

echo elgg_list_entities([
	'type' => 'user',
	'limit' => $count,
	'relationship' => 'member',
	'relationship_guid' => $widget->owner_guid,
	'inverse_relationship' => true,
	'sort_by' => [
		'property_type' => 'relationship',
		'direction' => 'desc',
		'inverse_relationship' => true,
	],
	'list_type' => 'gallery',
	'gallery_class' => 'elgg-gallery-users',
	'pagination' => false,
	'no_results' => elgg_echo('widgets:group_members:view:no_members'),
	'widget_more' => elgg_view_url($widget->getURL(), elgg_echo('groups:members:more')),
]);
