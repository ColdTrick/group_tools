<?php 

	$plugin = $vars["entity"];
	
	$admin_transfer_options = array(
		"no" => elgg_echo("option:no"),
		"admin" => elgg_echo("group_tools:settings:admin_transfer:admin"),
		"owner" => elgg_echo("group_tools:settings:admin_transfer:owner")
	);

	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);
	
	$listing_options = array(
		"discussion" => elgg_echo("groups:latestdiscussion"),
		"newest" => elgg_echo("groups:newest"),
		"popular" => elgg_echo("groups:popular"),
		"open" => elgg_echo("group_tools:groups:sorting:open"),
		"closed" => elgg_echo("group_tools:groups:sorting:closed"),
		"alpha" => elgg_echo("group_tools:groups:sorting:alphabetical"),
	);
	
	if($auto_joins = $plugin->auto_join){
		$auto_joins = string_to_tag_array($auto_joins);
	}
	
	
	// group management settings
	$title = elgg_echo("group_tools:settings:management:title");
	
	$body = "<div>";
	$body .= elgg_echo("group_tools:settings:admin_create");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[admin_create]", "options_values" => $noyes_options, "value" => $plugin->admin_create));
	$body .= "<div class='elgg-subtext'>" . elgg_echo("group_tools:settings:admin_create:description") . "</div>";
	$body .= "</div>";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:admin_transfer");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[admin_transfer]", "options_values" => $admin_transfer_options, "value" => $plugin->admin_transfer));
	$body .= "</div>";
	
	$body .= "<br />";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:search_index");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[search_index]", "options_values" => $noyes_options, "value" => $plugin->search_index));
	$body .= "</div>";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:auto_notification");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[auto_notification]", "options_values" => $noyes_options, "value" => $plugin->auto_notification));
	$body .= "</div>";
	
	$body .= "<br />";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:multiple_admin");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[multiple_admin]", "options_values" => $noyes_options, "value" => $plugin->multiple_admin));
	$body .= "</div>";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:mail");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[mail]", "options_values" => $noyes_options, "value" => $plugin->mail));
	$body .= "</div>";
	
	$body .= "<br />";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:listing");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[group_listing]", "options_values" => $listing_options, "value" => $plugin->group_listing));
	$body .= "</div>";
	
	echo elgg_view_module("inline", $title, $body);
	
	// group invite settings
	$title = elgg_echo("group_tools:settings:invite:title");
	
	$body = "<div>";
	$body .= elgg_echo("group_tools:settings:invite");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[invite]", "options_values" => $noyes_options, "value" => $plugin->invite));
	$body .= "</div>";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:invite_email");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[invite_email]", "options_values" => $noyes_options, "value" => $plugin->invite_email));
	$body .= "</div>";
	
	$body .= "<div>";
	$body .= elgg_echo("group_tools:settings:invite_csv");
	$body .= "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[invite_csv]", "options_values" => $noyes_options, "value" => $plugin->invite_csv));
	$body .= "</div>";
	
	echo elgg_view_module("inline", $title, $body);

	// check group auto join settings
	if(!empty($auto_joins)) { 
		$title = elgg_echo("group_tools:settings:auto_join");
		
		$content = "<div>" . elgg_echo("group_tools:settings:auto_join:description") . "</div>";
		
		foreach($auto_joins as $group_guid){
			if($group = get_entity($group_guid)){
				$content .= elgg_view("output/url", array("href" => $group->getURL(), "text" => $group->name));
				$content .= "&nbsp;(" . elgg_view("output/confirmlink", array("href" => $vars["url"] . "action/group_tools/toggle_auto_join?group_guid=" . $group->getGUID(), "text" => elgg_echo("group_tools:remove"))) . ")<br />";
			}
		}
		
		echo elgg_view_module("inline", $title, $content);
	}