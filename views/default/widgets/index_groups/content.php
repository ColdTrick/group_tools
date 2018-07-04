<?php
/**
 * content of the index groups widget
 */

$widget = elgg_extract('entity', $vars);

// get widget settings
$count = sanitise_int($widget->group_count, false) ?: 8;

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
	$profile_fields = elgg_get_config('group');
	if (!empty($profile_fields)) {
		$found = false;
		
		foreach ($profile_fields as $name => $type) {
			if (($name == $filter_name) && ($type == 'tags')) {
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

$sorting_value = $widget->sorting;
if (empty($sorting_value) && ($widget->apply_sorting == 'yes')) {
	$sorting_value = 'ordered';
}

// check if groups should respect a specific order
switch ($sorting_value) {
	case 'ordered':
		$dbprefix = elgg_get_config('dbprefix');
		
		$options['selects'] = [
			"IFNULL((
				SELECT order_ms.string as order_val
				FROM {$dbprefix}metadata mo
				WHERE e.guid = mo.entity_guid
				AND mo.name = 'order'
			), 99999) AS order_val",
		];
			
		$options['order_by'] = 'CAST(order_val AS SIGNED) ASC, e.time_created DESC';
		break;
	case 'popular':
		$options['relationship'] = 'member';
		$options['inverse_relationship'] = false;
		break;
	default:
		// just use default time created sorting
		break;
}

// list groups
echo elgg_list_entities($options);
