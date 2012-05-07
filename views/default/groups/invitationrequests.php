<?php

	/**
	* A user"s group invitations
	*
	* @uses $vars["invitations"] Array of ElggGroups
	*/

	$user = elgg_get_logged_in_user_entity();

	if (!empty($vars["invitations"]) && is_array($vars["invitations"])) {
		
		echo "<ul class='elgg-list'>";
		
		foreach ($vars["invitations"] as $group) {
			if ($group instanceof ElggGroup) {
				$icon = elgg_view_entity_icon($group, "tiny", array("use_hover" => "true"));
	
				$group_title = elgg_view("output/url", array(
					"href" => $group->getURL(),
					"text" => $group->name,
					"is_trusted" => true,
				));
	
				$url = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/groups/join?user_guid={$user->guid}&group_guid={$group->guid}");
				$accept_button = elgg_view("output/url", array(
					"href" => $url,
					"text" => elgg_echo("accept"),
					"class" => "elgg-button elgg-button-submit",
					"is_trusted" => true,
				));
	
				$url = "action/groups/killinvitation?user_guid={$user->getGUID()}&group_guid={$group->getGUID()}";
				$delete_button = elgg_view("output/confirmlink", array(
						"href" => $url,
						"confirm" => elgg_echo("groups:invite:remove:check"),
						"text" => elgg_echo("delete"),
						"class" => "elgg-button elgg-button-delete mlm",
				));
	
				$body = "<h4>$group_title</h4>";
				$body .= "<p class='elgg-subtext'>$group->briefdescription</p>";
	
				$alt = $accept_button . $delete_button;
	
				echo "<li class='pvs'>";
				echo elgg_view_image_block($icon, $body, array("image_alt" => $alt));
				echo "</li>";
			}
		}
		
		echo "</ul>";
	} else {
		echo "<p class='mtm'>" . elgg_echo("groups:invitations:none") . "</p>";
	}

	if(elgg_get_context() == "groups"){
		// list membership requests
		$request_options = array(
			"type" => "group",
			"relationship" => "membership_request", 
			"relationship_guid" => $user->getGUID(), 
			"limit" => false
		);
		$requests = elgg_get_entities_from_relationship($request_options);
		
		$title = elgg_echo("group_tools:group:invitations:request");
		
		if(!empty($requests)){
			$content = "<ul class='elgg-list'>";
			
			foreach($requests as $group){
				$icon = elgg_view_entity_icon($group, "tiny", array("use_hover" => "true"));
				
				$group_title = elgg_view("output/url", array(
									"href" => $group->getURL(),
									"text" => $group->name,
									"is_trusted" => true,
				));
				
				$url = "action/groups/delete_request?user_guid={$user->getGUID()}&group_guid={$group->getGUID()}";
				$delete_button = elgg_view("output/confirmlink", array(
										"href" => $url,
										"confirm" => elgg_echo("group_tools:group:invitations:request:revoke:confirm"),
										"text" => elgg_echo("group_tools:revoke"),
										"class" => "elgg-button elgg-button-delete mlm",
				));
				
				$body = "<h4>$group_title</h4>";
				$body .= "<p class='elgg-subtext'>$group->briefdescription</p>";
				
				$alt = $delete_button;
				
				$content .= "<li class='pvs'>";
				$content .= elgg_view_image_block($icon, $body, array("image_alt" => $alt));
				$content .= "</li>";
			}
			
			$content .= "</ul>";
		} else {
			$content = elgg_echo("group_tools:group:invitations:request:non_found");
		}
		
		echo elgg_view_module("info", $title, $content);
		
		// show e-mail invitation form
		if(elgg_get_plugin_setting("invite_email", "group_tools") == "yes"){
			
			$form_body = "<div>" . elgg_echo("group_tools:groups:invitation:code:description") . "</div>";
			$form_body .= elgg_view("input/text", array("name" => "invitecode", "value" => get_input("invitecode")));
		
			$form_body .= "<div>";
			$form_body .= elgg_view("input/submit", array("value" => elgg_echo("submit")));
			$form_body .= "</div>";
			
			$form = elgg_view("input/form", array("body" => $form_body,
													"action" => $vars["url"] . "action/groups/email_invitation"));
		
			echo elgg_view_module("info", elgg_echo("group_tools:groups:invitation:code:title"), $form);
		}
	}
