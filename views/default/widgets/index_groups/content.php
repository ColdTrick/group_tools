<?php 

	$widget = $vars["entity"];
	
	// get widget settings
	$count = sanitise_int($widget->group_count, false);
	if(empty($count)){
		$count = 8;
	}

	$options = array(
		"type" => "group",
		"limit" => $count,
		"full_view" => false,
		"pagination" => false
	);
	
	if($widget->featured == "yes"){
		$options["metadata_name"] = "featured_group";
		$options["metadata_value"] = "yes";
	}
	
	if($widget->show_members == "yes"){
		$show_members = true;
	} else {
		$show_members = false;
	}
	
	if($show_members){
		elgg_push_context("widgets_groups_show_members");
	}
	
	if($groups = elgg_list_entities_from_metadata($options)){
		echo $groups;
	} else {
		echo elgg_echo("groups:none");
	}
	
	if($show_members){
		elgg_pop_context();
	}