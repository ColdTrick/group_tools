<?php

$tools = elgg_extract('tools', $vars);

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:group:edit:tools:default:description'),
]);

/** @var \Elgg\Groups\Tool $tool */
foreach ($tools as $tool) {
	echo elgg_view('groups/edit/tool', [
		'entity' => elgg_extract('entity', $vars),
		'tool' => $tool,
		'value' => elgg_extract($tool->mapMetadataName(), $vars),
	]);
}
