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

$group = get_entity($group_guid);
$related = get_entity($guid);

if (!$group instanceof \ElggGroup || !$related instanceof \ElggGroup) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

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

$logged_in_user = elgg_get_logged_in_user_entity();

// notify the other owner about this
if ($group->owner_guid !== $related->owner_guid && $related->owner_guid !== $logged_in_user->guid) {
	/* @var $related_owner \ElggUser */
	$related_owner = $related->getOwnerEntity();
	
	$subject = elgg_echo('group_tools:related_groups:notify:owner:subject', [], $related_owner->getLanguage());
	$message = elgg_echo('group_tools:related_groups:notify:owner:message', [
		$logged_in_user->getDisplayName(),
		$related->getDisplayName(),
		$group->getDisplayName(),
	], $related_owner->getLanguage());
	
	$params = [
		'action' => 'relate',
		'object' => $related,
	];
	
	notify_user($related_owner->guid, $logged_in_user->guid, $subject, $message, $params);
}

return elgg_ok_response('', elgg_echo('group_tools:action:related_groups:success'));
