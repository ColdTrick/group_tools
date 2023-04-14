<?php
/**
 * content of the group members widget
 */

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

$count = (int) $widget->num_display ?: 5;

echo elgg_list_entities([
	'type' => 'user',
	'limit' => $count,
	'relationship' => 'member',
	'relationship_guid' => $widget->owner_guid,
	'inverse_relationship' => true,
	'list_type' => 'gallery',
	'gallery_class' => 'elgg-gallery-users',
	'pagination' => false,
	'no_results' => elgg_echo('widgets:group_members:view:no_members'),
]);
