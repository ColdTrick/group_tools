<?php
/**
 * Configure group tool presets
 *
 * @uses $vars['group_tool_presets'] the current group tool presets (if any)
 */

// load js
elgg_import_esm('forms/group_tools/group_tool_presets');

$presets = elgg_extract('group_tool_presets', $vars);
$group_tools = elgg()->group_tools->all();

// list existing
if (!empty($presets)) {
	foreach ($presets as $index => $values) {
		echo elgg_view('group_tools/elements/group_tool_preset', [
			'index' => $index,
			'values' => $values,
		]);
	}
}

// hidden wrapper for clone
echo elgg_view('group_tools/elements/group_tool_preset', [
	'wrapper_vars' => [
		'id' => 'group-tools-tool-preset-base',
		'class' => 'hidden',
	],
]);

// save button
$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('save'),
]);
elgg_set_form_footer($footer);
