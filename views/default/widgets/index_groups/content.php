<?php
/**
 * content of the index groups widget
 */

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

// get widget settings
$count = (int) $widget->group_count;
if ($count < 1) {
	$count = 8;
}

$options = [
	'type' => 'group',
	'limit' => $count,
	'full_view' => false,
	'pagination' => false,
	'metadata_name_value_pairs' => [],
	'metadata_case_sensitive' => false,
	'no_results' => elgg_echo('groups:none'),
];

// limit to featured groups?
if ($widget->featured == 'yes') {
	$options['metadata_name_value_pairs'][] = [
		'name' => 'featured_group',
		'value' => 'yes',
	];
}

// enable advanced filter
$filter_name = $widget->filter_name;
$filter_value = $widget->filter_value;
if (!empty($filter_name) && !empty($filter_value)) {
	$profile_fields = elgg()->fields->get('group', 'group');
	if (!empty($profile_fields)) {
		$found = false;
		
		foreach ($profile_fields as $field_config) {
			if ((elgg_extract('name', $field_config) === $filter_name) && (elgg_extract('#type', $field_config) === 'tags')) {
				$found = true;
				break;
			}
		}
		
		if ($found) {
			$filter_value = string_to_tag_array($filter_value);
			
			$options['metadata_name_value_pairs'][] = [
				'name' => $filter_name,
				'value' => $filter_value,
			];
		}
	}
}

// check if groups should respect a specific order
$gettter = 'elgg_get_entities';
switch ($widget->sorting) {
	case 'popular':
		$gettter = 'elgg_get_entities_from_relationship_count';
		
		$options['relationship'] = 'member';
		$options['inverse_relationship'] = false;
		break;
	default:
		// just use default time created sorting
		break;
}

// list groups
echo elgg_list_entities($options, $gettter);
