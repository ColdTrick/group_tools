<?php
/**
 * Configure a welcome message to send to a new member when he/she joins the group
 */

$group = elgg_extract('entity', $vars);

if (!($group instanceof ElggGroup) || !$group->canEdit()) {
	return;
}

$form = elgg_view_form('group_tools/welcome_message', [], $vars);

echo elgg_view_module('info', elgg_echo('group_tools:welcome_message:title'), $form);
