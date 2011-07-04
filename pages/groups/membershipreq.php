<?php
	/**
	 * Manage group invite requests.
	 * 
	 * @package ElggGroups
	 */

	gatekeeper();
	
	$group_guid = (int) get_input("group_guid");
	
	if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && $group->canEdit()){
		set_page_owner($group_guid);
	
		$request_options = array(
			"type" => "user",
			"relationship" => "membership_request", 
			"relationship_guid" => $group_guid, 
			"inverse_relationship" => true, 
			"limit" => false
		);
		$requests = elgg_get_entities_from_relationship($request_options);
		
		$invitation_options = array(
			"type" => "user",
			"relationship" => "invited", 
			"relationship_guid" => $group_guid, 
			"limit" => false
		);
		$invitations = elgg_get_entities_from_relationship($invitation_options);
		
		$title = elgg_echo("groups:membershiprequests");
	
		$area2 = elgg_view_title($title);
		$area2 .= elgg_view("groups/membershiprequests",array("requests" => $requests, "entity" => $group, "invitations" => $invitations));
		
		$body = elgg_view_layout("two_column_left_sidebar", $area1, $area2);
		
		page_draw($title, $body);
	} else {
		register_error(elgg_echo("groups:noaccess"));
		forward(REFERER);
	}
?>