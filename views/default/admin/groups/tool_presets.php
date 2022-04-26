<?php

// description
echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:admin:group_tool_presets:description'),
]);

// build elements
$title = elgg_echo('group_tools:admin:group_tool_presets:header');
$add_button = elgg_view('output/url', [
	'text' => elgg_echo('add'),
	'href' => false,
	'class' => 'elgg-button elgg-button-action group-tools-admin-add-tool-preset',
]);

$form = elgg_view_form('group_tools/group_tool_presets', [
	'action' => 'action/group_tools/admin/group_tool_presets',
], [
	'group_tool_presets' => group_tools_get_tool_presets(),
]);

// draw list
echo elgg_view_module('info', $title, $form, ['menu' => $add_button]);
