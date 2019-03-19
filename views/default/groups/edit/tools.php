<?php

/**
 * Group edit form
 *
 * This view contains the group tool options provided by the different plugins
 *
 * @package ElggGroups
 */

$entity = elgg_extract('entity', $vars);
if ($entity instanceof \ElggGroup) {
	$tools = elgg()->group_tools->group($entity);
} else {
	$tools = elgg()->group_tools->all();
}
if (empty($tools)) {
	return;
}

$tools = $tools->sort(function (\Elgg\Groups\Tool $a, \Elgg\Groups\Tool$b) {
	return strcmp($a->getLabel(), $b->getLabel());
})->all();

$vars['tools'] = $tools;

// if the form comes back from a failed save we can't use the presets
$sticky_form = (bool) elgg_extract('group_tools_presets', $vars, false);
$presets = false;
if (!$sticky_form) {
	$presets = group_tools_get_tool_presets();
}

$vars['presets'] = $presets;

// we need to be able to tell if we came from a sticky form
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'group_tools_presets',
	'value' => '1',
]);

if (!$entity instanceof ElggGroup) {
	echo elgg_view_field([
		'#type' => 'hidden',
		'id' => 'group-tools-preset',
		'name' => 'group_tools_preset',
		'value' => elgg_extract('group_tool_preset', $vars),
	]);
}

// new group can choose from preset (if any)
if (empty($entity) && !empty($presets)) {
	if (elgg_get_plugin_setting('simple_tool_presets', 'group_tools') === 'yes') {
		echo elgg_view('groups/edit/tools/simple', $vars);
	} else {
		echo elgg_view('groups/edit/tools/normal', $vars);
		?>
		<script type='text/javascript'>
			require(['group_tools/ToolsPreset'], function(ToolsPreset) {
				ToolsPreset.init('#group-tools-group-edit-tools');
			});
		</script>
		<?php
	}
} else {
	echo elgg_view('groups/edit/tools/default', $vars);
}
?>
<script type='text/javascript'>
	require(['group_tools/ToolsEdit'], function(ToolsEdit) {
		ToolsEdit.init('#group-tools-group-edit-tools');
	});
</script>
