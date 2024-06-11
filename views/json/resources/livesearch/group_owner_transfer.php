<?php
/**
 * Livesearch endpoint to search for users who are a member of the provided group
 *
 * @uses $vars['limit']          (int)    number of results to return
 * @uses $vars['term']           (string) search term (for username and displayname)
 * @uses $vars['name']           (string) the input name to be used when submitting the selected value
 * @uses $vars['group_guid']     (int)    the GUID of the group to search members in
 * @uses $vars['include_banned'] (bool)   include banned users in search results
 *
 * @throws Elgg\Exceptions\Http\EntityNotFoundException if the group_guid doesn't match a group
 */

use Elgg\Database\QueryBuilder;
use Elgg\Database\RelationshipsTable;
use Elgg\Exceptions\Http\EntityNotFoundException;

elgg_gatekeeper();

$limit = (int) elgg_extract('limit', $vars, elgg_get_config('default_limit'));
$query = elgg_extract('term', $vars, elgg_extract('q', $vars));
$input_name = elgg_extract('name', $vars);
$group_guid = (int) elgg_extract('group_guid', $vars);
$include_banned = (bool) elgg_extract('include_banned', $vars, false);

$group = get_entity($group_guid);
if (!$group instanceof \ElggGroup) {
	throw new EntityNotFoundException();
}

$options = [
	'query' => $query,
	'type' => 'user',
	'limit' => $limit,
	'sort_by' => [
		'property_type' => 'metadata',
		'property' => 'name',
		'direction' => 'ASC',
	],
	'fields' => ['metadata' => ['name', 'username']],
	'item_view' => 'search/entity',
	'input_name' => $input_name,
	'wheres' => [
		function(QueryBuilder $qb, $main_alias) use ($group_guid) {
			$user = elgg_get_logged_in_user_entity();
			$subs = [];
			
			// group members
			$members = $qb->subquery(RelationshipsTable::TABLE_NAME);
			$members->select('guid_one')
				->where($qb->compare('relationship', '=', 'member', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', $group_guid, ELGG_VALUE_GUID));
			
			$subs[] = $qb->compare("{$main_alias}.guid", 'in', $members->getSQL());
			
			// friends
			$friends = $qb->subquery(RelationshipsTable::TABLE_NAME);
			$friends->select('guid_two')
				->where($qb->compare('relationship', '=', 'friend', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_one', '=', $user->guid, ELGG_VALUE_GUID));
			
			$subs[] = $qb->compare("{$main_alias}.guid", 'in', $friends->getSQL());
			
			// myself
			$subs[] = $qb->compare("{$main_alias}.guid", '=', $user->guid, ELGG_VALUE_GUID);
			
			return $qb->merge($subs, 'OR');
		},
	],
	'metadata_name_value_pairs' => [],
];

if (!$include_banned) {
	$options['metadata_name_value_pairs'][] = [
		'name' => 'banned',
		'value' => 'no',
	];
}

$body = elgg_list_entities($options, 'elgg_search');

echo elgg_view_page('', $body);
