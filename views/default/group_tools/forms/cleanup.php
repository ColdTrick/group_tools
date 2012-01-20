<?php
	
	$group = $vars["entity"];
	
	if(!empty($group) && ($group instanceof ElggGroup) && $group->canEdit()){
		
		$noyes_options = array(
			"no" => elgg_echo("option:no"),
			"yes" => elgg_echo("option:yes")
		);
		
		$featured_options = array(
			"no" => elgg_echo("option:no"),
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 5,
			6 => 6,
			7 => 7,
			8 => 8,
			9 => 9,
			10 => 10,
			15 => 15,
			20 => 20,
			25 => 25
		);
		
		$prefix = "group_tools:cleanup:";
		
		// cleanup owner block
		$form_body = "<div>";
		$form_body .= elgg_echo("group_tools:cleanup:owner_block");
		$form_body .= ":&nbsp;" . elgg_view("input/pulldown", array("internalname" => "owner_block", "options_values" => $noyes_options, "value" => $group->getPrivateSetting($prefix . "owner_block")));
		$form_body .= "<img src='" . $vars["url"] . "_graphics/icon_customise_info.gif' class='group_tools_explain' alt='" . elgg_echo("group_tools:explain") . "' title='" . elgg_echo("group_tools:cleanup:owner_block:explain") . "' onmouseover='group_tools_cleanup_highlight(\"owner_block\");' onmouseout='group_tools_cleanup_unhighlight(\"owner_block\");' />";
		$form_body .= "</div>";
		
		// hide group actions
		$form_body .= "<div>";
		$form_body .= elgg_echo("group_tools:cleanup:actions");
		$form_body .= ":&nbsp;" . elgg_view("input/pulldown", array("internalname" => "actions", "options_values" => $noyes_options, "value" => $group->getPrivateSetting($prefix . "actions")));
		$form_body .= "<img src='" . $vars["url"] . "_graphics/icon_customise_info.gif' class='group_tools_explain' alt='" . elgg_echo("group_tools:explain") . "' title='" . elgg_echo("group_tools:cleanup:actions:explain") . "' onmouseover='group_tools_cleanup_highlight(\"actions\");' onmouseout='group_tools_cleanup_unhighlight(\"actions\");' />";
		$form_body .= "</div>";
		
		// hide group menu items
		$form_body .= "<div>";
		$form_body .= elgg_echo("group_tools:cleanup:menu");
		$form_body .= ":&nbsp;" . elgg_view("input/pulldown", array("internalname" => "menu", "options_values" => $noyes_options, "value" => $group->getPrivateSetting($prefix . "menu")));
		$form_body .= "<img src='" . $vars["url"] . "_graphics/icon_customise_info.gif' class='group_tools_explain' alt='" . elgg_echo("group_tools:explain") . "' title='" . elgg_echo("group_tools:cleanup:menu:explain") . "' onmouseover='group_tools_cleanup_highlight(\"menu\");' onmouseout='group_tools_cleanup_unhighlight(\"menu\");' />";
		$form_body .= "</div>";
		
		// hide group members
		$form_body .= "<div>";
		$form_body .= elgg_echo("group_tools:cleanup:members");
		$form_body .= ":&nbsp;" . elgg_view("input/pulldown", array("internalname" => "members", "options_values" => $noyes_options, "value" => $group->getPrivateSetting($prefix . "members")));
		$form_body .= "<img src='" . $vars["url"] . "_graphics/icon_customise_info.gif' class='group_tools_explain' alt='" . elgg_echo("group_tools:explain") . "' title='" . elgg_echo("group_tools:cleanup:members:explain") . "' onmouseover='group_tools_cleanup_highlight(\"members\");' onmouseout='group_tools_cleanup_unhighlight(\"members\");' />";
		$form_body .= "</div>";
		
		// show featured groups
		$form_body .= "<div>";
		$form_body .= elgg_echo("group_tools:cleanup:featured");
		$form_body .= ":&nbsp;" . elgg_view("input/pulldown", array("internalname" => "featured", "options_values" => $featured_options, "value" => $group->getPrivateSetting($prefix . "featured")));
		$form_body .= "<img src='" . $vars["url"] . "_graphics/icon_customise_info.gif' class='group_tools_explain' alt='" . elgg_echo("group_tools:explain") . "' title='" . elgg_echo("group_tools:cleanup:featured:explain") . "' onmouseover='group_tools_cleanup_highlight(\"featured\");' onmouseout='group_tools_cleanup_unhighlight(\"featured\");' />";
		$form_body .= "</div>";
		
		// buttons
		$form_body .= "<div>";
		$form_body .= elgg_view("input/hidden", array("internalname" => "group_guid", "value" => $group->getGUID()));
		$form_body .= elgg_view("input/submit", array("value" => elgg_echo("save")));
		$form_body .= "</div>";
		
		// make body
		$body = "<h3 class='settings'>" . elgg_echo("group_tools:cleanup:title") . "</h3>";
		$body .= elgg_view("input/form", array("action" => $vars["url"] . "action/group_tools/cleanup",
												"body" => $form_body));
		
		// show body
		echo elgg_view("page_elements/contentwrapper", array("body" => $body));
		?>
		<script type="text/javascript">
			var group_tools_members_example_text = "<?php echo elgg_echo("groups:members"); ?>";
			var group_tools_featured_example_text = "<?php echo elgg_echo("groups:featured"); ?>";
		</script>
		<?php 
	}