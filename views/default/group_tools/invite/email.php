<?php
/**
 * Invite email addresses to a group
 *
 * used in /forms/groups/invite
 */

elgg_require_js('group_tools/invite/email');

$group = elgg_extract('entity', $vars);

$contents = elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
		[
			'#type' => 'email',
			'#label' => elgg_echo('group_tools:group:invite:email:description'),
			'name' => 'user_guid_email[]',
			'group_guid' => $group->guid,
		],
		[
			'#type' => 'button',
			'value' => elgg_echo('add'),
			'class' => [
				'elgg-button-action',
			],
		],
	],
]);

$contents .= elgg_format_element('ul', ['class' => ['elgg-list', 'elgg-list-email']]);

echo elgg_format_element('div', ['id' => 'group-tools-invite-email'], $contents);
