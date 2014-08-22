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

	$dbprefix = elgg_get_config("dbprefix");
	
	// membership requests
	$requests = elgg_get_entities_from_relationship(array(
		"joins" => array("JOIN " . $dbprefix . "users_entity ue ON e.guid = ue.guid"),
		"type" => "user",
		"relationship" => "membership_request",
		"relationship_guid" => $guid,
		"inverse_relationship" => true,
		"limit" => false,
		"order_by" => "ue.name ASC"
	));
	
	// invited users
	$invitations = elgg_get_entities_from_relationship(array(
		"joins" => array("JOIN " . $dbprefix . "users_entity ue ON e.guid = ue.guid"),
		"type" => "user",
		"relationship" => "invited",
		"relationship_guid" => $guid,
		"limit" => false,
		"order_by" => "ue.name ASC"
	));
	
	// invited emails
	$emails = elgg_get_annotations(array(
		"selects" => array("SUBSTRING_INDEX(v.string, '|', -1) AS invited_email"),
		"annotation_name" => "email_invitation",
		"annotation_owner_guid" => $group->getGUID(),
		"wheres" => array("(v.string LIKE '%|%')"),
		"limit" => false,
		"order_by" => "invited_email ASC"
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
