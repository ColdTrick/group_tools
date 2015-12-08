<?php
/**
 * content of the discussions widget
 */
$widget = elgg_extract('entity', $vars);

$discussion_count = sanitise_int($widget->discussion_count, false);
if (empty($discussion_count)) {
	$discussion_count = 5;
}

$discussion_status = $widget->discussion_status;

$options = array(
	"type" => "object",
	"subtype" => "groupforumtopic",
	"limit" => $discussion_count,
	"order_by" => "e.last_action desc",
	"pagination" => false,
	"full_view" => false
);

if ($widget->group_only == "yes") {
	$owner = $widget->getOwnerEntity();
	$groups = $owner->getGroups("", false);

	if (!empty($groups)) {
		
		$group_guids = array();
		foreach ($groups as $group) {
			$group_guids[] = $group->getGUID();
		}
		$options["container_guids"] = $group_guids;
	}
}

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

$content = elgg_list_entities_from_metadata($options);
if (empty($content)) {
	$content = elgg_echo("grouptopic:notcreated");
} else {
	
	$href = "discussion/all";
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

// prepend a quick start form
$params = $vars;
$params["embed"] = true;
echo elgg_view("widgets/start_discussion/content", $params);

// view listing of discussions
echo $content;
