<?php

if (elgg_get_plugin_setting('domain_based', 'group_tools') !== 'yes') {
	return;
}

$content = elgg_format_element('div', ['class' => 'mbm'], elgg_echo('group_tools:domain_based:description'));

$domains = '';
$group = elgg_extract('entity', $vars);
if ($group instanceof ElggGroup) {
	$domains = (string) $group->getPluginSetting('group_tools', 'domain_based');
	if (!empty($domains)) {
		$domains = elgg_string_to_array($domains);
	}
}

$content .= elgg_view_field([
	'#type' => 'tags',
	'name' => 'settings[group_tools][domain_based]',
	'value' => $domains,
]);

// show content
echo elgg_view_module('info', elgg_echo('group_tools:domain_based:title'), $content);
