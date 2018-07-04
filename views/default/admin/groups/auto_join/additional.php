<?php

$content = elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:admin:auto_join:additional:description'),
]);

$output = '';
$configs = group_tools_get_auto_join_configurations();
foreach ($configs as $id => $config) {
	
	if (elgg_extract('type', $config) !== 'additional') {
		continue;
	}
	
	$output .= elgg_view('group_tools/elements/auto_join_configuration', [
		'config' => $config,
	]);
}

if (empty($output)) {
	$output = elgg_echo('group_tools:admin:auto_join:additional:none');
}

$content .= $output;

$menu = elgg_view('output/url', [
	'text' => elgg_echo('add'),
	'icon' => 'plus-circle',
	'href' => 'ajax/form/group_tools/admin/auto_join/additional',
	'class' => [
		'elgg-lightbox',
	],
	'data-colorbox-opts' => json_encode([
		'maxWidth' => '650px',
	]),
]);

echo elgg_view_module('info', elgg_echo('group_tools:admin:auto_join:additional'), $content, ['menu' => $menu]);
