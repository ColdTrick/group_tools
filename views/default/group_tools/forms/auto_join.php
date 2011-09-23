<?php

	if(($user = get_loggedin_user()) && $user->isAdmin()){
		if(!empty($vars["entity"])){
			$group = $vars["entity"];
			
			if($auto_join_groups = get_plugin_setting("auto_join", "group_tools")){
				$auto_join_groups = string_to_tag_array($auto_join_groups);
			} else {
				$auto_join_groups = array();
			}
			
			if(in_array($group->getGUID(), $auto_join_groups)){
				$lang_key = "group_tools:auto_join:remove";
			} else {
				$lang_key = "group_tools:auto_join:add";
			}
			
			$link_start = "<a href='" . elgg_add_action_tokens_to_url($vars["url"] . "action/group_tools/toggle_auto_join?group_guid=" . $group->getGUID()) . "'>";
			$link_end = "</a>";
			
			echo elgg_view("page_elements/contentwrapper", array("body" => sprintf(elgg_echo($lang_key), $link_start, $link_end)));
		}
	}