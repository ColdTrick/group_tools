<?php
/**
 * Make sure everyone is a member of the autojoin group
 */

// this could take a while ;)
set_time_limit(0);

$group_guid = (int) get_input('group_guid');
if (empty($group_guid)) {
	register_error(elgg_echo('error:missing_data'));
	forward(REFERER);
}

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);

// set counters
$already = 0;
$new = 0;
$failure = 0;

$options = [
	'type' => 'user',
	'relationship' => 'member_of_site',
	'relationship_guid' => elgg_get_site_entity()->getGUID(),
	'inverse_relationship' => true,
	'limit' => false,
];

$users = new ElggBatch('elgg_get_entities_from_relationship', $options);
foreach ($users as $user) {
	if (!$group->isMember($user)) {
		if ($group->join($user)) {
			$new++;
		} else {
			$failure++;
		}
	} else {
		$already++;
	}
}

system_message(elgg_echo('group_tools:action:fix_auto_join:success', [$new, $already, $failure]));

forward(REFERER);
