<?php
/**
 * Specific group item view use to list a group which needs to be approved by a site admin
 *
 * @uses $vars['entity']  The group to approve
 */

use Elgg\Database\QueryBuilder;

$group = elgg_extract('entity', $vars);
if (!$group instanceof ElggGroup || !elgg_is_admin_logged_in()) {
	return;
}

$buttons = [];
$buttons[] = elgg_view('output/url', [
	'text' => elgg_echo('approve'),
	'href' => elgg_generate_action_url('group_tools/admin/approve', [
		'guid' => $group->guid,
	]),
	'confirm' => true,
	'class' => 'elgg-button elgg-button-submit',
]);
$buttons[] = elgg_view('output/url', [
	'text' => elgg_echo('decline'),
	'href' => elgg_generate_action_url('group_tools/admin/decline', [
		'guid' => $group->guid,
	]),
	'confirm' => elgg_echo('group_tools:group:admin_approve:decline:confirm'),
	'class' => 'elgg-button elgg-button-delete',
]);

$count = $group->getAnnotations([
	'count' => true,
	'wheres' => [
		function(QueryBuilder $qb, $main_alias) {
			return $qb->compare("{$main_alias}.name", 'like', 'approval_reason:%', ELGG_VALUE_STRING);
		},
	],
]);
if (!empty($count)) {
	$buttons[] = elgg_view('output/url', [
		'text' => elgg_echo('group_tools:group:admin_approve:reasons'),
		'href' => elgg_http_add_url_query_elements('ajax/view/group_tools/group/reasons', [
			'guid' => $group->guid,
		]),
		'class' => 'elgg-button elgg-button-action elgg-lightbox',
	]);
}

$params = [
	'entity' => $group,
	'icon' => true,
	'icon_entity' => $group,
	'access' => false,
	'metadata' => false,
	'image_block_vars' => [
		'image_alt' => implode('', $buttons),
	],
];

echo elgg_view('group/elements/summary', $params);
