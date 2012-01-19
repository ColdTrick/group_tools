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
				
				// check if all users are member of this group
				$options = array(
					"type" => "user",
					"relationship" => "member_of_site",
					"relationship_guid" => $group->site_guid,
					"inverse_relationship" => true,
					"count" => true
				);
				
				$user_count = elgg_get_entities_from_relationship($options);
				$member_count = $group->getMembers(0, 0, true);
				
				if($user_count != $member_count){
					
					$link_start = "<a href='" . elgg_add_action_tokens_to_url($vars["url"] . "action/group_tools/fix_auto_join?group_guid=" . $group->getGUID()) . "'>";
					$link_end = "</a>";
					
					$fix_members = "<br /><br />" . sprintf(elgg_echo("group_tools:auto_join:fix"), $link_start, $link_end);
				}
			} else {
				$lang_key = "group_tools:auto_join:add";
			}
			
			$link_start = "<a href='" . elgg_add_action_tokens_to_url($vars["url"] . "action/group_tools/toggle_auto_join?group_guid=" . $group->getGUID()) . "'>";
			$link_end = "</a>";
			
			$body = "<h3 class='settings'>" . elgg_echo("group_tools:auto_join:title") . "</h3>";
			$body .= sprintf(elgg_echo($lang_key), $link_start, $link_end);
			$body .= $fix_members;
			
			echo elgg_view("page_elements/contentwrapper", array("body" => $body));
		}
	}