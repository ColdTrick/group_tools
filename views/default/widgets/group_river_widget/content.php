<?php
/**
 * content for the group river/activity widget
 */

use Elgg\Database\EntityTable;
use Elgg\Database\QueryBuilder;

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

// which group
if ($widget->context == 'groups') {
	$group_guid = [$widget->owner_guid];
} else {
	$group_guid = $widget->group_guid;
	if (!empty($group_guid)) {
		if (!is_array($group_guid)) {
			$group_guid = [$group_guid];
		}
	}
}

if (!empty($group_guid)) {
	array_walk($group_guid, function(&$value) {
		$value = (int) $value;
	});
	$group_guid = array_filter($group_guid, function ($value) {
		return $value > 0;
	});
}

if (empty($group_guid)) {
	echo elgg_echo('widgets:not_configured');
	return;
}

// limit
$limit = (int) $widget->num_display;
if ($limit < 1) {
	$limit = 10;
}

// prepare options
$options = [
	'limit' => $limit,
	'wheres' => [
		function (QueryBuilder $qb, $main_alias) use ($group_guid) {
			$wheres = [];
			$wheres[] = $qb->compare("{$main_alias}.object_guid", 'in', $group_guid, ELGG_VALUE_GUID);
			$wheres[] = $qb->compare("{$main_alias}.target_guid", 'in', $group_guid, ELGG_VALUE_GUID);
			
			$sub = $qb->subquery(EntityTable::TABLE_NAME, 'ce');
			$sub->select("{$sub->getTableAlias()}.guid")
				->where($qb->compare("{$sub->getTableAlias()}.container_guid", 'in', $group_guid, ELGG_VALUE_GUID));
			
			$wheres[] = $qb->compare("{$main_alias}.object_guid", 'in', $sub->getSQL());
		
			$wheres[] = $qb->compare("{$main_alias}.target_guid", 'in', $sub->getSQL());
			
			return $qb->merge($wheres, 'OR');
		},
	],
	'pagination' => false,
	'no_results' => elgg_echo('widgets:group_river_widget:view:noactivity'),
];

// get activity filter
$activity_filter = $widget->activity_filter;
if (!empty($activity_filter) && is_string($activity_filter)) {
	list($type, $subtype) = explode(',', $activity_filter);
	
	if (!empty($type)) {
		$options['type'] = $type;
	}
	
	if (!empty($subtype)) {
		$options['subtype'] = $subtype;
	}
}

echo elgg_list_river($options);
