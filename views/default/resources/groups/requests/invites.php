<?php
/**
 * List all invited user for the group
 */

use Elgg\EntityPermissionsException;

elgg_gatekeeper();

$guid = elgg_extract('guid', $vars);

elgg_entity_gatekeeper($guid, 'group');
$group = get_entity($guid);
if (!$group->canEdit()) {
	throw new EntityPermissionsException();
}

elgg_push_breadcrumb(elgg_echo('groups'), elgg_generate_url('collection:group:group:all'));

elgg_set_page_owner_guid($guid);

$title = elgg_echo('group_tools:menu:invitations');

elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());
elgg_push_breadcrumb($title);

// additional title menu item
elgg_register_menu_item('title', [
	'name' => 'groups:invite',
	'href' => elgg_generate_url('invite:group:group', [
		'guid' => $group->guid,
	]),
	'text' => elgg_echo('groups:invite'),
	'link_class' => 'elgg-button elgg-button-action',
]);

$offset = (int) get_input('offset', 0);
$limit = (int) get_input('limit', 25);

// get invited users
$options = [
	'type' => 'user',
	'relationship' => 'invited',
	'relationship_guid' => $group->guid,
	'offset' => $offset,
	'limit' => $limit,
	'count' => true,
	'order_by_metadata' => [
		'name' => 'name',
		'direction' => 'ASC',
	],
];

$count = elgg_get_entities($options);
unset($options['count']);
$invitations = elgg_get_entities($options);

$content = elgg_view('group_tools/membershipreq/invites', [
	'invitations' => $invitations,
	'entity' => $group,
	'offset' => $offset,
	'limit' => $limit,
	'count' => $count,
]);

$tabs = elgg_view_menu('group:membershiprequests', [
	'entity' => $group,
	'sort_by' => 'priority',
	'class' => 'elgg-tabs',
]);

$body = elgg_view_layout('content', [
	'content' => $content,
	'title' => $title,
	'filter' => $tabs,
]);

echo elgg_view_page($title, $body);
