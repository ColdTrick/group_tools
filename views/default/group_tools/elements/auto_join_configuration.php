<?php

$config = elgg_extract('config', $vars);
if (empty($config)) {
	return;
}

$title = elgg_extract('title', $config);
$title .= elgg_view('output/url', [
	'text' => elgg_view_icon('edit'),
	'href' => elgg_http_add_url_query_elements('ajax/form/group_tools/admin/auto_join/additional', [
		'id' => elgg_extract('id', $config),
	]),
	'title' => elgg_echo('edit'),
	'class' => [
		'mlm',
		'elgg-lightbox',
	],
	'data-colorbox-opts' => json_encode([
		'maxWidth' => '650px',
	]),
]);
$title .= elgg_view('output/url', [
	'text' => elgg_view_icon('delete-alt'),
	'href' => elgg_generate_action_url('group_tools/admin/auto_join/delete', [
		'id' => elgg_extract('id', $config),
	]),
	'title' => elgg_echo('delete'),
	'confirm' => elgg_echo('deleteconfirm'),
	'class' => [
		'mls',
	],
]);

$content = elgg_format_element('h4', ['class' => 'group-tools-auto-join-title'], $title);

$group_guids = (array) elgg_extract('group_guids', $config, []);
foreach ($group_guids as $guid) {
	$group = get_entity($guid);
	if (!$group instanceof \ElggGroup) {
		continue;
	}
	
	$content .= elgg_view_image_block(elgg_view_entity_icon($group, 'tiny'), elgg_view_entity_url($group));
}

echo elgg_format_element('div', ['class' => 'group-tools-auto-join-config'], $content);
