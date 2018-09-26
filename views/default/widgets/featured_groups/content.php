<?php
use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;

/**
 * content of the featured groups widget
 */

$widget = elgg_extract('entity', $vars);

$num_display = (int) $widget->num_display ?: 5;

$show_random = $widget->show_random;

$featured = elgg_list_entities([
	'type' => 'group',
	'limit' => $num_display,
	'full_view' => false,
	'pagination' => false,
	'metadata_name_value_pairs' => ['featured_group' => 'yes'],
	'order_by' => new OrderByClause('RAND()'),
]);

$random = '';
if ($show_random == 'yes') {
	$dbprefix = elgg_get_config('dbprefix');
	
	$random_options = [
		'type' => 'group',
		'limit' => 1,
		'order_by' => new OrderByClause('RAND()'),
		'wheres' => [
			function (QueryBuilder $qb) {
				$subquery = $qb->subquery('metadata', 'featured_md');
				$subquery->select('guid')
					->where($qb->compare('featured_md.entity_guid', '=', 'e.guid'))
					->andWhere($qb->compare('featured_md.name', '=', 'featured_group', ELGG_VALUE_STRING))
					->andWhere($qb->compare('featured_md.value', '=', 'yes', ELGG_VALUE_STRING));

				return "NOT EXISTS ({$subquery->getSQL()})";
			},
		],
	];
	
	$random = elgg_list_entities($random_options);
}

$list = $featured . $random;
if (empty($list)) {
	$list = elgg_echo('notfound');
}

echo $list;
