<?php

	if(($page_owner = page_owner_entity()) && ($page_owner instanceof ElggGroup)){
		$featured = $page_owner->getPrivateSetting("group_tools:cleanup:featured");
		$featured_sorting = $page_owner->getPrivateSetting("group_tools:cleanup:featured_sorting");
		
		if(($featured != "no") && ($featured > 0)){
			$options = array(
				"type" => "group",
				"limit" => $featured,
				"metadata_name_value_pairs" => array("featured_group" => "yes"),
				"wheres" => array("(e.guid <> " . $page_owner->getGUID() . ")")
			);
			
			switch($featured_sorting){
				case "alphabetical":
					$options["joins"] = array("JOIN " . get_config("dbprefix") . "groups_entity ge ON e.guid = ge.guid");
					$options["order_by"] = "ge.name ASC";
					break;
				default:
					$options["order_by"] = "e.time_created DESC";
					break;
			}
			
			if($groups = elgg_get_entities_from_metadata($options)){
				echo "<div id='group_tools_sidebar_featured'>";
				echo "<h2>" . elgg_echo("groups:featured") . "</h2>";
				
				foreach($groups as $group){
					$icon = elgg_view("groups/icon", array("entity" => $group, "size" => "small"));
					
					$info = "<b>" . elgg_view("output/url", array("text" => $group->name, "href" => $group->getURL())) . "</b>";
					$info .= "<br />";
					$info .= $group->briefdescription;
					
					echo elgg_view_listing($icon, $info);
				}
				
				echo "</div>";
			}
		}
	}