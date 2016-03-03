<?php
/**
 * Allow a user to enter a group e-mail invitation code
 */

// what does this form do
echo elgg_format_element('div', [], elgg_echo('group_tools:groups:invitation:code:description'));

// invitecode
echo elgg_view('input/text', [
	'name' => 'invitecode',
	'value' => get_input('invitecode'),
	'class' => 'mbm',
]);

// footer / buttons
echo elgg_format_element('div', ['class' => 'elgg-foot'], elgg_view('input/submit', ['value' => elgg_echo('submit')]));
