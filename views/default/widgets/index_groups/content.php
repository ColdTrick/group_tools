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
		"pagination" => false,
		"metadata_name_value_pairs" => array()
	);
	
	// limit to featured groups?
	if($widget->featured == "yes"){
		$options["metadata_name_value_pairs"][] = array(
			"name" => "featured_group",
			"value" => "yes"
		);
	}
	
	// enable advanced filter
	if(($filter_name = $widget->filter_name) && ($filter_value = $widget->filter_value)){
		if($profile_fields = elgg_get_config("group")){
			$found = false;
			
			foreach($profile_fields as $name => $type){
				if(($name == $filter_name) && ($type == "tags")){
					$found = true;
					break;
				}
			}
			
			if($found){
				
				$filter_value = string_to_tag_array($filter_value);
				$options["metadata_name_value_pairs"][] = array(
					"name" => $filter_name,
					"value" => $filter_value
				);
			}
		}
	}
	
	// show group member count
	if($widget->show_members == "yes"){
		$show_members = true;
		elgg_push_context("widgets_groups_show_members");
	} else {
		$show_members = false;
	}
	
	// list groups
	if($groups = elgg_list_entities_from_metadata($options)){
		echo $groups;
	} else {
		echo elgg_echo("groups:none");
	}
	
	if($show_members){
		elgg_pop_context();
	}