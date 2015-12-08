<?php
/**
 * settings for the discussions widget
 */

$widget = elgg_extract('entity', $vars);

$topic_count = sanitise_int($widget->topic_count, false);
if (empty($topic_count)) {
	$topic_count = 4;
}

$status_options = array(
	'' => elgg_echo('all'),
	'open' => elgg_echo('groups:topicopen'),
	'closed' => elgg_echo('groups:topicclosed'),
);

echo "<div>";
echo elgg_echo("widget:numbertodisplay");
echo elgg_view("input/dropdown", array(
	"name" => "params[topic_count]",
	"options" => range(1, 10),
	"value" => $topic_count,
	"class" => "mls",
));
echo "</div>";

echo "<div>";
echo elgg_echo("widgets:discussion:status");
echo elgg_view("input/dropdown", array(
	"name" => "params[discussion_status]",
	"value" => $widget->discussion_status,
	"options_values" => $status_options,
	"class" => "mls",
));
echo "</div>";
