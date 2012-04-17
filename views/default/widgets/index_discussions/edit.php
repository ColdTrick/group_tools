<?php

	$widget = elgg_extract("entity");
	
	$limit = (int) $widget->num_display;
	if($limit < 1){
		$limit = 5;
	}
	
	echo "<div>";
	echo elgg_echo("widget:numbertodisplay");
	echo "&nbsp;" . elgg_view("input/dropdown", array("name" => "params[num_display]", "value" => $limit, "options" => range(1, 25)));
	echo "</div>";