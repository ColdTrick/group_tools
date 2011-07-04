<?php 

	gatekeeper();
	
	$invitecode = get_input("invitecode");
	
	$user = get_loggedin_user();
	$forward_url = REFERER;
	
	if(!empty($invitecode)){
		$forward_url = $CONFIG->wwwroot . "pg/groups/invitations/" . $user->username;
		
		if($group = group_tools_check_group_email_invitation($invitecode)){
			if($group->join($user)){
				if($annotation = get_annotations($group->getGUID(), "", "", "email_invitation", $invitecode, $group->getGUID(), 1)){
					$annotation[0]->delete();
				}
				
				$forward_url = $group->getURL();
				system_message(elgg_echo("group_tools:action:groups:email_invitation:success"));
			} else {
				register_error(sprintf(elgg_echo("group_tools:action:groups:email_invitation:error:join"), $group->name));
			}
		} else {
			register_error(elgg_echo("group_tools:action:groups:email_invitation:error:code"));
		}
	} else {
		register_error(elgg_echo("group_tools:action:groups:email_invitation:error:input"));
	}

	forward($forward_url);

?>