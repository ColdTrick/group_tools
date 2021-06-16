<?php
/**
 * Ajax (lightbox) view to show the reasons a site admin should approve the group
 */

use Elgg\Database\QueryBuilder;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup || !$entity->canEdit()) {
	return;
}

$content = elgg_list_annotations([
	'limit' => false,
	'guid' => $entity->guid,
	'wheres' => [
		function (QueryBuilder $qb, $main_alias) {
			return $qb->compare("{$main_alias}.name", 'like', 'approval_reason:%', ELGG_VALUE_STRING);
		}
	],
	'item_view' => 'annotation/approval_reason',
]);

echo elgg_view_module('info', elgg_echo('group_tools:group:admin_approve:reasons:details'), $content);
