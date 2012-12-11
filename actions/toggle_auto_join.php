<?php

	$group_guid = (int) get_input("group_guid");
	
	if(!empty($group_guid)){
		if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup)){
			$result = false;
			
			if($auto_join_groups = elgg_get_plugin_setting("auto_join", "group_tools")){
				$auto_join_groups = string_to_tag_array($auto_join_groups);
			} else {
				$auto_join_groups = array();
			}
			
			if(($key = array_search($group_guid, $auto_join_groups)) !== false){
				unset($auto_join_groups[$key]);
			} else {
				$auto_join_groups[] = $group_guid;
			}
			
			if(!empty($auto_join_groups)){
				$result = elgg_set_plugin_setting("auto_join", implode(",", $auto_join_groups), "group_tools");
			} else {
				$result = elgg_unset_plugin_setting("auto_join", "group_tools");
			}
			
			if($result){
				system_message(elgg_echo("group_tools:action:toggle_auto_join:success"));
			} else {
				register_error(elgg_echo("group_tools:action:toggle_auto_join:error:save"));
			}
		} else {
			register_error(elgg_echo("group_tools:action:error:entity"));
		}
	} else {
		register_error(elgg_echo("group_tools:action:error:input"));
	}
	
	forward(REFERER);