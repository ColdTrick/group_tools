<?php 

	$group = $vars["entity"];

	$options = array(
		"type" => "user",
		"limit" => false,
		"relationship" => "group_admin",
		"relationship_guid" => $group->getGUID(),
		"inverse_relationship" => true
	);

	if($users = elgg_get_entities_from_relationship($options)){
		array_unshift($users, $group->getOwnerEntity());
		
		echo "<div id='group_tools_multiple_admin_group_admins'>";
		echo "<h2>" . elgg_echo("group_tools:multiple_admin:group_admins") . "</h2>";
		
		foreach($users as $user){
			echo "<div class='member_icon'>";
			echo "<a href='" . $user->getURL() . "' >" . elgg_view("profile/icon", array("entity" => $user, "size" => "tiny", "override" => true)) . "</a>";
			echo "</div>";
		}
		
		echo "<div class='clearfloat'></div>";
		echo "</div>";
	}
