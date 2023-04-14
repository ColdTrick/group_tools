<?php
/**
 * settings for the group members widget
 */

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'default' => 5,
	'min' => 1,
]);
