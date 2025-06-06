<?php
/**
 * Show a list of all group members
 */

use Elgg\Database\QueryBuilder;
use Elgg\Database\RelationshipsTable;

$group = elgg_get_page_owner_entity();

elgg_push_entity_breadcrumbs($group);

if ($group->canEdit()) {
	elgg_register_menu_item('title', [
		'name' => 'groups:invite',
		'icon' => 'user-plus',
		'href' => elgg_generate_entity_url($group, 'invite'),
		'text' => elgg_echo('groups:invite'),
		'link_class' => 'elgg-button elgg-button-action',
	]);
}

$filter = get_input('filter', 'members');

$options = [
	'type' => 'user',
	'relationship' => 'member',
	'relationship_guid' => $group->guid,
	'inverse_relationship' => true,
	'sort_by' => get_input('sort_by', [
		'property' => 'name',
		'property_type' => 'metadata',
		'direction' => 'asc',
	]),
];

if ($filter === 'group_admins') {
	$options['wheres'] = [
		function (QueryBuilder $qb, $main_alias) use ($group) {
			$owner = $qb->compare("{$main_alias}.guid_one", '=', $group->owner_guid, ELGG_VALUE_GUID);
			
			$admin_query = $qb->subquery(RelationshipsTable::TABLE_NAME)
				->select('guid_one')
				->where($qb->compare('relationship', '=', 'group_admin', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', $group->guid, ELGG_VALUE_GUID));
			
			$admins = $qb->compare("{$main_alias}.guid_one", 'IN', $admin_query->getSQL());
			
			return $qb->merge([$owner, $admins], 'OR');
		},
	];
}

$members_search = get_input('members_search');
if (!elgg_is_empty($members_search)) {
	$options['metadata_name_value_pairs'] = [
		[
			'name' => 'name',
			'value' => "%{$members_search}%",
			'operand' => 'LIKE',
			'case_sensitive' => false,
		],
		[
			'name' => 'username',
			'value' => "%{$members_search}%",
			'operand' => 'LIKE',
			'case_sensitive' => false,
		],
	];
	$options['metadata_name_value_pairs_operator'] = 'OR';
}

$list = elgg_list_relationships($options);

if (elgg_is_xhr() && isset($members_search)) {
	// ajax pagination
	echo $list;
	return;
}

$form = elgg_view_form('group_tools/members_search', [
	'action' => elgg_http_add_url_query_elements(elgg_get_current_url(), [
		'guid' => $group->guid,
	]),
	'disable_security' => true,
	'prevent_double_submit' => false,
]);

// draw page
echo elgg_view_page(elgg_echo('groups:members:title', [$group->getDisplayName()]), [
	'content' => $form . $list,
	'filter_id' => 'groups/members',
	'filter_value' => $filter,
	'filter_entity' => $group,
]);
