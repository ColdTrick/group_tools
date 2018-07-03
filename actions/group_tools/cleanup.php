<?php
/**
 * Cleanup the group sidebar
 */

$group_guid = (int) get_input('group_guid');

elgg_entity_gatekeeper($group_guid, 'group');
$group = get_entity($group_guid);

if (!$group->canEdit()) {
	return elgg_error_response(elgg_echo('actionunauthorized'));
}

// get input
$extras_menu = get_input('extras_menu');
$actions = get_input('actions');
$menu = get_input('menu');
$members = get_input('members');
$search = get_input('search');
$featured = get_input('featured');
$featured_sorting = get_input('featured_sorting');

// save settings
$prefix = \ColdTrick\GroupTools\Cleanup::SETTING_PREFIX;

$group->setPrivateSetting("{$prefix}extras_menu", $extras_menu);
$group->setPrivateSetting("{$prefix}actions", $actions);
$group->setPrivateSetting("{$prefix}menu", $menu);
$group->setPrivateSetting("{$prefix}members", $members);
$group->setPrivateSetting("{$prefix}search", $search);
$group->setPrivateSetting("{$prefix}featured", $featured);
$group->setPrivateSetting("{$prefix}featured_sorting", $featured_sorting);

return elgg_ok_response('', elgg_echo('group_tools:actions:cleanup:success'), $group->getURL());
