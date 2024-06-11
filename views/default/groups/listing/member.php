<?php
/**
 * Renders a list of groups which the user is a member of
 *
 * @uses $vars['options'] Additional listing options
 */

use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;
use Elgg\Database\RelationshipsTable;

elgg_gatekeeper();

$options = (array) elgg_extract('options', $vars);

$member_options = [
	'relationship' => 'member',
	'relationship_guid' => elgg_get_logged_in_user_guid(),
	'inverse_relationship' => false,
];

if (get_input('sort') === 'popular' && empty(get_input('sort_by'))) {
	$member_options['select'] = [
		function (QueryBuilder $qb, $main_alias) {
			$sub = $qb->subquery(RelationshipsTable::TABLE_NAME);
			$sub->select('count(*)')
				->where($qb->compare('guid_two', '=', "{$main_alias}.guid"))
				->andWhere($qb->compare('relationship', '=', 'member', ELGG_VALUE_STRING));
			
			return "({$sub->getSQL()}) as total";
		},
	];
	$member_options['order_by'] = [
		new OrderByClause('total', 'desc'),
	];
}

$vars['options'] = array_merge($options, $member_options);

echo elgg_view('groups/listing/all', $vars);
