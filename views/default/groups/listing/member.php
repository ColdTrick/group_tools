<?php

use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;

elgg_gatekeeper();

$options = [
	'relationship' => 'member',
	'relationship_guid' => elgg_get_logged_in_user_guid(),
	'inverse_relationship' => false,
];

if (get_input('sort') === 'popular' && empty(get_input('sort_by'))) {
	$options['select'] = [
		function (QueryBuilder $qb, $main_alias) {
			$sub = $qb->subquery('entity_relationships');
			$sub->select('count(*)')
				->where($qb->compare('guid_two', '=', "{$main_alias}.guid"))
				->andWhere($qb->compare('relationship', '=', 'member', ELGG_VALUE_STRING));
			
			return "({$sub->getSQL()}) as total";
		},
	];
	$options['order_by'] = [
		new OrderByClause('total', 'desc'),
	];
}

echo elgg_view('groups/listing/all', ['options' => $options]);
