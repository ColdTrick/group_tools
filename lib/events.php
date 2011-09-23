<?php

	function group_tools_join_group_event($event, $type, $params){
		
		if(!empty($params) && is_array($params)){
			if(!empty($params["group"]) && !empty($params["user"])){
				$group = $params["group"];
				$user = $params["user"];
				
				if(($user instanceof ElggUser) && ($group instanceof ElggGroup)) {
					// enable email notification
					add_entity_relationship($user->getGUID(), "notifyemail", $group->getGUID());
					
					if(is_plugin_enabled("messages")){
						// enable site/messages notification
						add_entity_relationship($user->getGUID(), "notifysite", $group->getGUID());
					}
				}
			}
		}
	}
	
	function group_tools_join_site_handler($event, $type, $relationship){
		global $GROUP_TOOLS_ACL;
		
		if(!empty($relationship) && ($relationship instanceof ElggRelationship)){
			$user_guid = $relationship->guid_one;
			$site_guid = $relationship->guid_two;
			
			if(($user = get_user($user_guid)) && ($auto_joins = get_plugin_setting("auto_join", "group_tools"))){
				$auto_joins = string_to_tag_array($auto_joins);
				
				// ignore access
				$ia = elgg_get_ignore_access();
				elgg_set_ignore_access(true);
				
				foreach ($auto_joins as $group_guid) {
					if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup)){
						if($group->site_guid == $site_guid){
							// need to be able to add user to the group acl
							register_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
							$GROUP_TOOLS_ACL = $group->group_acl;
							
							// join the group
							$group->join($user);
							
							// undo temp acl hook
							$GROUP_TOOLS_ACL = null;
							unregister_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
						}
					}
				}
				
				// restore access settings
				elgg_set_ignore_access($ia);
			}
		}
	}