<?php

	function group_tools_join_group_event($event, $type, $params){
		static $auto_notification;
		
		// only load plugin setting once
		if(!isset($auto_notification)){
			$auto_notification = false;
			
			if(elgg_get_plugin_setting("auto_notification", "group_tools") == "yes"){
				$auto_notification = true;
			}
		}
		
		if(!empty($params) && is_array($params)){
			$group = elgg_extract("group", $params);
			$user = elgg_extract("user", $params);
			
			if(($user instanceof ElggUser) && ($group instanceof ElggGroup)) {
				if($auto_notification){
					// enable email notification
					add_entity_relationship($user->getGUID(), "notifyemail", $group->getGUID());
					
					if(elgg_is_active_plugin("messages")){
						// enable site/messages notification
						add_entity_relationship($user->getGUID(), "notifysite", $group->getGUID());
					}
				}
				
				// cleanup invites 
				if(check_entity_relationship($group->getGUID(), "invited", $user->getGUID())){
					remove_entity_relationship($group->getGUID(), "invited", $user->getGUID());
				}
				
				// and requests
				if(check_entity_relationship($user->getGUID(), "membership_request", $group->getGUID())){
					remove_entity_relationship($user->getGUID(), "membership_request", $group->getGUID());
				}
			}
		}
	}
	
	function group_tools_join_site_handler($event, $type, $relationship){
		
		if(!empty($relationship) && ($relationship instanceof ElggRelationship)){
			$user_guid = $relationship->guid_one;
			$site_guid = $relationship->guid_two;
			
			if(($user = get_user($user_guid)) && ($auto_joins = elgg_get_plugin_setting("auto_join", "group_tools"))){
				$auto_joins = string_to_tag_array($auto_joins);
				
				// ignore access
				$ia = elgg_get_ignore_access();
				elgg_set_ignore_access(true);
				
				foreach ($auto_joins as $group_guid) {
					if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup)){
						if($group->site_guid == $site_guid){
							// join the group
							$group->join($user);
						}
					}
				}
				
				// restore access settings
				elgg_set_ignore_access($ia);
			}
		}
	}
	
	function group_tools_multiple_admin_group_leave($event, $type, $params){
	
		if(!empty($params) && is_array($params)){
			if(array_key_exists("group", $params) && array_key_exists("user", $params)){
				$entity = $params["group"];
				$user = $params["user"];
	
				if(($entity instanceof ElggGroup) && ($user instanceof ElggUser)){
					if(check_entity_relationship($user->getGUID(), "group_admin", $entity->getGUID())){
						return remove_entity_relationship($user->getGUID(), "group_admin", $entity->getGUID());
					}
				}
			}
		}
	}
	