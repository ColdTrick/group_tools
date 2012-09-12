<?php 

	$widget = $vars["entity"];
	
	$count = sanitise_int($widget->group_count, false);
	if(empty($count)){
		$count = 8;
	}
	
	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);
	
	// filter based on tag fields
	$tag_fields = array();
	if($profile_fields = elgg_get_config("group")){
		foreach($profile_fields as $name => $type){
			if($type == "tags"){
				$lan_key = "groups:" . $name;
				$label = $name;
				if(elgg_echo($lan_key) != $lan_key){
					$label = elgg_echo($lan_key);
				}
				
				$tag_fields[$name] = $label;
			}
		}
	}
	
?>
<div>
	<?php echo elgg_echo("widget:numbertodisplay"); ?><br />
	<?php echo elgg_view("input/text", array("name" => "params[group_count]", "value" => $count, "size" => "4", "maxlength" => "4")); ?>
</div>

<div>
	<?php 
		echo elgg_echo("widgets:index_groups:show_members");
		echo "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[show_members]", "options_values" => $noyes_options, "value" => $widget->show_members));
	?>
</div>

<div>
	<?php 
		echo elgg_echo("widgets:index_groups:featured");
		echo "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[featured]", "options_values" => $noyes_options, "value" => $widget->featured));
	?>
</div>
<?php if(!empty($tag_fields)) { ?>
<div>
	<?php 
		$tag_fields = array_reverse($tag_fields);
		$tag_fields[""] = elgg_echo("widgets:index_group:filter:no_filter");
		$tag_fields = array_reverse($tag_fields);
	
		echo elgg_echo("widgets:index_group:filter:field");
		echo "&nbsp" . elgg_view("input/dropdown", array("name" => "params[filter_name]", "value" => $widget->filter_name, "options_values" => $tag_fields));
		echo "<br />";
		
		echo elgg_echo("widgets:index_group:filter:value");
		echo elgg_view("input/tags", array("name" => "params[filter_value]", "value" => $widget->filter_value));
		
	?>
</div>
<?php }