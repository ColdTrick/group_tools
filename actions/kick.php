<?php
	
	global $GROUP_TOOLS_ACL;

	gatekeeper();
	
	$user_guid = (int) get_input("user_guid", 0);
	$group_guid = (int) get_input("group_guid", 0);
	
	$user = get_entity($user_guid);		
	$group = get_entity($group_guid);
	
	if (($user instanceof ElggUser) && ($group instanceof ElggGroup)){
		// can't kick a group owner
		if ($group->canEdit() && ($group->getOwner() != $user->getGUID())) {
			// make sure the user is removed from correct acl
			register_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
			$GROUP_TOOLS_ACL = $group->group_acl;
			
			if ($group->leave($user)){
				system_message(elgg_echo("group_tools:action:kick:success"));
			} else {
				register_error(elgg_echo("group_tools:action:kick:error"));
			}
			
			// cleanup hook
			unregister_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
		} else {
			register_error(elgg_echo("group_tools:action:kick:error"));
		}
	} else {
		register_error(elgg_echo("group_tools:action:error:input"));
	}
		
	forward(REFERER);
?>