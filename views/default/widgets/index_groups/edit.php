<?php 

	$widget = $vars["entity"];
	
	$count = sanitise_int($widget->group_count, false);
	if(empty($count)){
		$count = 8;
	}
	
	$featured_options_values = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);
	
?>
<div>
	<?php echo elgg_echo("widget:numbertodisplay"); ?><br />
	<?php echo elgg_view("input/text", array("name" => "params[group_count]", "value" => $count, "size" => "4", "maxlength" => "4")); ?>
</div>

<div>
	<?php echo elgg_echo("widgets:index_groups:featured"); ?>
	<?php echo elgg_view("input/dropdown", array("name" => "params[featured]", "options_values" => $featured_options_values, "value" => $widget->featured)); ?>
</div>
