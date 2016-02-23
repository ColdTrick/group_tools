<?php
/**
 * Cleanup the group sidebar
 */

$group_guid = (int) get_input('group_guid');

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);

if (!$group->canEdit()) {
	register_error(elgg_echo('actionunauthorized'));
	forward(REFERER);
}

// get input
$owner_block = get_input('owner_block');
$actions = get_input('actions');
$menu = get_input('menu');
$members = get_input('members');
$search = get_input('search');
$featured = get_input('featured');
$featured_sorting = get_input('featured_sorting');
$my_status = get_input('my_status');

// save settings
$prefix = 'group_tools:cleanup:';

$group->setPrivateSetting("{$prefix}owner_block", $owner_block);
$group->setPrivateSetting("{$prefix}actions", $actions);
$group->setPrivateSetting("{$prefix}menu", $menu);
$group->setPrivateSetting("{$prefix}members", $members);
$group->setPrivateSetting("{$prefix}search", $search);
$group->setPrivateSetting("{$prefix}featured", $featured);
$group->setPrivateSetting("{$prefix}featured_sorting", $featured_sorting);
$group->setPrivateSetting("{$prefix}my_status", $my_status);

system_message(elgg_echo('group_tools:actions:cleanup:success'));
forward($group->getURL());
