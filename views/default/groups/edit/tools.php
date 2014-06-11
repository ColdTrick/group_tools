<?php

/**
 * Group edit form
 *
 * This view contains the group tool options provided by the different plugins
 *
 * @package ElggGroups
 */

$tools = elgg_get_config("group_tool_options");
if ($tools) {
	usort($tools, create_function('$a, $b', 'return strcmp($a->label, $b->label);'));
	
	foreach ($tools as $group_option) {
		$group_option_toggle_name = $group_option->name . "_enable";
		$value = elgg_extract($group_option_toggle_name, $vars);
		
		echo "<div>";
		
		echo elgg_view("input/radio", array(
			"name" => $group_option_toggle_name,
			"value" => $value,
			"options" => array(
				elgg_echo("option:yes") => "yes",
				elgg_echo("option:no") => "no",
			),
			"align" => "horizontal",
			"class" => "float-alt"
		));
		
		echo "<label>" . $group_option->label . "</label><br />";
		
		// optional description
		$lan_key = $group_option->name . ":group_tool_option:description";
		if (elgg_echo($lan_key) != $lan_key) {
			echo elgg_view("output/longtext", array("value" => elgg_echo($lan_key), "class" => "elgg-quiet mtn"));
		}
		
		echo "</div>";
	}
	?>
	<script type="text/javascript">
		require(["group_tools/ToolsEdit"], function(ToolsEdit) {
			ToolsEdit.init("#group-tools-group-edit-tools");
		});
	</script>
	<?php 
}