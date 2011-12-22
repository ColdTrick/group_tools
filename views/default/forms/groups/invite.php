<?php
	/**
	 * Elgg groups plugin
	 * 
	 * @package ElggGroups
	 */

	$group = elgg_extract("entity", $vars, elgg_get_page_owner_entity());
	$invite_site_members = elgg_extract("invite", $vars, "no");
	$invite_email = elgg_extract("invite_email", $vars, "no");;
	$invite_csv = elgg_extract("invite_csv", $vars, "no");;
	
	$owner = $group->getOwnerEntity();
	$forward_url = $group->getURL();
	
	if ($friends = elgg_get_logged_in_user_entity()->getFriends("", false)) {
		$friendspicker = elgg_view('input/friendspicker', array('entities' => $friends, 'name' => 'user_guid', 'highlight' => 'all'));
	} else {
		$friendspicker = elgg_echo('groups:nofriendsatall');
	}

	// which options to show
	if(in_array("yes", array($invite_site_members, $invite_email, $invite_csv))){
		$tabs = array(
			"friends" => array(
				"text" => elgg_echo("friends"),
				"href" => "#",
				"rel" => "friends",
				"priority" => 200,
				"onclick" => "group_tools_group_invite_switch_tab(\"friends\");",
				"selected" => true
			)
		);
		
		// invite friends
		$form_data .= "<div id='group_tools_group_invite_friends'>";
		$form_data .= $friendspicker;
		$form_data .= "</div>";

		//invite all site members
		if($invite_site_members == "yes"){
			$tabs["users"] = array(
				"text" => elgg_echo("group_tools:group:invite:users"),
				"href" => "#",
				"rel" => "users",
				"priority" => 300,
				"onclick" => "group_tools_group_invite_switch_tab(\"users\");"
			);
			
			$form_data .= "<div id='group_tools_group_invite_users'>";
			$form_data .= "<div>" . elgg_echo("group_tools:group:invite:users:description") . "</div>";
			$form_data .= elgg_view("input/group_invite_autocomplete", array("name" => "user_guid", 
																				"id" => "group_tools_group_invite_autocomplete",
																				"group_guid" => $group->getGUID(),
																				"relationship" => "site"));
			$form_data .= "</div>";
		}
		
		// invite by email
		if($invite_email == "yes"){
			$tabs["email"] = array(
				"text" => elgg_echo("group_tools:group:invite:email"),
				"href" => "#",
				"rel" => "users",
				"priority" => 400,
				"onclick" => "group_tools_group_invite_switch_tab(\"email\");"
			);
			
			$form_data .= "<div id='group_tools_group_invite_email'>";
			$form_data .= "<div>" . elgg_echo("group_tools:group:invite:email:description") . "</div>";
			$form_data .= elgg_view("input/group_invite_autocomplete", array("name" => "user_guid", 
																				"id" => "group_tools_group_invite_autocomplete_email",
																				"group_guid" => $group->getGUID(),
																				"relationship" => "email"));
			$form_data .= "</div>";
		}
		
		//invite by cvs upload
		if($invite_csv ==  "yes"){
			$tabs["csv"] = array(
				"text" => elgg_echo("group_tools:group:invite:csv"),
				"href" => "#",
				"rel" => "users",
				"priority" => 500,
				"onclick" => "group_tools_group_invite_switch_tab(\"csv\");"
			);
			
			$form_data .= "<div id='group_tools_group_invite_csv'>";
			$form_data .= "<div>" . elgg_echo("group_tools:group:invite:csv:description") . "</div>";
			$form_data .= elgg_view("input/file", array("name" => "csv"));
			$form_data .= "</div>";
		}
		
	} else {
		// only friends
		$form_data .= $friendspicker;
	}
	
	// optional text
	$form_data .= elgg_view_module("aside", elgg_echo("group_tools:group:invite:text"), elgg_view("input/longtext", array("name" => "comment")));
	
	// renotify existing invites
	$form_data .= "<div>";
	$form_data .= "<input type='checkbox' name='resend' value='yes' />";
	$form_data .= "&nbsp;" . elgg_echo("group_tools:group:invite:resend");
	$form_data .= "</div>";
	
	// build tabs
	if(!empty($tabs)){
		foreach($tabs as $name => $tab){
			$tab["name"] = $name;
				
			elgg_register_menu_item("filter", $tab);
		}
		echo elgg_view_menu("filter", array("sort_by" => "priority"));
	}
	
	// show form
	echo $form_data;
	
	// show buttons
	echo '<div class="elgg-foot">';
	echo elgg_view('input/hidden', array('name' => 'forward_url', 'value' => $forward_url));
	echo elgg_view('input/hidden', array('name' => 'group_guid', 'value' => $group->guid));
	echo elgg_view('input/submit', array('value' => elgg_echo('invite')));
	if(elgg_is_admin_logged_in()){
		echo elgg_view("input/submit", array("value" => elgg_echo("group_tools:add_users"), "onclick" => "return confirm(\"" . elgg_echo("group_tools:group:invite:add:confirm") . "\");"));
	}
	echo '</div>';
	
?>
<script type="text/javascript">
	function group_tools_group_invite_switch_tab(tab){
		$('#invite_to_group li').removeClass('elgg-state-selected');

		$('#invite_to_group li.elgg-menu-item-' + tab).addClass('elgg-state-selected');

		switch(tab){
			case "users":
				$('#group_tools_group_invite_friends').hide();
				$('#group_tools_group_invite_email').hide();
				$('#group_tools_group_invite_csv').hide();
				
				$('#group_tools_group_invite_users').show();
				break;
			case "email":
				$('#group_tools_group_invite_friends').hide();
				$('#group_tools_group_invite_users').hide();
				$('#group_tools_group_invite_csv').hide();
				
				$('#group_tools_group_invite_email').show();
				break;
			case "csv":
				$('#group_tools_group_invite_friends').hide();
				$('#group_tools_group_invite_users').hide();
				$('#group_tools_group_invite_email').hide();
				
				$('#group_tools_group_invite_csv').show();
				break;
			default:
				$('#group_tools_group_invite_users').hide();
				$('#group_tools_group_invite_email').hide();
				$('#group_tools_group_invite_csv').hide();
				
				$('#group_tools_group_invite_friends').show();
				break;
		}
	}
</script>