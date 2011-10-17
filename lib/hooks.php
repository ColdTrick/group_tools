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
	