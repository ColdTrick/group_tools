<?php
/**
 * show the group widgets/module on the profile for non members of closed groups
 */

$group = $vars['entity'];

if (!($group instanceof ElggGroup) || !$group->canEdit()) {
	return;
}

if ($group->isPublicMembership()) {
	return;
}

// closed membership, so extend options
$noyes_options = [
	'no' => elgg_echo('option:no'),
	'yes' => elgg_echo('option:yes'),
];

// build form
$title = elgg_echo('group_tools:profile_widgets:title');
$form_body = elgg_format_element('div', [], elgg_echo('group_tools:profile_widgets:description'));

$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:profile_widgets:option');
$form_body .= elgg_view('input/select', [
	'name' => 'profile_widgets',
	'options_values' => $noyes_options,
	'value' => $group->profile_widgets,
	'class' => 'mls',
]);
$form_body .= '</div>';

$form_body .= '<div>';
$form_body .= elgg_view('input/hidden', ['name' => 'group_guid', 'value' => $group->getGUID()]);
$form_body .= elgg_view('input/submit', ['value' => elgg_echo('submit')]);
$form_body .= '</div>';

// make form
$form = elgg_view('input/form', [
	'body' => $form_body,
	'action' => 'action/group_tools/profile_widgets',
]);

// draw content
echo elgg_view_module('info', $title, $form);
