<?php
/**
 * Group invite user HTML view for autocomplete items
 *
 * @uses $vars['entity'] the selected entity
 * @uses $vars['input_name'] name of the returned data array
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggUser) {
	return;
}

$input_name = elgg_extract('input_name', $vars);
if (empty($input_name)) {
	return;
}

$icon = elgg_view_entity_icon($entity, 'tiny', ['use_hover' => false]);
$delete_icon = elgg_view_icon('delete', ['class' => 'elgg-autocomplete-item-remove']);

$title = $entity->getDisplayName();

$group = elgg_extract('group', $vars);
if ($group instanceof ElggGroup && $group->isMember($entity)) {
	$title .= elgg_format_element('span', ['class' => ['mls', 'elgg-subtext']], elgg_echo('group_tools:groups:invite:member'));
}

$body = elgg_view_image_block($icon, $title, ['image_alt' => $delete_icon]);
$body .= elgg_view('input/hidden', [
	'name' => "{$input_name}[]",
	'value' => $entity->guid,
]);

echo elgg_format_element('li', [
	'class' => 'elgg-item',
	'data-guid' => $entity->guid,
], $body);
