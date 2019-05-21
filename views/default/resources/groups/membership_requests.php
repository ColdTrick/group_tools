<?php
/**
 * List all the groups a user is invited for
 */

use Elgg\EntityPermissionsException;

$user = elgg_get_page_owner_entity();
if (!$user instanceof ElggUser || !$user->canEdit()) {
	throw new EntityPermissionsException();
}

elgg_push_collection_breadcrumbs('group', 'group');

$title = elgg_echo('group_tools:group:invitations:request');

$content = elgg_list_entities([
	'type' => 'group',
	'relationship' => 'membership_request',
	'relationship_guid' => $user->guid,
	'item_view' => 'group_tools/format/membershiprequest',
	'no_results' => elgg_echo('group_tools:group:invitations:request:non_found'),
]);

$body = elgg_view_layout('default', [
	'title' => $title,
	'content' => $content,
	'filter_id' => 'group:invitations',
	'filter_value' => 'requests',
]);

echo elgg_view_page($title, $body);
