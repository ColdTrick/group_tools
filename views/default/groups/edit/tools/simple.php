<?php

$tools = elgg_extract('tools', $vars);
$presets = elgg_extract('presets', $vars);

if (empty($presets)) {
	return;
}

elgg_require_js('groups/edit/tools/simple');

$output = '';
$selected_tools = [];

foreach ($presets as $index => $preset) {
	$preset_content = elgg_format_element('h3', [], elgg_extract('title', $preset));
	$preset_content .= elgg_view('output/longtext', [
		'value' => elgg_extract('description', $preset),
	]);
	
	$preset_content .= elgg_view_field([
		'#type' => 'hidden',
		'name' => 'group_tools_preset',
		'value' => elgg_extract('title', $preset),
		'disabled' => true,
	]);
	
	$preset_tools = (array) elgg_extract('tools', $preset, []);
	foreach ($preset_tools as $tool_name => $tool) {
		if ($tool == 'yes') {
			$selected_tools[$tool_name][] = "group-tools-preset-{$index}";
		}
	}
	
	$output .= elgg_format_element('div', [
		'class' => 'group-tools-simplified-option',
		'data-preset' => $index,
	], $preset_content);
}

echo elgg_format_element('div', ['class' => ['group-tools-edit-tools-simple', 'group-tools-simplified-options']], $output);

/* @var $group_option \Elgg\Groups\Tool */
foreach ($tools as $group_option) {
	$name = $group_option->mapMetadataName();
	
	$options = [
		'group_tool' => $group_option,
		'value' => 'name',
		'class' => ['hidden'],
	];
	
	if (array_key_exists($name, $selected_tools)) {
		$options['class'] = elgg_extract_class($options, $selected_tools[$name]);
	}
	
	echo elgg_view('group_tools/elements/group_tool', $options);
}
