<?php

	if(($user = elgg_get_logged_in_user_entity()) && $user->isAdmin()){
		if(!empty($vars["entity"])){
			$group = $vars["entity"];
			
			$title = elgg_echo("group_tools:auto_join:title");
			
			if($auto_join_groups = elgg_get_plugin_setting("auto_join", "group_tools")){
				$auto_join_groups = string_to_tag_array($auto_join_groups);
			} else {
				$auto_join_groups = array();
			}
			
			$fix_members = "";
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
					
					$fix_members = "<br /><br />" . elgg_echo("group_tools:auto_join:fix", array($link_start, $link_end));
				}
			} else {
				$lang_key = "group_tools:auto_join:add";
			}
			
			$link_start = "<a href='" . elgg_add_action_tokens_to_url($vars["url"] . "action/group_tools/toggle_auto_join?group_guid=" . $group->getGUID()) . "'>";
			$link_end = "</a>";
			
			$body = elgg_echo($lang_key, array($link_start, $link_end));
			$body .= $fix_members;
			
			echo elgg_view_module("info", $title, $body);
		}
	}