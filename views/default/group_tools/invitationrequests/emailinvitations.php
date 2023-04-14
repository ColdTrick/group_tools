<?php
/**
 * Show all group invitations based on the user's e-mail address
 */

$user = elgg_extract('entity', $vars);
if (!$user instanceof \ElggUser || !$user->canEdit()) {
	return;
}

if (elgg_get_plugin_setting('invite_email', 'group_tools') !== 'yes') {
	return;
}

echo elgg_list_annotations([
	'type' => 'group',
	'annotation_name_value_pairs' => [
		[
			'name' => 'email_invitation',
			'value' => "%|{$user->email}",
			'operand' => 'LIKE',
			'type' => ELGG_VALUE_STRING,
		],
	],
	'no_results' => elgg_echo('groups:invitations:none'),
]);
