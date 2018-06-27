<?php
/**
 * Revoke an email inviation for a group
 */

$annotation_id = (int) get_input('annotation_id');
$group_guid = (int) get_input('group_guid');

if (empty($group_guid) || empty($annotation_id)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);
$annotation = elgg_get_annotation_from_id($annotation_id);
if (empty($annotation) || ($annotation->name !== 'email_invitation')) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

if (!$annotation->delete()) {
	return elgg_error_response(elgg_echo('group_tools:action:revoke_email_invitation:error'));
}

return elgg_ok_response('', elgg_echo('group_tools:action:revoke_email_invitation:success'));
