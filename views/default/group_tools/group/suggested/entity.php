<?php
/**
 * Item view for suggested groups during group creation
 *
 * @user $vars['entity'] the group to show
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup) {
	return;
}

$params = [
	'entity' => $entity,
	'title' => elgg_view('output/url', [
		'text' => $entity->getDisplayName(),
		'href' => $entity->getURL(),
	]),
	'content' => elgg_get_excerpt($entity->description),
	'byline' => false,
	'time' => false,
	'access' => false,
	'metadata' => false,
	'icon' => elgg_view_entity_icon($entity, 'small'),
];
$params = $params + $vars;

echo elgg_view('group/elements/summary', $params);
