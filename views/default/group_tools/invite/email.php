<?php
/**
 * Invite email addresses to a group
 *
 * used in /forms/groups/invite
 */

elgg_import_esm('group_tools/invite/email');

$group = elgg_extract('entity', $vars);
if (!$group instanceof \ElggGroup) {
	return;
}

$contents = elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
		[
			'#type' => 'email',
			'#label' => elgg_echo('group_tools:group:invite:email:description'),
			'#class' => 'elgg-field-stretch',
			'name' => 'user_guid_email[]',
			'group_guid' => $group->guid,
		],
		[
			'#type' => 'button',
			'icon' => 'plus',
			'text' => elgg_echo('add'),
			'class' => [
				'elgg-button-action',
			],
		],
	],
]);

$contents .= elgg_format_element('ul', ['class' => ['elgg-list', 'elgg-list-email']]);

echo elgg_format_element('div', ['id' => 'group-tools-invite-email'], $contents);
