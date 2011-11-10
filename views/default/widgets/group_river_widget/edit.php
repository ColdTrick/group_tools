<?php
	/**
	 * Edit the widget
	 * 
	 */

	$widget = $vars["entity"];
	
	// filter activity
	$filter_contents = array();
	$filter_contents['all'] = 'all';
	if (!empty($vars['config']->registered_entities)) {
		foreach ($vars['config']->registered_entities as $type => $ar) {
			if($type == "user"){
				continue;
			} else {
				if (count($vars['config']->registered_entities[$type])) {
					foreach ($vars['config']->registered_entities[$type] as $subtype) {
						$keyname = 'item:' . $type . ':' . $subtype;
						$filter_contents[$keyname] = "{$type},{$subtype}";
					}
				} else {
					$keyname = 'item:' . $type;
					$filter_contents[$keyname] = "{$type},";
				}
			}
		}
	}

	if($widget->context != "groups"){
	    //the user of the widget
		$owner = $widget->getOwnerEntity();
	      
		// get all groups
		$options = array(
			"type" => "group",
			"limit" => false
		);
		
		if($widget->context != "index"){
			$options["relationship"] = "member";
			$options["relationship_guid"] = $owner->getGUID();
		}
		
	    if($groups = elgg_get_entities_from_relationship($options)){
			// get groups
	    	$group_options_values = array();
			foreach($groups as $group){
				$group_options_values[$group->getGUID()] = $group->name;
			}
			
			natcasesort($group_options_values);

			// get selected value(s)
			if($group_guid = $widget->getMetadata("group_guid")){
				if(!is_array($group_guid)){
					$group_guid = array($group_guid);
				}
			} elseif($group_guid = $widget->group_guid) {
				unset($widget->group_guid);
				$group_guid = array($group_guid);
	    	} else {
				$group_guid = array();
			}
			
			$group_guid = array_map("sanitise_int", $group_guid);
			
			// make options
			echo "<div>";
			echo elgg_echo('widgets:group_river_widget:edit:num_display'); 
			echo " " . elgg_view("input/pulldown", array("options" => range(5, 25, 5), "value" => $widget->num_display, "internalname" => "params[num_display]"));
			echo "</div>";
			
			echo "<div>";
			echo elgg_echo("filter");
			echo "<select name='params[activity_filter]'>";
			foreach($filter_contents as $label => $content) {
				if (($widget->activity_filter == $content)) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				}
				echo "<option value='" . $content . "' " . $selected . ">" . elgg_echo($label) . "</option>";
			}
			echo "</select>";
			echo "</div>";
	
			echo "<div>";
			echo elgg_echo('widgets:group_river_widget:edit:group');
			echo "<div>";
			echo elgg_view("input/hidden", array("internalname" => "params[group_guid][]", "value" => ""));
			foreach($group_options_values as $guid => $name){
				$checked = "";
				if(in_array($guid, $group_guid)){
					$checked = "checked='checked'";
				}
				echo "<input type='checkbox' name='params[group_guid][]' value='" . $guid . "' " . $checked . " /> " . $name . "<br />";
			}
			echo "</div>";
			echo "</div>";
		} else {
			echo elgg_echo("widgets:group_river_widget:edit:no_groups");
	    }
	} else {
		echo "<div>";
		echo elgg_echo('widgets:group_river_widget:edit:num_display'); 
		echo " " . elgg_view("input/pulldown", array("options" => range(5, 25, 5), "value" => $widget->num_display, "internalname" => "params[num_display]"));
		echo "</div>";
		
		echo "<div>";
		echo elgg_echo("filter");
		echo "<select name='params[activity_filter]'>";
		foreach($filter_contents as $label => $content) {
			if (($widget->activity_filter == $content)) {
				$selected = 'selected="selected"';
			} else {
				$selected = '';
			}
			echo "<option value='" . $content . "' " . $selected . ">" . elgg_echo($label) . "</option>";
		}
		echo "</select>";
		echo "</div>";
	}