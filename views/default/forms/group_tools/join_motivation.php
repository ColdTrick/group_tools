<?php
/**
 * Require the user to provide a motivation for joining this group
 *
 */

$group = elgg_extract('entity', $vars);

// motivation
$motivation = elgg_format_element('label', [
	'for' => 'group-tools-join-motivation',
], elgg_echo('group_tools:join_motivation:label'));

$motivation .= elgg_view('input/longtext', [
	'name' => 'motivation',
	'id' => 'group-tools-join-motivation',
	'required' => true,
]);

echo elgg_format_element('div', [], $motivation);

// footer
$footer = elgg_view('input/submit', [
	'value' => elgg_echo('groups:joinrequest'),
]);
$footer .= elgg_view('input/hidden', [
	'name' => 'group_guid',
	'value' => $group->getGUID(),
]);

echo elgg_format_element('div', ['class' => 'elgg-foot'], $footer);
