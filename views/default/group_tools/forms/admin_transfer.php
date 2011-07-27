<?php 

	$group = $vars["entity"];
	$user = get_loggedin_user();
	
	if(!empty($group) && ($group instanceof ElggGroup) && !empty($user)){
		if(($group->getOwner() == $user->getGUID()) || $user->isAdmin()){
			$friends_options = array(
				"type" => "user",
				"relationship" => "friend",
				"relationship_guid" => $user->getGUID(),
				"limit" => false,
			);
			
			$member_options = array(
				"type" => "user",
				"relationship" => "member",
				"relationship_guid" => $group->getGUID(),
				"inverse_relationship" => true,
				"limit" => false,
			);
			
			$friends = elgg_get_entities_from_relationship($friends_options);
			$members = elgg_get_entities_from_relationship($member_options);
			
			if(!empty($friends) || !empty($members)){
				
		 		$result .= elgg_echo("group_tools:admin_transfer:transfer") . ": ";
		 		$result .= "<select name='owner_guid'>\n";
		 		
		 		if($group->getOwner() != $user->getGUID()){
		 			$result .= "<option value='" . $user->getGUID() . "'>" . elgg_echo("group_tools:admin_transfer:myself") . "</option>\n";
		 		}
		 		
		 		if(!empty($friends)){
		 			$add_friends = false;
		 			$friends_block .= "<optgroup label='" . elgg_echo("friends") . "'>\n";
			 		
		 			foreach($friends as $friend){
		 				if($group->getOwner() != $friend->getGUID()){
		 					$add_friends = true;
		 					
			 				$friends_block .= "<option value='" . $friend->getGUID() . "'>" . $friend->name . "</option>\n";
		 				}
			 		}
			 		
			 		$friends_block .= "</optgroup>\n";
			 		
			 		if($add_friends){
			 			$result .= $friends_block;
			 		}
		 		}
		 		
		 		if(!empty($members)){
		 			$add_members = false;
		 			
		 			$members_block .= "<optgroup label='" . elgg_echo("groups:members") . "'>\n";
			 		
		 			foreach($members as $member){
		 				if(($group->getOwner() != $member->getGUID()) && ($user->getGUID() != $member->getGUID())){
		 					$add_members = true;
		 					
			 				$members_block .= "<option value='" . $member->getGUID() . "'>" . $member->name . "</option>\n";
		 				}
			 		}
			 		
			 		$members_block .= "</optgroup>\n";
			 		
			 		if($add_members){
			 			$result .= $members_block;
			 		}
		 		}
		 		
		 		$result .= "</select>\n";
		 		$result .= "<br />";
		 		
		 		if($add_friends || $add_members){
			 		
			 		$result .= elgg_view("input/hidden", array("internalname" => "group_guid", "value" => $group->getGUID()));
			 		$result .= elgg_view("input/submit", array("value" => elgg_echo("group_tools:admin_transfer:submit")));
			 		
			 		$result = elgg_view("input/form", array("body" => $result,
			 												"action" => $vars["url"] . "action/group_tools/admin_transfer",
			 												"internalid" => "group_tools_admin_transfer_form"));
		 		} else {
		 			$result = elgg_echo("group_tools:admin_transfer:no_users");
		 		}
		 	} else {
		 		$result = elgg_echo("group_tools:admin_transfer:no_users");
		 	}
		
		 	echo elgg_view("page_elements/contentwrapper", array("body" => $result));
			
		}
	}

?>
<script type="text/javascript">
	$(document).ready(function(){
		$('#group_tools_admin_transfer_form').submit(function(){
			return confirm("<?php echo elgg_echo("group_tools:admin_transfer:confirm"); ?>");
		});
	});
</script>