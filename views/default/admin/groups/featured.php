<?php

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:settings:special_states:featured:description'),
]);

echo elgg_list_entities([
	'type' => 'group',
	'limit' => false,
	'metadata_name_value_pairs' => [
		'name' => 'featured_group',
		'value' => 'yes',
	],
	'no_results' => true,
]);
