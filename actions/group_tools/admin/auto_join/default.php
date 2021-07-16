<?php

$group_guids = get_input('group_guids');

if (!empty($group_guids)) {
	if (!is_array($group_guids)) {
		$group_guids = [$group_guids];
	}
	
	$group_guids = array_map(function($value) {
		return (int) $value;
	}, $group_guids);
}

$plugin = elgg_get_plugin_from_id('group_tools');

if (empty($group_guids)) {
	$plugin->unsetSetting('auto_join');
} else {
	$plugin->setSetting('auto_join', implode(',', $group_guids));
}

return elgg_ok_response('', elgg_echo('save:success'));
