<?php

use Elgg\Database\QueryBuilder;

elgg_gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();

$options = [
	'type' => 'group',
	'no_results' => elgg_echo('groups:none'),
	'wheres' => [
		function(QueryBuilder $qb, $main_alias) use ($user_guid) {
			
			$subquery = $qb->subquery('entity_relationships', 'managed');
			$subquery->select('guid_two')
				->where($qb->compare('managed.guid_one', '=', $user_guid, ELGG_VALUE_GUID))
				->andWhere($qb->compare('managed.relationship', '=', 'group_admin', ELGG_VALUE_STRING));
			
			$managed = $qb->compare("{$main_alias}.guid", 'IN', $subquery->getSQL());
				
			$owner = $qb->compare("{$main_alias}.owner_guid", '=', $user_guid, ELGG_VALUE_GUID);
			
			return $qb->merge([$managed, $owner], 'OR');
		},
	],
];

$getter = 'elgg_get_entities';
// sorting options
$sorting = get_input('sort');
switch ($sorting) {
	case 'popular':
		$getter = 'elgg_get_entities_from_relationship_count';
		
		$options['relationship'] = 'member';
		$options['inverse_relationship'] = false;
		break;
	case 'alpha':
		$order = strtoupper(get_input('order'));
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
