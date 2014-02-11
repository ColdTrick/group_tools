<?php
/**
 * content of the discussions widget
 */

$widget = $vars["entity"];
$group = $widget->getOwnerEntity();
	
$topic_count = sanitise_int($widget->topic_count, false);
if (empty($topic_count)) {
	$topic_count = 4;
}

$options = array(
	"type" => "object",
	"subtype" => "groupforumtopic",
	"container_guid" => $group->getGUID(),
	"order_by" => "e.last_action desc",
	"limit" => $topic_count,
	"full_view" => false,
	"pagination" => false,
);

// prepend a quick start form
$params = $vars;
$params["embed"] = true;
echo elgg_view("widgets/start_discussion/content", $params);

// show discussion listing
$content = elgg_list_entities($options);
if (empty($content)) {
	$content = elgg_echo("discussion:none");
}

echo $content;
