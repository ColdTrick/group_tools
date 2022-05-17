<?php
/**
 * Show a list of all group members
 */

use Elgg\Database\QueryBuilder;

$guid = (int) elgg_extract('guid', $vars);

elgg_entity_gatekeeper($guid, 'group');

$group = get_entity($guid);

elgg_set_page_owner_guid($guid);

elgg_push_breadcrumb(elgg_echo('groups'), elgg_generate_url('collection:group:group:all'));
elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());

if ($group->canEdit() && elgg_is_active_plugin('friends')) {
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
			$owner = $qb->compare("e.guid", '=', $group->owner_guid, ELGG_VALUE_GUID);
			
			$admin_query = $qb->subquery('entity_relationships')
				->select('guid_one')
				->where($qb->compare('relationship', '=', 'group_admin', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', $group->guid, ELGG_VALUE_GUID));
			
			$admins = $qb->compare("e.guid", 'IN', $admin_query->getSQL());
			
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
	'action' => elgg_http_add_url_query_elements(current_page_url(), [
		'guid' => $guid,
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
