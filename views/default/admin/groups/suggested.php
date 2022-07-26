<?php

$suggested_groups = (string) elgg_get_plugin_setting('suggested_groups', 'group_tools');
$suggested_groups = elgg_string_to_array($suggested_groups);

if (empty(($suggested_groups))) {
	echo elgg_view('page/components/no_results', ['no_results' => elgg_echo('notfound')]);
	return;
}

echo elgg_list_entities([
	'type' => 'group',
	'limit' => false,
	'guids' => $suggested_groups,
]);
