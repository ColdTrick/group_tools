<?php
/**
 * settings for the featured group widget
 */

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'default' => 5,
	'min' => 1,
	'max' => 10,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('widgets:featured_groups:edit:show_random_group'),
	'name' => 'params[show_random]',
	'checked' => $widget->show_random === 'yes',
	'default' => 'no',
	'value' => 'yes',
	'switch' => true,
]);
