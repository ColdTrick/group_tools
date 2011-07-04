<?php

	$requests = $vars["requests"];
	$invitations = $vars["invitations"];
	$group = $vars["entity"];
	
	// outstanding join requests
	$list = "<h3 class='settings'>" . elgg_echo("group_tools:groups:membershipreq:requests") . "</h3>";

	if (!empty($requests) && is_array($requests)) {
		foreach($requests as $user){
			$icon = elgg_view("profile/icon", array("entity" => $user, "size" => "small"));
			
			$info = elgg_view("output/url", array("href" => $user->getURL(), "text" => $user->name));
			$info .= "<br />";
			$info .= elgg_view("output/url", array("href" => $vars["url"] . "action/groups/addtogroup?user_guid=" . $user->getGUID() . "&group_guid=" . $group->getGUID(), 
													"text" => elgg_echo("accept"),
													"is_action" => true));
			$info .= " | ";
			$info .= elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/groups/killrequest?user_guid=" . $user->getGUID() . "&group_guid=" . $group->getGUID(),
															"confirm" => elgg_echo("groups:joinrequest:remove:check"),
															"text" => elgg_echo("group_tools:decline")));
			
			$list .= elgg_view_listing($icon, $info);
		}
	} else {
		$list .= "<p>" . elgg_echo("groups:requests:none") . "</p>";
	}
	
	// outstanding invitations
	$list .= "<h3 class='settings'>" . elgg_echo("group_tools:groups:membershipreq:invitations") . "</h3>";
	
	if(!empty($invitations) && is_array($invitations)){
		foreach($invitations as $user){
			$icon = elgg_view("profile/icon", array("entity" => $user, "size" => "small"));
			
			$info = elgg_view("output/url", array("href" => $user->getURL(), "text" => $user->name));
			$info .= "<br />";
			$info .= elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/groups/killinvitation?user_guid=" . $user->getGUID() . "&group_guid=" . $group->getGUID(),
															"confirm" => elgg_echo("group_tools:groups:membershipreq:invitations:revoke:confirm"),
															"text" => elgg_echo("group_tools:revoke")));
			
			$list .= elgg_view_listing($icon, $info);
		}
	} else {
		$list .= "<p>" . elgg_echo("group_tools:groups:membershipreq:invitations:none") . "</p>";
	}

?>
<div id="group_tools_group_membershipreq" class="contentWrapper">
	<?php echo $list; ?>
</div>