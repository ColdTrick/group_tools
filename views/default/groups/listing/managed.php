<?php
/**
 * Renders a list of groups which the user can manage
 *
 * @uses $vars['options'] Additional listing options
 */

use Elgg\Database\QueryBuilder;
use Elgg\Database\RelationshipsTable;

elgg_gatekeeper();
$user_guid = elgg_get_logged_in_user_guid();

$options = (array) elgg_extract('options', $vars);

$managed_options = [
	'wheres' => [
		function(QueryBuilder $qb, $main_alias) use ($user_guid) {
			$subquery = $qb->subquery(RelationshipsTable::TABLE_NAME, 'managed');
			$subquery->select('guid_two')
				 ->where($qb->compare("{$subquery->getTableAlias()}.guid_one", '=', $user_guid, ELGG_VALUE_GUID))
				 ->andWhere($qb->compare("{$subquery->getTableAlias()}.relationship", '=', 'group_admin', ELGG_VALUE_STRING));
			
			$managed = $qb->compare("{$main_alias}.guid", 'IN', $subquery->getSQL());
			
			$owner = $qb->compare("{$main_alias}.owner_guid", '=', $user_guid, ELGG_VALUE_GUID);
			
			return $qb->merge([$managed, $owner], 'OR');
		},
	],
];

$vars['options'] = array_merge($options, $managed_options);

echo elgg_view('groups/listing/all', $vars);
