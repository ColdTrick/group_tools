<?php
/**
 * Livesearch endpoint to search for users who are a member of the provided group
 *
 * @uses get_input('limit')      (int)    number of results to return
 * @uses get_input('term')       (string) search term (for username and displayname)
 * @uses get_input('name')       (string) the input name to be used when submitting the selected value
 * @uses get_input('group_guid') (int)    the GUID of the group to search members in
 *
 * @throws Elgg\EntityNotFoundException if the group_guid doesn't match a group
 */

use Elgg\EntityNotFoundException;
use Elgg\Database\QueryBuilder;

elgg_gatekeeper();

$limit = get_input('limit', elgg_get_config('default_limit'));
$query = get_input('term', get_input('q'));
$input_name = get_input('name');
$group_guid = (int) get_input('group_guid');

$group = get_entity($group_guid);
if (!$group instanceof ElggGroup) {
	throw new EntityNotFoundException();
}

$options = [
	'query' => $query,
	'type' => 'user',
	'limit' => $limit,
	'sort' => 'name',
	'order' => 'ASC',
	'fields' => ['metadata' => ['name', 'username']],
	'item_view' => 'search/entity',
	'input_name' => $input_name,
	'wheres' => [
		function(QueryBuilder $qb, $main_alias) use ($group_guid) {
			$user = elgg_get_logged_in_user_entity();
			$subs = [];
			
			// group members
			$members = $qb->subquery('entity_relationships');
			$members->select('guid_one')
				->where($qb->compare('relationship', '=', 'member', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', $group_guid, ELGG_VALUE_GUID));
			
			$subs[] = $qb->compare("{$main_alias}.guid", 'in', $members->getSQL());
			
			// friends
			$friends = $qb->subquery('entity_relationships');
			$friends->select('guid_two')
				->where($qb->compare('relationship', '=', 'friend', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_one', '=', $user->guid, ELGG_VALUE_GUID));
			
			$subs[] = $qb->compare("{$main_alias}.guid", 'in', $friends->getSQL());
			
			// myself
			$subs[] = $qb->compare("{$main_alias}.guid", '=', $user->guid, ELGG_VALUE_GUID);
			
			return $qb->merge($subs, 'OR');
		},
	],
];

$body = elgg_list_entities($options, 'elgg_search');

echo elgg_view_page('', $body);
