<?php
/**
 * Save the domains for domain based joining of a group
 */

$group_guid = (int) get_input('group_guid');
$domains = get_input('domains');

if (empty($group_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);
if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

if (!empty($domains)) {
	$domains = string_to_tag_array($domains);
	$domains = '|' . implode('|', $domains) . '|';
	
	$group->setPrivateSetting('domain_based', $domains);
} else {
	$group->removePrivateSetting('domain_based');
}

return elgg_ok_response('', elgg_echo('group_tools:action:domain_based:success'), $group->getURL());
