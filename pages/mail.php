<?php 

	gatekeeper();
	
	$group_guid = (int) get_input("group_guid", 0);

	if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && ($group->canEdit())){
		// set page owner
		set_page_owner($group->getGUID());
		set_context("groups");
		
		// get members
		$members = $group->getMembers(false);
		
		// build page elements
		$title_text = elgg_echo("group_tools:mail:title");
		$title = elgg_view_title($title_text);
		
		$form = elgg_view("group_tools/forms/mail", array("entity" => $group, "members" => $members));
		
		$page_data = $title . $form;
		
		page_draw($title_text, elgg_view_layout("two_column_left_sidebar", "", $page_data));
	} else {
		forward(REFERER);
	}

?>