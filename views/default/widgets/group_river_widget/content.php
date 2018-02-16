<?php
/**
 * content for the group river/activity widget
 */

$widget = elgg_extract('entity', $vars);

// which group
if ($widget->context == 'groups') {
	$group_guid = [$widget->getOwnerGUID()];
} else {
	$group_guid = $widget->group_guid;
	if (!empty($group_guid)) {
		if (!is_array($group_guid)) {
			$group_guid = [$group_guid];
		}
	}
}

if (!empty($group_guid)) {
	$group_guid = array_map('sanitise_int', $group_guid);
	$key = array_search(0, $group_guid);
	if ($key !== false) {
		unset($group_guid[$key]);
	}
}

if (empty($group_guid)) {
	echo elgg_echo('widgets:group_river_widget:view:not_configured');
	return;
}

// get activity filter
$activity_filter = $widget->activity_filter;

//get the number of items to display
$dbprefix = elgg_get_config('dbprefix');
$offset = 0;
$limit = (int) $widget->num_display;
if ($limit < 1) {
	$limit = 10;
}

// set river options
$options = [
        'pagination' => false,
	'limit' => $limit,
	'offset' => $offset,
        'distinct' => false,
	'joins' => [
		"JOIN {$dbprefix}entities e1 ON e1.guid = rv.object_guid",
		"LEFT JOIN {$dbprefix}entities e2 ON e2.guid = rv.target_guid",
	],
	'wheres' => [
		'(e1.container_guid IN (' . implode(',', $group_guid) . ')' . ' OR e1.guid IN (' . implode(',', $group_guid) . ')' . ' OR e2.container_guid IN (' . implode(',', $group_guid) . ')' . ')',
	],
	'no_results' => elgg_echo('widgets:group_river_widget:view:noactivity'),
];

if (!empty($activity_filter) && is_string($activity_filter)) {
	list($type, $subtype) = explode(',', $activity_filter);
	
	if (!empty($type)) {
		$options['type'] = sanitise_string($type);
		
		if (!empty($subtype)) {
			$options['subtype'] = sanitise_string($subtype);
		}		
	}
}

echo elgg_list_river($options);
