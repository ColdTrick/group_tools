<?php

if (elgg_get_plugin_setting('limited_groups', 'groups') != 'yes' || elgg_is_admin_logged_in()) {
	elgg_register_title_button('add', 'group', 'group');
}

$selected_tab = get_input('filter', 'all');
if (!elgg_view_exists("groups/listing/{$selected_tab}")) {
	$selected_tab = 'all';
}

$content = elgg_view("groups/listing/{$selected_tab}", $vars);

$sidebar = elgg_view('groups/sidebar/find');
$sidebar .= elgg_view('groups/sidebar/featured');

echo elgg_view_page(elgg_echo('groups:all'), [
	'content' => $content,
	'sidebar' => $sidebar,
	'filter_id' => 'groups/all',
	'filter_value' => $selected_tab,
	'filter_sorting' => $selected_tab !== 'suggested',
	'filter_sorting_selected' => elgg_extract('filter_sorting_selected', $vars),
]);
