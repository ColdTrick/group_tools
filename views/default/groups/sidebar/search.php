<?php
/**
 * Search for content in this group
 *
 * @uses vars['entity'] ElggGroup
 */

$group = elgg_extract('entity', $vars);

if (!($group instanceof ElggGroup)) {
	return;
}

if ($group->getPrivateSetting('group_tools:cleanup:search') === 'yes') {
	return;
}

$body = elgg_view_form('groups/search', [
	'action' => 'search',
	'method' => 'get',
	'disable_security' => true,
], $vars);

echo elgg_view_module('aside', elgg_echo('groups:search_in_group'), $body);
