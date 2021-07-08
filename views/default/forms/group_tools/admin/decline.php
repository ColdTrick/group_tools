<?php
/**
 * Form to add a reason why a group was declined
 *
 * @use $vars['entity'] the group to decline
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup) {
	return;
}

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:group:admin_approve:decline:description', [$entity->getDisplayName()]),
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $entity->guid,
]);

echo elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('group_tools:group:admin_approve:decline:reason'),
	'name' => 'reason',
]);

// form footer
$footer = '';
$footer .= elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('decline'),
	'class' => 'elgg-button-delete',
	'data-confirm' => elgg_echo('group_tools:group:admin_approve:decline:confirm'),
]);

elgg_set_form_footer($footer);
