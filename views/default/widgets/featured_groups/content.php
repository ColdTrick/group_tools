<?php
/**
 * content of the featured groups widget
 */

use Elgg\Database\MetadataTable;
use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

$num_display = (int) $widget->num_display ?: 5;

$show_random = $widget->show_random;

$entities = elgg_get_entities([
	'type' => 'group',
	'limit' => $num_display,
	'metadata_name_value_pairs' => ['featured_group' => 'yes'],
	'order_by' => new OrderByClause('RAND()'),
]);

if ($show_random == 'yes') {
	$random = elgg_get_entities([
		'type' => 'group',
		'limit' => 1,
		'order_by' => new OrderByClause('RAND()'),
		'wheres' => [
			function (QueryBuilder $qb, $main_alias) {
				$subquery = $qb->subquery(MetadataTable::TABLE_NAME, 'featured_md');
				$subquery->select('entity_guid')
					->andWhere($qb->compare("{$subquery->getTableAlias()}.name", '=', 'featured_group', ELGG_VALUE_STRING))
					->andWhere($qb->compare("{{$subquery->getTableAlias()}}.value", '=', 'yes', ELGG_VALUE_STRING));
				
				return $qb->compare("{$main_alias}.guid", 'NOT IN', $subquery->getSQL());
			},
		],
	]);
	
	$entities = array_merge($entities, $random);
}

echo elgg_view_entity_list($entities, [
	'limit' => count($entities),
	'count' => count($entities),
	'pagination' => false,
	'full_view' => false,
	'no_results' => true,
]);
