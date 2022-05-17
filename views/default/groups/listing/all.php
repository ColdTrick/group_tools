<?php

$defaults = [
	'type' => 'group',
	'no_results' => elgg_echo('groups:none'),
];

$options = (array) elgg_extract('options', $vars, []);
$options = array_merge($defaults, $options);

$getter = 'elgg_get_entities';

// sorting options
if (!isset($options['order_by'])) {
	$sorting = get_input('sort');
	switch ($sorting) {
		case 'popular':
			$getter = 'elgg_get_entities_from_relationship_count';
			
			$options['relationship'] = 'member';
			$options['inverse_relationship'] = false;
			break;
		default:
			// nothing, as Elgg default sorting is by time_created desc (eg newest)
			break;
	}
}

echo elgg_list_entities($options, $getter);
