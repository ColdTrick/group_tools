<?php
	/**
	 * Elgg groups plugin
	 * 
	 * @package ElggGroups
	 */

	global $CONFIG;
	
	$limit = (int) get_input("limit", 10);
	$offset = (int) get_input("offset", 0);
	$tag = get_input("tag");
	$filter = get_input("filter");
	if (empty($filter)) {
		if(!($filter = get_plugin_setting("group_listing", "group_tools"))){
			// active discussions is the default
			$filter = "active";
		}
	}
	
	// Get objects
	$context = get_context();
	set_context('search');
	
	if (!empty($tag)) {
		$filter = 'search';
		
		// groups plugin saves tags as "interests" - see groups_fields_setup() in start.php
		$params = array(
			'types' => 'group',
			'limit' => $limit,
			'metadata_name' => 'interests',
			'metadata_value' => $tag,
			'full_view' => FALSE,
		);
		$objects = elgg_list_entities_from_metadata($params);
	} else {
		
		$group_options = array(
			"types" => "group", 
			"owner_guid" => 0, 
			"limit" => max(0, $limit), 
			"offset" => max(0, $offset), 
			"full_view" => false,
		);
		
		switch($filter){
			case "alfa":
				$group_options["joins"]	= array("JOIN {$CONFIG->dbprefix}groups_entity ge ON e.guid = ge.guid");
				$group_options["order_by"] = "ge.name ASC";
				
				$objects = elgg_list_entities($group_options);
				break;
			case "newest":
				$objects = elgg_list_entities($group_options);
				break;
			case "pop":
				$objects = list_entities_by_relationship_count("member", true, "group", "", 0, $limit, false);
				break;
			case "open":
				$group_options["metadata_name_value_pairs"] = array(
					"name" => "membership",
					"value" => ACCESS_PUBLIC
				);
				
				$objects = elgg_list_entities_from_metadata($group_options);
				break;
			case "closed":
				$group_options["metadata_name_value_pairs"] = array(
					"name" => "membership",
					"value" => ACCESS_PUBLIC,
					"operand" => "<>"
				);
				
				$objects = elgg_list_entities_from_metadata($group_options);
				break;
			case "active":
			default:
				$objects = list_entities_from_annotations("object", "groupforumtopic", "group_topic_post", "", 40, 0, 0, false, true);
				break;
		}
	}
	
	//get a group count
	$group_count = elgg_get_entities(array('types' => 'group', 'count' => TRUE));
	
	//find groups
	$area1 = elgg_view("groups/find");
	
	//menu options
	$area1 .= elgg_view("groups/side_menu");
	
	//featured groups
	$featured_groups = elgg_get_entities_from_metadata(array('metadata_name' => 'featured_group', 'metadata_value' => 'yes', 'types' => 'group', 'limit' => 10));
	$area1 .= elgg_view("groups/featured", array("featured" => $featured_groups));
		
	set_context($context);
	
	$title = sprintf(elgg_echo("groups:all"),page_owner_entity()->name);
	
	$area2 = elgg_view_title($title);
	$area2 .= elgg_view('groups/contentwrapper', array('body' => elgg_view("groups/group_sort_menu", array("count" => $group_count, "filter" => $filter)) . $objects));
	
	$body = elgg_view_layout('sidebar_boxes',$area1, $area2);
	
	// Finally draw the page
	page_draw($title, $body);
