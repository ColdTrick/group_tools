<?php
/**
 * settings for the discussions widget
 */

$widget = elgg_extract('entity', $vars);

$topic_count = sanitise_int($widget->topic_count, false);
if ($topic_count < 1) {
	$topic_count = 4;
}

echo '<div>';
echo elgg_echo('widget:numbertodisplay');
echo elgg_view('input/select', [
	'name' => 'params[topic_count]',
	'options' => range(1, 10),
	'value' => $topic_count,
	'class' => 'mls',
]);
echo '</div>';
