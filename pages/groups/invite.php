<?php
	/**
	 * Invite users to groups
	 * 
	 * @package ElggGroups
	 */

	gatekeeper();
	
	$group_guid = (int) get_input('group_guid');
	
	if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && $group->canEdit()){
		set_page_owner($group->getGUID());
	
		$title = elgg_echo("group_tools:groups:invite");
	
		$area2 = elgg_view_title($title);
		$area2 .= elgg_view("forms/groups/invite", array('entity' => $group));
		
		$body = elgg_view_layout('two_column_left_sidebar', $area1, $area2);
		
		page_draw($title, $body);
	} else {
		register_error(elgg_echo("groups:noaccess"));
		forward(REFERER);
	}
?>