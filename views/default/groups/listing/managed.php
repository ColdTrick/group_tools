<?php

use Elgg\Database\QueryBuilder;

elgg_gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();

echo elgg_view('groups/listing/all', [
	'options' => [
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
	],
]);

