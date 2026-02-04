<?php

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:group:admin_approve:admin:description'),
]);

echo elgg_list_entities([
	'type' => 'group',
	'limit' => false,
	'metadata_names' => [
		'admin_approval',
		'is_concept',
	],
	'item_view' => 'group_tools/group/admin_approve',
	'no_results' => elgg_echo('groups:none'),
]);
