<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggGroup) {
	return;
}

$stale_info = group_tools_get_stale_info($entity);
if (empty($stale_info)) {
	return;
}

if (!$stale_info->isStale()) {
	return;
}

$link = '';

if ($entity->canEdit()) {
	$link = elgg_view('output/url', [
		'icon' => 'exclamation-triangle',
		'text' => elgg_echo('group_tools:stale_info:link'),
		'href' => elgg_generate_action_url('group_tools/mark_not_stale', [
			'guid' => $entity->guid,
		]),
		'confirm' => true,
		'id' => 'group-tools-stale-touch-link',
	]);
	
	elgg_require_js('group_tools/extends/groups/profile/stale_message');
}

echo elgg_view_message('warning', elgg_echo('group_tools:stale_info:description'), [
	'id' => 'group-tools-stale-message',
	'title' => false,
	'link' => $link,
]);
