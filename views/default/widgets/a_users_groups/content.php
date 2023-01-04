<?php
/**
 * Group membership widget
 *
 * added: sort_by options
 */

use Elgg\Database\Select;
use Elgg\Database\QueryBuilder;

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

$owner = $widget->getOwnerEntity();
if (!$owner instanceof \ElggUser) {
	$owner = elgg_get_logged_in_user_entity();
}

if (!$owner instanceof \ElggUser) {
	echo elgg_view_message('notice', elgg_echo('loggedinrequired'), ['title' => false]);
	return;
}

$num_display = (int) $widget->num_display ?: 4;
$sort_by = $widget->sort_by ?? 'alpha';

$params = [
	'type' => 'group',
	'relationship' => 'member',
	'relationship_guid' => $owner->guid,
	'limit' => $num_display,
	'pagination' => false,
	'no_results' => elgg_echo('groups:none'),
	'widget_more' => elgg_view_url(elgg_generate_url('collection:group:group:member', ['username' => $owner->username]), elgg_echo('groups:more')),
	'sort_by' => [
		'property_type' => 'metadata',
		'property' => 'name',
	],
];

if ($widget->context !== 'profile') {
	switch ($sort_by) {
		case 'join_date':
			$params['sort_by'] = [
				'property_type' => 'relationship',
				'property' => 'member',
				'direction' => 'DESC',
				'relationship_guid' => $owner->guid,
			];
			break;
		
		case 'activity':
			unset($params['sort_by']);
			
			// sort by latest activity
			$params['select'][] = function (QueryBuilder $qb, $main_alias) use ($owner) {
				$river = $qb->subquery('river', 'river');
				$river->select('river.posted');
				$river->joinEntitiesTable('river', 'object_guid', 'inner', 'ent');
				$river->where($qb->compare('river.subject_guid', '=', $owner->guid, ELGG_VALUE_GUID));
				$river->andWhere($qb->merge([
					$qb->compare("{$main_alias}.guid", '=', 'ent.container_guid'),
					$qb->compare("{$main_alias}.guid", '=', 'river.object_guid'),
				], 'OR'));
				$river->orderBy('river.posted', 'desc');
				$river->setMaxResults(1);
				
				return '('. $river->getSQL() . ') AS latest_activity';
			};
			
			$params['order_by'][] = new \Elgg\Database\Clauses\OrderByClause('latest_activity', 'desc');
			$params['order_by'][] = new \Elgg\Database\Clauses\OrderByClause('e.time_created', 'desc');
			break;
	}
}

echo elgg_list_entities($params);
