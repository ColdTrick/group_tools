<?php
/**
 * Renders a list of groups (default sorted by creation date)
 *
 * Note: this view has a corresponding view in the rss view type, changes should be reflected
 *
 * @uses $vars['options'] Additional listing options
 * @uses $vars['getter']  Function to get entities (default: 'elgg_get_entities')
 */

$defaults = [
	'type' => 'group',
	'full_view' => false,
	'no_results' => elgg_echo('groups:none'),
];

$options = (array) elgg_extract('options', $vars, []);
$options = array_merge($defaults, $options);

$getter = (string) elgg_extract('getter', $vars, 'elgg_get_entities');

// sorting options
if (!isset($options['sort_by'])) {
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
