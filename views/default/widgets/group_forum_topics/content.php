<?php
/**
 * content of the discussions widget
 */

$widget = elgg_extract('entity', $vars);
$group = $widget->getOwnerEntity();
	
$topic_count = sanitise_int($widget->topic_count, false);
if (empty($topic_count)) {
	$topic_count = 4;
}

$discussion_status = $widget->discussion_status;

$options = array(
	"type" => "object",
	"subtype" => "groupforumtopic",
	"container_guid" => $group->getGUID(),
	"order_by" => "e.last_action desc",
	"limit" => $topic_count,
	"full_view" => false,
	"pagination" => false,
);

switch ($discussion_status) {
	case 'open':
	case 'closed':
		$options['metadata_name_value_pairs'] = array(
		'status' => $discussion_status,
		);
		break;
	default:
		$discussion_status = '';
		break;
}

// prepend a quick start form
$params = $vars;
$params["embed"] = true;
echo elgg_view("widgets/start_discussion/content", $params);

// show discussion listing
$content = elgg_list_entities_from_metadata($options);
if (empty($content)) {
	$content = elgg_echo("discussion:none");
} else {
	
	$href = "discussion/owner/{$widget->getOwnerGUID()}";
	if (!empty($discussion_status)) {
		$href .= "/{$discussion_status}";
	}
	
	$content .= "<div class='elgg-widget-more'>";
	$content .= elgg_view("output/url", array(
		"text" => elgg_echo("widgets:discussion:more"),
		"href" => $href,
	));
	$content .= "</div>";
}

echo $content;
