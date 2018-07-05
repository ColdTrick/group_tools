<?php

elgg_gatekeeper();

$limit = get_input('limit', elgg_get_config('default_limit'));
$query = get_input('term', get_input('q'));
$group_guid = (int) get_input('group_guid');
$input_name = get_input('name');

$options = [
	'query' => $query,
	'type' => 'user',
	'limit' => $limit,
	'sort' => 'name',
	'order' => 'ASC',
	'fields' => ['metadata' => ['name', 'username']],
	'item_view' => 'search/entity',
	'input_name' => $input_name,
];

if ($group_guid) {
	$options['wheres'][] = function(\Elgg\Database\QueryBuilder $qb) use ($group_guid) {
		$subquery = $qb->subquery('entity_relationships', 'er');
		$subquery->select('er.guid_one')
			->where($qb->compare('er.relationship', '=', 'member', ELGG_VALUE_STRING))
			->andWhere($qb->compare('er.guid_two', '=', $group_guid, ELGG_VALUE_INTEGER));

		return $qb->compare("e.guid", 'NOT IN', $subquery->getSQL());
	};
}

$body = elgg_list_entities($options, 'elgg_search');

echo elgg_view_page('', $body);
