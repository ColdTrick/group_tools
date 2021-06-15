<?php
/**
 * Ajax view to show suggested groups when creating a group based on the name of the new group
 * This is to help prevent the creation of duplicate groups
 */

$q = elgg_extract('q', $vars);
if (empty($q)) {
	return;
}

$result = elgg_list_entities([
	'type' => 'group',
	'query' => $q,
	'limit' => 3,
	'pagination' => false,
	'item_view' => 'group_tools/group/suggested/entity',
], 'elgg_search');

if (empty($result)) {
	return;
}

$module = 'info';
if (elgg_get_plugin_setting('simple_create_form', 'group_tools') === 'yes') {
	$module = 'simple';
}
echo elgg_view_module($module, elgg_echo('group_tools:group:edit:suggested'), $result, [
	'id' => 'group-tools-edit-group-suggestions'
]);
