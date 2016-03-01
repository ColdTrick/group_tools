<?php
/**
 * widget settings for the discussions widget
 */

$widget = elgg_extract('entity', $vars);

$discussion_count = sanitise_int($widget->discussion_count, false);
if ($discussion_count < 1) {
	$discussion_count = 5;
}

if (elgg_in_context('dashboard')) {
	
	$noyes_options = [
		'no' => elgg_echo('option:no'),
		'yes' => elgg_echo('option:yes'),
	];
	
	echo '<div>';
	echo elgg_echo('widgets:discussion:settings:group_only');
	echo elgg_view('input/select', [
		'name' => 'params[group_only]',
		'value' => $widget->group_only,
		'options_values' => $noyes_options,
		'class' => 'mls',
	]);
	echo '</div>';
	
}

echo '<div>';
echo elgg_echo('widget:numbertodisplay');
echo elgg_view('input/select', [
	'name' => 'params[discussion_count]',
	'value' => $discussion_count,
	'options' => range(1, 10),
	'class' => 'mls',
]);
echo '</div>';
