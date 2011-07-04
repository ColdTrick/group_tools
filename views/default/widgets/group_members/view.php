<?php 

	$widget = $vars["entity"];
	
	$count = (int) $widget->num_display;
	
	if($count < 1){
		$count = 5;
	}

	$options = array(
		"type" => "user",
		"limit" => $count,
		"relationship" => "member",
		"relationship_guid" => $widget->getOwner(),
		"inverse_relationship" => true
	);
	
	if($members = elgg_get_entities_from_relationship($options)){
		$list = "";
		
		foreach($members as $member){
			$list .= elgg_view("profile/icon", array("entity" => $member, "size" => "small"));
		}
		
		$list .= "<div class='clearfloat'></div>";
	} else {
		$list = elgg_echo("widgets:group_members:view:no_members");
	}

	echo "<div class='contentWrapper group_tools_widget_group_members'>";
	echo $list;
	echo "</div>";

?>