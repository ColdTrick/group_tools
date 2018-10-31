<?php

$entity = elgg_extract('entity', $vars);
if (!($entity instanceof ElggGroup)) {
	return;
}

$stale_info = group_tools_get_stale_info($entity);
if (empty($stale_info)) {
	return;
}

if (!$stale_info->isStale()) {
	return;
}

$message = elgg_echo('group_tools:stale_info:description');

if ($entity->canEdit()) {
	$message .= elgg_view('output/url', [
		'text' => elgg_view_icon('exclamation-triangle') . elgg_echo('group_tools:stale_info:link'),
		'href' => "action/group_tools/mark_not_stale?guid={$entity->guid}",
		'confirm' => true,
		'class' => 'mls',
		'id' => 'group-tools-stale-touch-link',
	]);
	
	elgg_require_js('group_tools/stale_info');
}

echo elgg_view_message('warning', $message, [
	'id' => 'group-tools-stale-message',
	'title' => false,
]);
