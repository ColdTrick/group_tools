<?php
/**
 * show all group admins
 */

use Elgg\Database\QueryBuilder;

$group = elgg_extract('entity', $vars);
if (!$group instanceof ElggGroup) {
	return;
}

if (!group_tools_multiple_admin_enabled()) {
	return;
}

$options = [
	'relationship' => 'group_admin',
	'relationship_guid' => $group->guid,
	'inverse_relationship' => true,
	'type' => 'user',
	'limit' => false,
	'list_type' => 'gallery',
	'gallery_class' => 'elgg-gallery-users',
	'wheres' => [
		function (QueryBuilder $qb, $main_alias) use ($group) {
			return $qb->compare("{$main_alias}.guid", '!=', $group->owner_guid, ELGG_VALUE_GUID);
		},
	],
];

$users = elgg_get_entities($options);
if (empty($users)) {
	return;
}

// add owner to the beginning of the list
array_unshift($users, $group->getOwnerEntity());

$body = elgg_view_entity_list($users, $options);

echo elgg_view_module('aside', elgg_echo('group_tools:multiple_admin:group_admins'), $body);
