<?php
/**
 * Show a form where the user can enter an invitation code from their e-mail
 * (auto-fill if provided by url param)
 */

$user = elgg_extract('entity', $vars);
if (!$user instanceof \ElggUser || $user->guid !== elgg_get_logged_in_user_guid()) {
	// only show this form on the current user's page (only applies to admins)
	return;
}

if (elgg_get_plugin_setting('invite_email', 'group_tools') !== 'yes') {
	return;
}

echo elgg_view_module('info', elgg_echo('group_tools:groups:invitation:code:title'), elgg_view_form('groups/email_invitation'));
