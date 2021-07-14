<?php
/**
 * Set notification settings of group members
 */

if (!elgg_is_admin_logged_in()) {
	return;
}

$methods = elgg_get_notification_methods();
if (empty($methods)) {
	return;
}

$entity = elgg_extract('entity', $vars);
$content = '';

// notification settings
$members_count = 0;
if ($entity instanceof ElggGroup) {
	$members_count = $entity->getMembers([
		'count' => true,
	]);
}

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

$selected_methods = group_tools_get_default_group_notification_settings($entity);

$content .= elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('group_tools:edit:group:notifications:defaults'),
	'#help' => elgg_echo('group_tools:edit:group:notifications:defaults:help'),
	'name' => 'settings[group_tools][default_methods]',
	'options' => $options,
	'value' => $selected_methods,
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
			],
			'confirm' => true,
		]),
	];
}
if ($entity instanceof ElggGroup) {
	$buttons[] = [
		'#html' => elgg_view('output/url', [
			'text' => elgg_echo('group_tools:notifications:enable'),
			'title' => elgg_echo('group_tools:notifications:disclaimer'),
			'href' => elgg_generate_action_url('group_tools/admin/notifications', [
				'guid' => $entity->guid,
			]),
			'class' => [
				'elgg-button',
				'elgg-button-submit',
			],
			'confirm' => true,
		]),
	];
}

if (!empty($buttons)) {
	$content .= elgg_view_field([
		'#type' => 'fieldset',
		'fields' => $buttons,
		'align' => 'horizontal',
	]);
}

echo elgg_view_module('info', elgg_echo('group_tools:notifications:title'), $content);
