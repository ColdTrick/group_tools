<?php
	/**
	 * All groups listing page navigation
	 *
	 */
	
	$tabs = array(
		"newest" => array(
			"text" => elgg_echo("groups:newest"),
			"href" => "groups/all?filter=newest",
			"priority" => 200,
		),
		"popular" => array(
			"text" => elgg_echo("groups:popular"),
			"href" => "groups/all?filter=popular",
			"priority" => 300,
		),
		"discussion" => array(
			"text" => elgg_echo("groups:latestdiscussion"),
			"href" => "groups/all?filter=discussion",
			"priority" => 400,
		),
		"open" => array(
			"text" => elgg_echo("group_tools:groups:sorting:open"),
			"href" => "groups/all?filter=open",
			"priority" => 500,
		),
		"closed" => array(
			"text" => elgg_echo("group_tools:groups:sorting:closed"),
			"href" => "groups/all?filter=closed",
			"priority" => 600,
		),
		"alpha" => array(
			"text" => elgg_echo("group_tools:groups:sorting:alphabetical"),
			"href" => "groups/all?filter=alpha",
			"priority" => 700,
		),
	);
	
	foreach ($tabs as $name => $tab) {
		$tab["name"] = $name;
		
		if($vars["selected"] == $name){
			$tab["selected"] = true;
		}
	
		elgg_register_menu_item("filter", $tab);
	}
	
	echo elgg_view_menu("filter", array("sort_by" => "priority", "class" => "elgg-menu-hz"));
