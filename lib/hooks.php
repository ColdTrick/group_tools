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
		static $group_admin_cache;
		
		$result = $return_value;
	
		if(!empty($params) && is_array($params) && !$result){
			if(array_key_exists("entity", $params) && array_key_exists("user", $params)){
				$entity = $params["entity"];
				$user = $params["user"];
	
				if(($entity instanceof ElggGroup) && ($user instanceof ElggUser)){
					if(!isset($group_admin_cache)){
						$group_admin_cache = array();
					}
					
					if(!isset($group_admin_cache[$user->getGUID()])){
						$group_admin_cache[$user->getGUID()] = array();
					}
					
					if(!isset($group_admin_cache[$user->getGUID()][$entity->getGUID()])){
						if($entity->isMember($user) && check_entity_relationship($user->getGUID(), "group_admin", $entity->getGUID())){
							$group_admin_cache[$user->getGUID()][$entity->getGUID()] = true;
						} else {
							$group_admin_cache[$user->getGUID()][$entity->getGUID()] = false;
						}
					}
					
					$result = $group_admin_cache[$user->getGUID()][$entity->getGUID()];
				}
			}
		}
	
		return $result;
	}
	