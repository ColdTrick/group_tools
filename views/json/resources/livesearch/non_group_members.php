<?php
/**
 * Livesearch endpoint to search for users to invite to a group. These users aren't already member of the group
 *
 * @uses $vars['limit']          (int)    number of results to return
 * @uses $vars['term']           (string) search term (for username and displayname)
 * @uses $vars['name']           (string) the input name to be used when submitting the selected value
 * @uses $vars['group_guid']     (int)    the GUID of the group to search members in
 * @uses $vars['include_banned'] (bool)   include banned users in search results
 */

elgg_gatekeeper();

$limit = (int) elgg_extract('limit', $vars, elgg_get_config('default_limit'));
$query = elgg_extract('term', $vars, elgg_extract('q', $vars));
$input_name = elgg_extract('name', $vars);
$group_guid = (int) elgg_extract('group_guid', $vars);
$include_banned = (bool) elgg_extract('include_banned', $vars, false);

$options = [
	'query' => $query,
	'type' => 'user',
	'limit' => $limit,
	'sort' => 'name',
	'order' => 'ASC',
	'fields' => ['metadata' => ['name', 'username']],
	'item_view' => 'search/entity',
	'input_name' => $input_name,
	'metadata_name_value_pairs' => [],
];

if ($group_guid) {
	$options['wheres'][] = function(\Elgg\Database\QueryBuilder $qb, $main_alias) use ($group_guid) {
		$subquery = $qb->subquery('entity_relationships', 'er');
		$subquery->select('er.guid_one')
			->where($qb->compare('er.relationship', '=', 'member', ELGG_VALUE_STRING))
			->andWhere($qb->compare('er.guid_two', '=', $group_guid, ELGG_VALUE_INTEGER));

		return $qb->compare("{$main_alias}.guid", 'NOT IN', $subquery->getSQL());
	};
}

if (!$include_banned) {
	$options['metadata_name_value_pairs'][] = [
		'name' => 'banned',
		'value' => 'no',
	];
}

$body = elgg_list_entities($options, 'elgg_search');

echo elgg_view_page('', $body);
