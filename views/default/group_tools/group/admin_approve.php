<?php
/**
 * Specific group item view use to list a group which needs to be approved by a site admin
 *
 * @uses $vars['entity']  The group to approve
 */

use Elgg\Database\QueryBuilder;
use Elgg\Values;

$group = elgg_extract('entity', $vars);
if (!$group instanceof ElggGroup || !elgg_is_admin_logged_in()) {
	return;
}

$buttons = [];
$content = '';

if (!(bool) $group->is_concept) {
	// awaiting approval
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
		'href' => "#group-tools-admin-approve-decline-{$group->guid}",
		'class' => [
			'elgg-button',
			'elgg-button-delete',
			'elgg-lightbox-inline',
		],
	]);
	
	$form = elgg_view_form('group_tools/admin/decline', [], [
		'entity' => $group,
	]);
	$module = elgg_view_module('info', elgg_echo('group_tools:group:admin_approve:decline:title'), $form, [
		'id' => "group-tools-admin-approve-decline-{$group->guid}",
	]);
	$content .= elgg_format_element('div', ['class' => 'hidden'], $module);
	
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
} else {
	// concept group
	$buttons[] = elgg_format_element('span', ['class' => 'mls'], elgg_echo('status:draft'));
	
	$retention = (int) elgg_get_plugin_setting('concept_groups_retention', 'group_tools');
	if ($retention > 0) {
		$remove_ts = Values::normalizeTime($group->time_created);
		$remove_ts->modify("+{$retention} days");
		
		$friendly_time = elgg_get_friendly_time($remove_ts->getTimestamp());
		$buttons[] = elgg_format_element('span', ['class' => 'mls'], elgg_echo('group_tools:group:concept:remaining', [$friendly_time]));
	}
}

$params = [
	'entity' => $group,
	'icon' => true,
	'icon_entity' => $group,
	'access' => false,
	'metadata' => false,
	'image_block_vars' => [
		'image_alt' => implode('', $buttons) . $content,
	],
];

echo elgg_view('group/elements/summary', $params);
