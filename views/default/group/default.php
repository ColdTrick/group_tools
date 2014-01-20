<?php
/**
 * Group entity view
 *
 * @package ElggGroups
 */

$group = $vars['entity'];

$icon = elgg_view_entity_icon($group, 'tiny');

$metadata = elgg_view_menu('entity', array(
	'entity' => $group,
	'handler' => 'groups',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

if ((elgg_in_context('owner_block') || elgg_in_context('widgets')) && !elgg_in_context("widgets_groups_show_members")) {
	$metadata = '';
}

if ($vars['full_view']) {
	echo elgg_view('groups/profile/summary', $vars);
} else {
	$title = "";
	if (group_tools_show_hidden_indicator($group)) {
		$access_id_string = get_readable_access_level($group->access_id);
		$access_id_string = htmlspecialchars($access_id_string, ENT_QUOTES, "UTF-8", false);
		
		$title .= "<span title='" . $access_id_string . "'>" . elgg_view_icon("eye", "float") . "</span>";
	}
	$title .= elgg_view("output/url", array("text" => $group->name, "href" => $group->getURL(), "is_trusted" => true));
	
	// brief view
	$params = array(
		'entity' => $group,
		'title' => $title,
		'metadata' => $metadata,
		'subtitle' => $group->briefdescription,
	);
	$params = $params + $vars;
	$list_body = elgg_view('group/elements/summary', $params);

	echo elgg_view_image_block($icon, $list_body, $vars);
}
