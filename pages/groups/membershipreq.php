<?php
/**
 * Manage group invite requests.
 *
 * @package ElggGroups
 */

elgg_gatekeeper();

$guid = (int) get_input('group_guid');
elgg_entity_gatekeeper($guid, 'group');

elgg_set_page_owner_guid($guid);
elgg_group_gatekeeper();

$group = get_entity($guid);

if (!$group->canEdit()) {
	register_error(elgg_echo('groups:noaccess'));
	forward(REFERER);
}

$title = elgg_echo('groups:membershiprequests');

// change page title
if ($group->isPublicMembership()) {
	$title = elgg_echo('group_tools:menu:invitations');
}

// breadcrumb
elgg_push_breadcrumb(elgg_echo('groups'), 'groups/all');
elgg_push_breadcrumb($group->name, $group->getURL());
elgg_push_breadcrumb($title);

// additional title menu item
elgg_register_menu_item('title', [
	'name' => 'groups:invite',
	'href' => 'groups/invite/' . $group->getGUID(),
	'text' => elgg_echo('groups:invite'),
	'link_class' => 'elgg-button elgg-button-action',
]);

// build page elements
$subpage = get_input('subpage');
$offset = (int) get_input('offset');
$limit = (int) get_input('limit', 25);

$dbprefix = elgg_get_config('dbprefix');

elgg_push_context('group_membershipreq');
$menu = elgg_view_menu('filter', [
	'entity' => $group,
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
	'handler' => 'groups'
]);
elgg_pop_context();

switch ($subpage) {
	case 'invites':
		// invited users
		$options = [
			'joins' => [
				"JOIN {$dbprefix}users_entity ue ON e.guid = ue.guid",
			],
			'type' => 'user',
			'relationship' => 'invited',
			'relationship_guid' => $guid,
			'offset' => $offset,
			'limit' => $limit,
			'count' => true,
			'order_by' => 'ue.name ASC',
		];
		
		$count = elgg_get_entities_from_relationship($options);
		unset($options['count']);
		$invitations = elgg_get_entities_from_relationship($options);
		
		$content = elgg_view('group_tools/membershipreq/invites', [
			'invitations' => $invitations,
			'entity' => $group,
			'offset' => $offset,
			'limit' => $limit,
			'count' => $count,
		]);
		
		break;
	case 'email_invites':
		// invited emails
		$options = [
			'selects' => [
				'SUBSTRING_INDEX(v.string, "|", -1) AS invited_email',
			],
			'annotation_name' => 'email_invitation',
			'annotation_owner_guid' => $group->getGUID(),
			'wheres' => [
				'(v.string LIKE "%|%")',
			],
			'offset' => $offset,
			'limit' => $limit,
			'count' => true,
			'order_by' => 'invited_email ASC',
		];
		
		$count = elgg_get_annotations($options);
		unset($options['count']);
		$emails = elgg_get_annotations($options);
		
		$content = elgg_view('group_tools/membershipreq/email_invites', [
			'emails' => $emails,
			'entity' => $group,
			'offset' => $offset,
			'limit' => $limit,
			'count' => $count,
		]);
		
		break;
	default:
		// membership requests
		$options = [
			'joins' => [
				"JOIN {$dbprefix}users_entity ue ON e.guid = ue.guid",
			],
			'type' => 'user',
			'relationship' => 'membership_request',
			'relationship_guid' => $guid,
			'inverse_relationship' => true,
			'offset' => $offset,
			'limit' => $limit,
			'count' => true,
			'order_by' => 'ue.name ASC',
		];
		
		$count = elgg_get_entities_from_relationship($options);
		unset($options['count']);
		$requests = elgg_get_entities_from_relationship($options);
		
		$content = elgg_view('groups/membershiprequests', [
			'requests' => $requests,
			'entity' => $group,
			'offset' => $offset,
			'limit' => $limit,
			'count' => $count,
		]);
		
		break;
}

// build page
$body = elgg_view_layout('content', [
	'content' => $content,
	'title' => $title,
	'filter' => $menu,
]);

// draw page
echo elgg_view_page($title, $body);
