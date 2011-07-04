<?php 

	$user = $vars["entity"];
	
	$loggedin_user = get_loggedin_user();
	$page_owner = page_owner_entity();

	if(($page_owner instanceof ElggGroup) && !empty($loggedin_user)){
		// multiple admin options
		if(get_plugin_setting("multiple_admin", "group_tools") == "yes"){
			if(($page_owner->getOwner() == $loggedin_user->getGUID()) || ($page_owner->group_multiple_admin_allow_enable == "yes" && $page_owner->canEdit()) || $loggedin_user->isAdmin()){
				if($page_owner->isMember($user) && ($page_owner->getOwner() != $user->getGUID())){
					if(check_entity_relationship($user->getGUID(), "group_admin", $page_owner->getGUID())){
						$text = elgg_echo("group_tools:multiple_admin:profile_actions:remove");
					} else {
						$text = elgg_echo("group_tools:multiple_admin:profile_actions:add");
					}
					
					echo elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/group_tools/toggle_admin?group_guid=" . $page_owner->getGUID() . "&user_guid=" . $user->getGUID(), "text" => $text));
				}
			}
		}
		
		// group kick options
		if($page_owner->canEdit() && ($page_owner->isMember($user)) && ($page_owner->getOwner() != $user->getGUID()) && ($loggedin_user->getGUID() != $user->getGUID())){
			echo elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/group_tools/kick?user_guid=" . $user->getGUID() . "&group_guid=" . $page_owner->getGUID(), "text" => elgg_echo("group_tools:group_kick:profile_actions:kick")));
		}
	}
	
	
	

?>