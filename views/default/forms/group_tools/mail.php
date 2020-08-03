<?php
/**
 * Mail group members
 */

$group = elgg_extract('entity', $vars);
$members = elgg_extract('members', $vars);

$user_guids = [];
if (!empty($members)) {
	foreach ($members as $member) {
		$user_guids[] = $member->guid;
	}
}

$form_data = elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:all_members', [$group->getMembers(['count' => true])]),
	'name' => 'all_members',
	'value' => 1,
	'checked' => true,
	'switch' => true,
]);

$form_data .= elgg_view_field([
	'#type' => 'fieldset',
	'#class' => 'hidden',
	'id' => 'group-tools-mail-individual',
	'fields' => [
		[
			'#type' => 'userpicker',
			'#label' => elgg_echo('group_tools:mail:form:recipients'),
			'name' => 'user_guids',
			'value' => $user_guids,
			'show_friends' => false,
			'handler' => 'livesearch/group_members',
			'options' => [
				'match_target' => $group->guid,
			],
		],
		[
			'#type' => 'button',
			'id' => 'group-tools-mail-clear',
			'value' => elgg_echo('group_tools:clear_selection'),
			'class' => 'elgg-button-action',
		],
	]
]);

$form_data .= elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('group_tools:mail:form:title'),
	'name' => 'title',
]);

$form_data .= elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('group_tools:mail:form:description'),
	'name' => 'description',
	'required' => true,
]);

$form_data .= elgg_view('input/hidden', [
	'name' => 'group_guid',
	'value' => $group->guid,
]);

echo $form_data;

// footer
$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('send'),
]);
elgg_set_form_footer($footer);
