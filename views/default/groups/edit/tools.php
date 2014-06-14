<?php

/**
 * Group edit form
 *
 * This view contains the group tool options provided by the different plugins
 *
 * @package ElggGroups
 */

$entity = elgg_extract("entity", $vars);
$tools = elgg_get_config("group_tool_options");
$presets = group_tools_get_tool_presets();

if ($tools) {
	// new group can choose from preset (if any)
	if (empty($entity) && !empty($presets)) {
		echo elgg_view("output/longtext", array("value" => elgg_echo("group_tools:create_group:tool_presets:description")));
		
		echo "<div id='group-tools-preset-selector'>";
		
		foreach ($presets as $values) {
			echo elgg_view("output/url", array(
				"text" => elgg_extract("title", $values),
				"href" => "#",
				"class" => "elgg-button elgg-button-action mrm"
			));
		}
		echo "</div>";
		
		echo "<div id='group-tools-preset-description'></div>";
		$more_link = elgg_view("output/url", array(
			"text" => elgg_echo("more"),
			"href" => "#group-tools-preset-more",
			"rel" => "toggle",
			"class" => "float-alt"
		));
		echo elgg_view_module("info", elgg_echo("group_tools:create_group:tool_presets:active_header"), $more_link, array("id" => "group-tools-preset-active"));
		
		$tools_content = "";
		foreach ($tools as $group_option) {
			$group_option_toggle_name = $group_option->name . "_enable";
			$value = "no";
			
			$tools_content .= elgg_view("group_tools/elements/group_tool", array(
				"group_tool" => $group_option, 
				"value" => $value,
				"class" => "mbm"
			));
			
		}
		
		echo elgg_view_module("info", elgg_echo("group_tools:create_group:tool_presets:more_header"), $tools_content, array("id" => "group-tools-preset-more"));
		
		?>
		<script type="text/javascript">
			var GROUP_TOOLS_PRESETS = <?php echo json_encode($presets); ?>;
			
			require(["group_tools/ToolsPreset"], function(ToolsPreset) {
				ToolsPreset.init("#group-tools-group-edit-tools");
			});
		</script>
		<?php 
	} else {
	
		usort($tools, create_function('$a, $b', 'return strcmp($a->label, $b->label);'));
		
		foreach ($tools as $group_option) {
			$group_option_toggle_name = $group_option->name . "_enable";
			$value = elgg_extract($group_option_toggle_name, $vars);
			
			echo elgg_view("group_tools/elements/group_tool", array("group_tool" => $group_option, "value" => $value));
			
		}
	}
	?>
	<script type="text/javascript">
		require(["group_tools/ToolsEdit"], function(ToolsEdit) {
			ToolsEdit.init("#group-tools-group-edit-tools");
		});
	</script>
	<?php 
}