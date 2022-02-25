<?php

use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;

elgg_gatekeeper();

$options = [
	'type' => 'group',
	'no_results' => elgg_echo('groups:none'),
	'relationship' => 'member',
	'relationship_guid' => elgg_get_logged_in_user_guid(),
	'inverse_relationship' => false,
];

$getter = 'elgg_get_entities';
// sorting options
$sorting = get_input('sort');
switch ($sorting) {
	case 'popular':
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
		break;
	case 'alpha':
		$order = strtoupper(get_input('order', ''));
		if (!in_array($order, ['ASC', 'DESC'])) {
			$order = 'ASC';
		}
		
		$options['order_by_metadata'] = [
			'name' => 'name',
			'direction' => $order,
		];
		
		break;
	case 'newest':
	default:
		// nothing, as Elgg default sorting is by time_created desc (eg newest)
		break;
}

echo elgg_list_entities($options, $getter);
