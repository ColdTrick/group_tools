<?php
/**
 * Content view of the related groups widget
 */

$widget = elgg_extract("entity", $vars);

$num_display = (int) $widget->num_display;
if ($num_display < 1) {
	$num_display = 4;
}

$options = array(
	"type" => "group",
	"limit" => $num_display,
	"relationship" => "related_group",
	"relationship_guid" => $widget->getOwnerGUID(),
	"full_view" => false,
	"pagination" => false
);

$content = elgg_list_entities_from_relationship($options);
if (empty($content)) {
	$content = "<div>" . elgg_echo("groups_tools:related_groups:none") . "</div>";
}

echo $content;