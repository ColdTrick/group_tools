<?php

$group = elgg_extract('entity', $vars);
if (!$group instanceof \ElggGroup) {
	return;
}

echo elgg_view('output/longtext', ['value' => elgg_echo('group_tools:welcome_message:description')]);

echo elgg_view_field([
	'#type' => 'longtext',
	'#help' => elgg_echo('group_tools:welcome_message:explain', [
		elgg_get_logged_in_user_entity()->name,
		$group->name,
		$group->getURL(),
	]),
	'name' => 'welcome_message',
	'value' => $group->getPrivateSetting('group_tools:welcome_message'),
]);
echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'group_guid',
	'value' => $group->guid,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);
