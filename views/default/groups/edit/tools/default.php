<?php

$tools = elgg_extract('tools', $vars);

/* @var $tool \Elgg\Groups\Tool */
foreach ($tools as $tool) {
	echo elgg_view('groups/edit/tool', [
		'entity' => elgg_extract('entity', $vars),
		'tool' => $tool,
		'value' => elgg_extract($tool->mapMetadataName(), $vars),
	]);
}
