<?php

$tools = elgg_extract('tools', $vars);
$presets = elgg_extract('presets', $vars);

elgg_import_esm('groups/edit/tools/normal');

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:create_group:tool_presets:description'),
]);

$preset_selectors = '';
$preset_descriptions = '';

$presets_tools = [];

foreach ($presets as $index => $values) {
	$preset_selectors .= elgg_view('output/url', [
		'text' => elgg_extract('title', $values),
		'href' => false,
		'class' => 'elgg-button elgg-button-action mrm',
		'rel' => $index,
		'title' => elgg_strip_tags(elgg_extract('description', $values)),
	]);
	
	$preset_tools = (array) elgg_extract('tools', $values, []);
	foreach ($preset_tools as $tool_name => $tool) {
		if ($tool == 'yes') {
			$presets_tools[$tool_name][] = "group-tools-preset-{$index}";
		}
	}
	
	$tool_description = elgg_extract('description', $values);
	$tool_description .= elgg_view_field([
		'#type' => 'hidden',
		'name' => 'group_tools_preset',
		'value' => elgg_extract('title', $values),
		'disabled' => true,
	]);
	
	$preset_descriptions .= elgg_format_element('div', [
		'id' => "group-tools-preset-description-{$index}",
		'class' => 'hidden'
	], $tool_description);
}

// blank preset
$preset_selectors .= elgg_view('output/url', [
	'text' => elgg_echo('group_tools:create_group:tool_presets:blank:title'),
	'href' => '#',
	'class' => 'elgg-button elgg-button-action mrm',
	'rel' => 'blank',
]);

$tool_description = elgg_echo('group_tools:create_group:tool_presets:blank:description');
$tool_description .= elgg_view_field([
	'#type' => 'hidden',
	'name' => 'group_tools_preset',
	'value' => 'blank',
	'disabled' => true,
]);
$preset_descriptions .= elgg_format_element('div', [
	'id' => 'group-tools-preset-description-blank',
	'class' => 'hidden'
], $tool_description);

echo elgg_format_element('label', ['class' => 'mbs'], elgg_echo('group_tools:create_group:tool_presets:select')) . ':';
echo elgg_format_element('div', ['id' => 'group-tools-preset-selector', 'class' => 'mbs'], $preset_selectors);
echo elgg_format_element('div', ['id' => 'group-tools-preset-descriptions', 'class' => 'mbs'], $preset_descriptions);

$more_link = elgg_view('output/url', [
	'text' => elgg_echo('group_tools:create_group:tool_presets:show_more'),
	'href' => '#group-tools-preset-more',
	'id' => 'group-tools-preset-more-link',
	'class' => 'elgg-toggle',
]);

echo elgg_view_module('info', elgg_echo('group_tools:create_group:tool_presets:active_header'), '&nbsp;', [
	'id' => 'group-tools-preset-active',
	'class' => 'hidden',
	'menu' => $more_link,
]);

$tools_content = '';
/* @var $tool \Elgg\Groups\Tool */
foreach ($tools as $tool) {
	$group_option_toggle_name = $tool->mapMetadataName();
	
	$options = [
		'tool' => $tool,
		'value' => 'no',
		'class' => ['mbm'],
	];
	
	if (array_key_exists($group_option_toggle_name, $presets_tools)) {
		$options['class'] = elgg_extract_class($options, $presets_tools[$group_option_toggle_name]);
	}
	
	$tools_content .= elgg_view('groups/edit/tool', $options);
}

echo elgg_view_module('info', elgg_echo('group_tools:create_group:tool_presets:more_header'), $tools_content, ['id' => 'group-tools-preset-more', 'class' => 'hidden']);

$url_preset = get_input('group_tools_preset');
if (!empty($url_preset)) {
	echo elgg_view_field([
		'#type' => 'hidden',
		'name' => 'group_tools_auto_select',
		'value' => $url_preset,
	]);
}
