<?php
/**
 * Manage group invite requests.
 *
 * @package ElggGroups
 */

gatekeeper();

$guid = (int) get_input("group_guid");

elgg_set_page_owner_guid($guid);

$group = get_entity($guid);

$title = elgg_echo("groups:membershiprequests");

if (!empty($group) && elgg_instanceof($group, "group") && $group->canEdit()) {
	// change page title
	if ($group->isPublicMembership()) {
		$title = elgg_echo("group_tools:menu:invitations");
	}
	
	elgg_push_breadcrumb(elgg_echo("groups"), "groups/all");
	elgg_push_breadcrumb($group->name, $group->getURL());
	elgg_push_breadcrumb($title);

	// membership requests
	$requests = elgg_get_entities_from_relationship(array(
		"type" => "user",
		"relationship" => "membership_request",
		"relationship_guid" => $guid,
		"inverse_relationship" => true,
		"limit" => false,
	));
	
	// invited users
	$invitations = elgg_get_entities_from_relationship(array(
		"type" => "user",
		"relationship" => "invited",
		"relationship_guid" => $guid,
		"limit" => false
	));
	
	// invited emails
	$emails = elgg_get_annotations(array(
		"annotation_name" => "email_invitation",
		"annotation_owner_guid" => $group->getGUID(),
		"wheres" => array("(v.string LIKE '%|%')"),
		"limit" => false
	));
		
	$content = elgg_view("groups/membershiprequests", array(
		"requests" => $requests,
		"invitations" => $invitations,
		"entity" => $group,
		"emails" => $emails
	));

} else {
	$content = elgg_echo("groups:noaccess");
}

$params = array(
	"content" => $content,
	"title" => $title,
	"filter" => "",
);
$body = elgg_view_layout("content", $params);

echo elgg_view_page($title, $body);
