<?php

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:group:admin_approve:admin:description'),
]);

echo elgg_list_entities([
	'type' => 'group',
	'limit' => false,
	'access_id' => ACCESS_PRIVATE,
	'item_view' => 'group_tools/group/admin_approve',
	'no_results' => elgg_echo('groups:none'),
]);
