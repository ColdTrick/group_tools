<?php

// for consistency with other tabs
elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo('groups'));

elgg_register_title_button();

$selected_tab = "suggested";

$groups = group_tools_get_suggested_groups();
if ($groups) {
	// list suggested groups
} else {
	// replace with better 'no suggested groups' text
	$content = elgg_echo("groups:none");
}

$filter = elgg_view('groups/group_sort_menu', array('selected' => $selected_tab));

$sidebar = elgg_view('groups/sidebar/find');
$sidebar .= elgg_view('groups/sidebar/featured');

$params = array(
		'content' => $content,
		'sidebar' => $sidebar,
		'filter' => $filter,
);

$body = elgg_view_layout('content', $params);

echo elgg_view_page(elgg_echo('groups:all'), $body);
