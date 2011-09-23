<?php 

	$plugin = $vars["entity"];
	
	$admin_transfer_options = array(
		"no" => elgg_echo("option:no"),
		"admin" => elgg_echo("group_tools:settings:admin_transfer:admin"),
		"owner" => elgg_echo("group_tools:settings:admin_transfer:owner")
	);

	$noyes_options = array(
		"no" => elgg_echo("option:no"),
		"yes" => elgg_echo("option:yes")
	);
	
	$listing_options = array(
		"active" => elgg_echo("groups:latestdiscussion"),
		"newest" => elgg_echo("groups:newest"),
		"pop" => elgg_echo("groups:popular"),
		"alfa" => elgg_echo("group_tools:groups:sorting:alfabetical"),
	);
	
?>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:admin_transfer");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[admin_transfer]", "options_values" => $admin_transfer_options, "value" => $plugin->admin_transfer));
	?>
</div>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:multiple_admin");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[multiple_admin]", "options_values" => $noyes_options, "value" => $plugin->multiple_admin));
	?>
</div>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:invite");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[invite]", "options_values" => $noyes_options, "value" => $plugin->invite));
	?>
</div>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:invite_email");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[invite_email]", "options_values" => $noyes_options, "value" => $plugin->invite_email));
	?>
</div>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:invite_csv");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[invite_csv]", "options_values" => $noyes_options, "value" => $plugin->invite_csv));
	?>
</div>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:mail");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[mail]", "options_values" => $noyes_options, "value" => $plugin->mail));
	?>
</div>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:listing");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[group_listing]", "options_values" => $listing_options, "value" => $plugin->group_listing));
	?>
</div>
<div>
	<?php 
		echo elgg_echo("group_tools:settings:search_index");
		echo "&nbsp;" . elgg_view("input/pulldown", array("internalname" => "params[search_index]", "options_values" => $noyes_options, "value" => $plugin->search_index));
	?>
</div>