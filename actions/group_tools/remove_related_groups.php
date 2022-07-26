<?php
/**
 * Action to save a new related group
 */

$group_guid = (int) get_input('group_guid');
$guid = (int) get_input('guid');

if (empty($group_guid) || empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$group = get_entity($group_guid);
$related = get_entity($guid);

if (!$group instanceof \ElggGroup || !$related instanceof \ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// related?
if (!$group->hasRelationship($related->guid, 'related_group')) {
	return elgg_error_response(elgg_echo('group_tools:action:remove_related_groups:error:not_related'));
}

if (!$group->removeRelationship($related->guid, 'related_group')) {
	return elgg_error_response(elgg_echo('group_tools:action:remove_related_groups:error:remove'));
}

return elgg_ok_response('', elgg_echo('group_tools:action:remove_related_groups:success'));
