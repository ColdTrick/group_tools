<?php
/**
 * Allow a user to enter a group e-mail invitation code
 */

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:groups:invitation:code:description'),
]);

echo elgg_view_field([
	'#type' => 'text',
	'name' => 'invitecode',
	'value' => get_input('invitecode'),
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('submit'),
]);
elgg_set_form_footer($footer);
