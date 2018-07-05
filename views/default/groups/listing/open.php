<?php

echo elgg_list_entities([
	'type' => 'group',
	'metadata_name_value_pairs' => [
		'name' => 'membership',
		'value' => ACCESS_PUBLIC,
	],
	'no_results' => elgg_echo('groups:none'),
]);
