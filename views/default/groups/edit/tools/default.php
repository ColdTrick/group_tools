<?php

$tools = elgg_extract('tools', $vars);

/* @var $group_option \Elgg\Groups\Tool */
foreach ($tools as $group_option) {
	echo elgg_view('group_tools/elements/group_tool', [
		'group_tool' => $group_option,
		'value' => elgg_extract($group_option->mapMetadataName(), $vars),
	]);
}
