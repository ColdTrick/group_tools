<?php

	function group_tools_multiple_admin_can_edit_hook($hook, $type, $return_value, $params){
		$result = $return_value;
	
		if(!empty($params) && is_array($params) && !$result){
			if(array_key_exists("entity", $params) && array_key_exists("user", $params)){
				$entity = $params["entity"];
				$user = $params["user"];
	
				if(($entity instanceof ElggGroup) && ($user instanceof ElggUser)){
					if($entity->isMember($user) && check_entity_relationship($user->getGUID(), "group_admin", $entity->getGUID())){
						$result = true;
					}
				}
			}
		}
	
		return $result;
	}
	
	function group_tools_route_groups_handler($hook, $type, $return_value, $params){
		/**
		 * $return_value contains:
		 * $return_value['handler'] => requested handler
		 * $return_value['segments'] => url parts ($page)
		 */
		$result = $return_value;
		
		if(!empty($return_value) && is_array($return_value)){
			$page = $return_value['segments'];
			
			switch($page[0]){
				case "all":
					$filter = get_input("filter");
					
					if(empty($filter) && ($default_filter = elgg_get_plugin_setting("group_listing", "group_tools"))){
						$filter = $default_filter;
						set_input("filter", $default_filter);
					}
					
					if(in_array($filter, array("open", "closed", "alfa"))){
						// we will handle the output
						$result = false;
						
						include(dirname(dirname(__FILE__)) . "/pages/groups/all.php");
					}
					
					break;
				case "requests":
					$result = false;
					
					set_input("group_guid", $page[1]);
					
					include(dirname(dirname(__FILE__)) . "/pages/groups/membershipreq.php");
					break;
				case "invite":
					$result = false;
					
					set_input("group_guid", $page[1]);
					
					include(dirname(dirname(__FILE__)) . "/pages/groups/invite.php");
					break;
				case "mail":
					$result = false;
					
					set_input("group_guid", $page[1]);
						
					include(dirname(dirname(__FILE__)) . "/pages/mail.php");
					break;
				case "group_invite_autocomplete":
					$result = false;
					
					include(dirname(dirname(__FILE__)) . "/procedures/group_invite_autocomplete.php");
					break;
			}
		}
		
		return $result;
	}
	
	function group_tools_menu_title_handler($hook, $type, $return_value, $params){
		$result = $return_value;
		
		$page_owner = elgg_get_page_owner_entity();
		$user = elgg_get_logged_in_user_entity();
		
		if(!empty($page_owner) && !empty($user) && ($page_owner instanceof ElggGroup) && elgg_in_context("groups")){
			if(!empty($result) && is_array($result)){
				foreach($result as $menu_item){
					switch($menu_item->getText()){
						case elgg_echo("groups:joinrequest"):
							if(check_entity_relationship($user->getGUID(), "membership_request", $page_owner->getGUID())){
								$menu_item->setText(elgg_echo("group_tools:joinrequest:already"));
								$menu_item->setTooltip(elgg_echo("group_tools:joinrequest:already:tooltip"));
								$menu_item->setHref(elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/groups/killrequest?user_guid=" . $user->getGUID() . "&group_guid=" . $page_owner->getGUID()));
							}
							
							break;
						case elgg_echo("groups:invite"):
							$invite = elgg_get_plugin_setting("invite", "group_tools");
							$invite_email = elgg_get_plugin_setting("invite_email", "group_tools");
							$invite_csv = elgg_get_plugin_setting("invite_csv", "group_tools");
							
							if(in_array("yes", array($invite, $invite_csv, $invite_email))){
								$menu_item->setText(elgg_echo("group_tools:groups:invite"));
							}
							
							break;
					}
				}
			}
		}
		
		return $result;
	}
	
	function group_tools_menu_entity_handler($hook, $type, $return_value, $params){
		$result = $return_value;
		
		$page_owner = elgg_get_page_owner_entity();
		$loggedin_user = elgg_get_logged_in_user_entity();
		
		if(!empty($page_owner) && ($page_owner instanceof ElggGroup) && !empty($loggedin_user)){
			// are multiple admins allowed
			if(elgg_get_plugin_setting("multiple_admin", "group_tools") == "yes"){
				if(!empty($params) && is_array($params)){
					$user = $params["entity"];
					
					// do we have a user
					if(!empty($user) && ($user instanceof ElggUser)){
						// is the user not the owner of the group and noet the current user
						if($page_owner->getOwnerGUID() != $user->getGUID() && $user->getGUID() != $loggedin_user->getGUID()){
							// is the user a member od this group
							if($page_owner->isMember($user)){
								// can we add/remove an admin
								if(($page_owner->getOwnerGUID() == $loggedin_user->getGUID()) || ($page_owner->group_multiple_admin_allow_enable == "yes" && $page_owner->canEdit()) || $loggedin_user->isAdmin()){
									if(check_entity_relationship($user->getGUID(), "group_admin", $page_owner->getGUID())){
										$text = elgg_echo("group_tools:multiple_admin:profile_actions:remove");
									} else {
										$text = elgg_echo("group_tools:multiple_admin:profile_actions:add");
									}
									
									$result[] = ElggMenuItem::factory(array(
										"text" => $text,
										"name" => "group_admin",
										"href" => elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/group_tools/toggle_admin?group_guid=" . $page_owner->getGUID() . "&user_guid=" . $user->getGUID())
									));
								}
							}
						}
					}
				}
			}
		}
		
		return $result;
	}
	