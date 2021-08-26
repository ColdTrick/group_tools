<?php
/**
 * Set notification settings of group members, only admins can change this for all the current members
 */

if (!elgg_is_admin_logged_in()) {
	return;
}

$methods = elgg_get_notification_methods();
if (empty($methods)) {
	return;
}

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup) {
	return;
}

$content = '';

// notification settings
$members_count = $entity->getMembers([
	'count' => true,
]);

$notification_count = 0;

$options = [];
foreach ($methods as $method) {
	
	// make correct label
	$label = $method;
	if (elgg_language_key_exists("notification:method:{$method}")) {
		$label = elgg_echo("notification:method:{$method}");
	}
	
	if ($entity instanceof ElggGroup) {
		// add counters
		$count = count($entity->getSubscribers($method));
		$notification_count += $count;
	
		// append counters to label
		$label .= ' ' . elgg_echo('group_tools:edit:group:notifications:counter', [$count, $members_count]);
	}
	
	$options[$label] = $method;
}

$content .= elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('group_tools:edit:group:notifications:change_settings'),
	'#help' => elgg_echo('group_tools:edit:group:notifications:change_settings:help'),
	'name' => 'group_tools_change_notification_settings',
	'options' => $options,
	'align' => 'horizontal',
]);

// buttons
$buttons = [];
if ($notification_count > 0) {
	$buttons[] = [
		'#html' => elgg_view('output/url', [
			'text' => elgg_echo('group_tools:notifications:disable'),
			'title' => elgg_echo('group_tools:notifications:disclaimer'),
			'href' => elgg_generate_action_url('group_tools/admin/disable_notifications', [
				'guid' => $entity->guid,
			]),
			'class' => [
				'elgg-button',
				'elgg-button-delete',
				'mrm',
			],
			'confirm' => true,
		]),
	];
}

$buttons[] = [
	'#type' => 'submit',
	'value' => elgg_echo('group_tools:notifications:enable'),
	'title' => elgg_echo('group_tools:notifications:disclaimer'),
	'confirm' => true,
	'formaction' => elgg_generate_action_url('group_tools/admin/notifications', [], false),
];

if (!empty($buttons)) {
	$content .= elgg_view_field([
		'#type' => 'fieldset',
		'fields' => $buttons,
		'align' => 'horizontal',
	]);
}

echo elgg_view_module('info', elgg_echo('group_tools:notifications:title'), $content);
