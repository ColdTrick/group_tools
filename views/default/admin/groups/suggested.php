<?php

$suggested_groups = elgg_get_plugin_setting('suggested_groups', 'group_tools');
$suggested_groups = string_to_tag_array($suggested_groups);

if (empty(($suggested_groups))) {
	echo elgg_view('page/components/no_results', ['no_results' => elgg_echo('notfound')]);
	return;
}

echo elgg_list_entities([
	'type' => 'group',
	'limit' => false,
	'guids' => $suggested_groups,
]);
