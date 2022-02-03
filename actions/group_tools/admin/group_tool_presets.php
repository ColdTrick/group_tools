<?php

$presets = (array) get_input('params', []);

// filter out invalid input
foreach ($presets as $index => $values) {
	if (!is_numeric($index)) {
		// the placeholder for cloning
		unset($presets[$index]);
	} elseif (!elgg_extract('title', $values)) {
		// title is required
		unset($presets[$index]);
	}
}

// reset array keys
if (!empty($presets)) {
	$presets = array_values($presets);
}

$plugin = elgg_get_plugin_from_id('group_tools');
$plugin->setSetting('group_tool_presets', json_encode($presets));

return elgg_ok_response('', elgg_echo('group_tools:action:group_tool:presets:saved'));
