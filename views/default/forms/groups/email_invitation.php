<?php
/**
 * Allow a user to enter a group e-mail invitation code
 */

// what does this form do
echo elgg_format_element('div', [], elgg_echo('group_tools:groups:invitation:code:description'));

// invitecode
echo elgg_view_field([
	'#type' => 'text',
	'name' => 'invitecode',
	'value' => get_input('invitecode'),
]);

// footer / buttons
$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('submit'),
]);
elgg_set_form_footer($footer);
