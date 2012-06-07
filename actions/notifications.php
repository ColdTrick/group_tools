<?php

	$toggle = get_input("toggle");
	$guid = (int) get_input("guid");
	
	$forward_url = REFERER;
	
	if(!empty($guid) && !empty($toggle)){
		if(($group = get_entity($guid)) && elgg_instanceof($group, "group")){
			// get group members
			if($members = $group->getMembers(false)){
				
				// check if we need to do stuff with messages
				$messages_enabled = elgg_is_active_plugin("messages");
				
				if($toggle == "enable"){
					// enable notification for everyone
					foreach($members as $member){
						// add email notification
						add_entity_relationship($member->getGUID(), "notifyemail", $group->getGUID());
						
						if($messages_enabled){
							add_entity_relationship($member->getGUID(), "notifysite", $group->getGUID());
						}
					}
					
					system_message(elgg_echo("group_tools:action:notifications:success:enable"));
					$forward_url = $group->getURL();
				} elseif($toggle == "disable"){
					// disable notification for everyone
					foreach($members as $member){
						// add email notification
						remove_entity_relationship($member->getGUID(), "notifyemail", $group->getGUID());
				
						if($messages_enabled){
							remove_entity_relationship($member->getGUID(), "notifysite", $group->getGUID());
						}
					}
					
					system_message(elgg_echo("group_tools:action:notifications:success:disable"));
					$forward_url = $group->getURL();
				} else {
					register_error(elgg_echo("group_tools:action:notifications:error:toggle"));
				}
			}
		} else {
			register_error(elgg_echo("group_tools:action:error:entity"));
		}
	} else {
		register_error(elgg_echo("group_tools:action:error:input"));
	}
	
	forward($forward_url);