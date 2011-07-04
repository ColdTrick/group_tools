<?php 

	$widget = $vars["entity"];

	$count = (int) $widget->num_display;
	if($count < 1){
		$count = 5;
	}

	echo elgg_echo("widgets:group_members:edit:num_display");
	echo "&nbsp;<input type='text' name='params[num_display]' value='" . $count . "' size='4' maxlength='3' />";
?>