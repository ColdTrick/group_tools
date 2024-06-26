<?php
/**
 * Require the user to provide a motivation for joining this group
 */

$group = elgg_extract('entity', $vars);
if (!$group instanceof \ElggGroup) {
	return;
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'group_guid',
	'value' => $group->guid,
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('group_tools:join_motivation:label'),
	'name' => 'motivation',
	'id' => 'group-tools-join-motivation',
	'required' => true,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('groups:joinrequest'),
]);
elgg_set_form_footer($footer);
