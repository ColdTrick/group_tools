<?php

$batch = elgg_get_entities([
	'type' => 'group',
	'limit' => false,
	'batch' => true,
	'access_id' => ACCESS_PRIVATE,
]);

$lis = [];

/* @var $group \ElggGroup */
foreach ($batch as $group) {
	$buttons = [];
	$buttons[] = elgg_view('output/url', [
		'text' => elgg_echo('approve'),
		'href' => 'action/group_tools/admin/approve?guid=' . $group->guid,
		'confirm' => true,
		'class' => 'elgg-button elgg-button-submit',
	]);
	$buttons[] = elgg_view('output/url', [
		'text' => elgg_echo('decline'),
		'href' => 'action/group_tools/admin/decline?guid=' . $group->guid,
		'confirm' => elgg_echo('group_tools:group:admin_approve:decline:confirm'),
		'class' => 'elgg-button elgg-button-delete',
	]);
	
	$params = [
		'entity' => $group,
		'icon' => true,
		'icon_entity' => $group,
		'access' => false,
		'metadata' => false,
		'image_block_vars' => [
			'image_alt' => implode('', $buttons),
		]
	];
	$lis[] = elgg_format_element('li', [
		'class' => [
			'elgg-item',
			"elgg-item-{$group->getType()}",
			"elgg-item-{$group->getType()}-{$group->getSubtype()}",
		]
	], elgg_view('group/elements/summary', $params));
}

if (empty($lis)) {
	echo elgg_echo('groups:none');
}

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:group:admin_approve:admin:description'),
]);

echo elgg_format_element('ul', ['class' => ['elgg-list', 'elgg-list-entity']], implode(PHP_EOL, $lis));
