<?php

	$invitations = $vars["invitations"];
	$user = get_loggedin_user();
	
	if (!empty($invitations) && is_array($invitations)) {
		// ignore acces to show group correctly
		$ia = elgg_get_ignore_access();
		elgg_set_ignore_access(true);
		
		foreach($invitations as $group){
			if ($group instanceof ElggGroup) {
				
				$icon = elgg_view("groups/icon", array("entity" => $group, "size" => "small"));
				
				$info = elgg_view("output/url", array("href" => $group->getURL(), "text" => $group->name));
				$info .= "<br />";
				$info .= elgg_view("output/url", array("href" => $vars["url"] . "action/groups/join?user_guid=" . $user->getGUID() . "&group_guid=" . $group->getGUID(), "text" => elgg_echo("accept"), "is_action" => true));
				$info .= " | ";
				$info .= elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/groups/killinvitation?user_guid=" . $user->getGUID() . "&group_guid=" . $group->getGUID(), "confirm" => elgg_echo("groups:invite:remove:check"), "text" => elgg_echo("group_tools:decline")));
				
				echo elgg_view_listing($icon, $info);
			}
		}
		
		// restore access settings
		elgg_set_ignore_access($ia);
	} else {
		echo elgg_view("page_elements/contentwrapper", array("body" => elgg_echo("groups:invitations:none")));
	}
	
	if(get_context() == "groups"){
		$request_options = array(
			"type" => "group",
			"relationship" => "membership_request", 
			"relationship_guid" => $user->getGUID(), 
			"limit" => false
		);
		$requests = elgg_get_entities_from_relationship($request_options);
		
		echo elgg_view_title(elgg_echo("group_tools:group:invitations:request"));
		
		if(!empty($requests)){
			foreach($requests as $group){
				$icon = elgg_view("groups/icon", array("entity" => $group, "size" => "small"));
				
				$info = elgg_view("output/url", array("href" => $group->getURL(), "text" => $group->name));
				$info .= "<br />";
				$info .= elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/groups/killrequest?group_guid=" . $group->getGUID() . "&user_guid=" . $user->getGUID(),
																"text" => elgg_echo("group_tools:revoke"),
																"confirmtext" => elgg_echo("group_tools:group:invitations:request:revoke:confirm")));
				
				echo elgg_view_listing($icon, $info);
			}
		} else {
			echo elgg_view("page_elements/contentwrapper", array("body" => elgg_echo("group_tools:group:invitations:request:non_found")));
		}
		
		// show e-mail invitation form
		if(get_plugin_setting("invite_email", "group_tools") == "yes"){
			
			$form_body = "<div>" . elgg_echo("group_tools:groups:invitation:code:description") . "</div>";
			$form_body .= elgg_view("input/text", array("internalname" => "invitecode", "value" => get_input("invitecode")));
		
			$form_body .= "<div>";
			$form_body .= elgg_view("input/submit", array("value" => elgg_echo("submit")));
			$form_body .= "</div>";
			
			$form = elgg_view("input/form", array("body" => $form_body,
													"action" => $vars["url"] . "action/groups/email_invitation"));
		
			echo elgg_view_title(elgg_echo("group_tools:groups:invitation:code:title"));
			echo elgg_view("page_elements/contentwrapper", array("body" => $form));
		}
	}
