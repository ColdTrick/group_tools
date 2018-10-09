<?php

$vars['options_values'] = elgg_extract('options_values', $vars, []);

$presets = group_tools_get_tool_presets();
if (!empty($presets)) {
	foreach ($presets as $set) {
		$title = elgg_extract('title', $set);
		if (!$title) {
			continue;
		}
		
		$vars['options_values'][$title] = $title;
	}
}

$value = elgg_extract('value', $vars);
if ($value && !in_array($value, $vars['options_values'])) {
	$vars['options_values'][$value] = $value;
}

echo elgg_view('input/select', $vars);
