<?php
/**
 * content for the group river/activity widget
 */

use Elgg\Database\EntityTable;
use Elgg\Database\QueryBuilder;

/** @var \ElggWidget $widget */
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
	'type_subtype_pairs' => [],
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
$activity_filter = (array) $widget->activity_filter;
foreach ($activity_filter as $content) {
	list($type, $subtype) = explode(',', $content);
	if (empty($type)) {
		continue;
	}

	$value = $subtype;
	if (array_key_exists($type, $options['type_subtype_pairs'])) {
		if (!is_array($options['type_subtype_pairs'][$type])) {
			$value = [$options['type_subtype_pairs'][$type]];
		} else {
			$value = $options['type_subtype_pairs'][$type];
		}

		$value[] = $subtype;
	}

	$options['type_subtype_pairs'][$type] = $value;
}

echo elgg_list_river($options);
