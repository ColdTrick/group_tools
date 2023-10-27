<?php
/**
 * List all invited email addresses for the group
 */

use Elgg\Database\QueryBuilder;
use Elgg\Database\Clauses\OrderByClause;

/* @var $group \ElggGroup */
$group = elgg_get_page_owner_entity();

elgg_push_entity_breadcrumbs($group);

// additional title menu item
if ($group->canEdit() && elgg_is_active_plugin('friends')) {
	elgg_register_menu_item('title', [
		'name' => 'groups:invite',
		'icon' => 'user-plus',
		'href' => elgg_generate_entity_url($group, 'invite'),
		'text' => elgg_echo('groups:invite'),
		'link_class' => 'elgg-button elgg-button-action',
	]);
}

$offset = (int) get_input('offset', 0);
$limit = (int) get_input('limit', 25);

// build page elements
$content = elgg_list_annotations([
	'selects' => [
		function(QueryBuilder $qb, $main_alias) {
			return "SUBSTRING_INDEX({$main_alias}.value, '|', -1) AS invited_email";
		},
	],
	'annotation_name' => 'email_invitation',
	'annotation_owner_guid' => $group->guid,
	'wheres' => [
		function(QueryBuilder $qb, $main_alias) {
			return $qb->compare("{$main_alias}.value", 'LIKE', '%|%');
		},
	],
	'offset' => $offset,
	'limit' => $limit,
	'count' => true,
	'order_by' => new OrderByClause('invited_email', 'ASC'),
	'no_results' => elgg_echo('group_tools:groups:membershipreq:email_invitations:none'),
]);

// draw page
echo elgg_view_page(elgg_echo('collection:annotation:email_invitation:group'),  [
	'content' => $content,
	'filter_id' => 'groups/members',
	'filter_value' => 'email_invitations',
	'filter_entity' => $group,
	'filter_sorting' => false,
]);
