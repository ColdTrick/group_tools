<?php
/**
 * Set default notifications for new users who join a group
 *
 * @uses $vars['entity'] the group to configure
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup || !$entity->canEdit()) {
	return;
}

$methods = elgg_get_notification_methods();
if (empty($methods)) {
	echo elgg_view('output/longtext', [
		'value' => elgg_echo('group_tools:edit:group:notifications:no_methods'),
	]);
	return;
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $entity->guid,
]);

// notification settings
$members_count = $entity->getMembers([
	'count' => true,
]);

$notification_count = 0;
$notification_options = [
	'type' => 'user',
	'count' => true,
	'relationship_guid' => $entity->guid,
	'inverse_relationship' => true,
];

$options = [];
foreach ($methods as $method) {
	
	// make correct label
	$label = $method;
	if (elgg_language_key_exists("notification:method:{$method}")) {
		$label = elgg_echo("notification:method:{$method}");
	}
	
	// add counters
	$notification_options['relationship'] = "notify{$method}";
	$count = elgg_get_entities_from_relationship($notification_options);
	
	$notification_count += $count;
	
	// append counters to label
	$label .= ' ' . elgg_echo('group_tools:edit:group:notifications:counter', [$count, $members_count]);
	
	$options[$label] = $method;
}

$selected_methods = group_tools_get_default_group_notification_settings($entity);

echo elgg_view_field([
	'#type' => 'checkboxes',
	'#label' => elgg_echo('group_tools:edit:group:notifications:defaults'),
	'#help' => elgg_echo('group_tools:edit:group:notifications:defaults:help'),
	'name' => 'default_methods',
	'options' => $options,
	'value' => $selected_methods,
	'align' => 'horizontal',
]);

// footer
$footer = '';
if ($notification_count > 0) {
	$footer .= elgg_view('output/url', [
		'href' => elgg_http_add_url_query_elements('action/group_tools/admin/disable_notifications', [
			'guid' => $entity->guid,
		]),
		'text' => elgg_echo('group_tools:notifications:disable'),
		'title' => elgg_echo('group_tools:notifications:disclaimer'),
		'class' => [
			'elgg-button',
			'elgg-button-delete',
			'float-alt',
		],
		'confirm' => true,
	]);
}
$footer .= elgg_view_field([
	'#type' => 'fieldset',
	'fields' => [
		[
			'#type' => 'submit',
			'name' => 'save',
			'value' => elgg_echo('save'),
		],
		[
			'#type' => 'submit',
			'name' => 'save_apply',
			'value' => elgg_echo('group_tools:notifications:enable'),
			'title' => elgg_echo('group_tools:notifications:disclaimer'),
		],
	],
	'align' => 'horizontal',
]);

elgg_set_form_footer($footer);
