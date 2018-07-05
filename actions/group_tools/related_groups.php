<?php
/**
 * Action to save a new related group
 */

$group_guid = (int) get_input('group_guid');
$guid = get_input('guid');
if (is_array($guid) && !empty($guid)) {
	$guid = $guid[0];
}

if (empty($group_guid) || empty($guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

elgg_entity_gatekeeper($group_guid, 'group');
elgg_entity_gatekeeper($guid, 'group');

$group = get_entity($group_guid);
$related = get_entity($guid);

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

if ($group->guid === $related->guid) {
	return elgg_error_response(elgg_echo('group_tools:action:related_groups:error:same'));
}

// not already related?
if (check_entity_relationship($group->guid, 'related_group', $related->guid)) {
	return elgg_error_response(elgg_echo('group_tools:action:related_groups:error:already'));
}

if (!add_entity_relationship($group->guid, 'related_group', $related->guid)) {
	return elgg_error_response(elgg_echo('group_tools:action:related_groups:error:add'));
}

// notify the other owner about this
if ($group->owner_guid != $related->owner_guid) {
	$subject = elgg_echo('group_tools:related_groups:notify:owner:subject');
	$message = elgg_echo('group_tools:related_groups:notify:owner:message', [
		$related->getOwnerEntity()->getDisplayName(),
		elgg_get_logged_in_user_entity()->getDisplayName(),
		$related->getDisplayName(),
		$group->getDisplayName(),
	]);
	
	notify_user($related->owner_guid, $group->owner_guid, $subject, $message);
}

return elgg_ok_response('', elgg_echo('group_tools:action:related_groups:success'));
