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