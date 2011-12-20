<?php

	function group_tools_add_user_acl_hook($hook, $type, $return_value, $params){
		global $GROUP_TOOLS_ACL;
	
		$result = $return_value;
	
		if(!empty($GROUP_TOOLS_ACL) && is_array($result)){
			if(!array_key_exists($GROUP_TOOLS_ACL, $result)){
				$result[$GROUP_TOOLS_ACL] = "temp group";
			}
		}
	
		return $result;
	}

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
				case "":
					$result = false;
					
					set_input("group_guid", $page[1]);
					
					include(dirname(dirname(__FILE__)) . "/pages/groups/membershipreq.php");
					break;
			}
		}
		
		return $result;
	}
	