<?php
/**
 * Here we will show is a group has one or more of the following special states
 * - featured
 * - autojoin
 * - suggested
 *
 * Also these states can be toggled
 */

$user = elgg_get_logged_in_user_entity();
if (empty($user) || !$user->isAdmin()) {
	// only for site administrators
	return;
}

$group = elgg_extract('entity', $vars);
if (!($group instanceof ElggGroup)) {
	return;
}

elgg_require_js('group_tools/group_edit');

$noyes_options = [
	'no' => elgg_echo('option:no'),
	'yes' => elgg_echo('option:yes')
];

$auto_join_groups = [];
$auto_join_setting = elgg_get_plugin_setting('auto_join', 'group_tools');
if (!empty($auto_join_setting)) {
	$auto_join_groups = string_to_tag_array($auto_join_setting);
}

$auto_join_value = 'no';
if (in_array($group->getGUID(), $auto_join_groups)) {
	$auto_join_value = 'yes';
}

$suggested_groups = [];
$suggested_setting = elgg_get_plugin_setting('suggested_groups', 'group_tools');
if (!empty($suggested_setting)) {
	$suggested_groups = string_to_tag_array($suggested_setting);
}

$suggested_value = 'no';
if (in_array($group->getGUID(), $suggested_groups)) {
	$suggested_value = 'yes';
}

$title = elgg_echo('group_tools:special_states:title');

$content = elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:special_states:description'),
]);


// build content
$content .= '<table class="elgg-table mtm">';

// featured group
$content .= '<tr>';
$content .= elgg_format_element('td', [], elgg_echo('group_tools:special_states:featured'));
$content .= elgg_format_element('td', [], elgg_view('input/select', [
	'options_values' => $noyes_options,
	'value' => $group->featured_group,
	'onchange' => "elgg.group_tools.toggle_featured({$group->getGUID()}, this);",
]));
$content .= '</tr>';

// auto_join group
$content .= '<tr>';
$content .= elgg_format_element('td', [], elgg_echo('group_tools:special_states:auto_join'));
$content .= elgg_format_element('td', [], elgg_view('input/select', [
	'options_values' => $noyes_options,
	'value' => $auto_join_value,
	'onchange' => "elgg.group_tools.toggle_special_state('auto_join', {$group->getGUID()});",
]));
$content .= '</tr>';

// suggested group
$content .= '<tr>';
$content .= elgg_format_element('td', [], elgg_echo('group_tools:special_states:suggested'));
$content .= elgg_format_element('td', [], elgg_view('input/select', [
	'options_values' => $noyes_options,
	'value' => $suggested_value,
	'onchange' => "elgg.group_tools.toggle_special_state('suggested', {$group->getGUID()});",
]));
$content .= '</tr>';

$content .= '</table>';

// check if this is an auto_join group and everyone is a member of this group
if ($auto_join_value == 'yes') {
	$options = [
		'type' => 'user',
		'relationship' => 'member_of_site',
		'relationship_guid' => $group->site_guid,
		'inverse_relationship' => true,
		'count' => true,
	];
	
	$user_count = elgg_get_entities_from_relationship($options);
	$member_count = $group->getMembers(['count' => true]);
	
	if ($user_count != $member_count) {
		
		$link_start = '<a href="' . elgg_add_action_tokens_to_url("action/group_tools/fix_auto_join?group_guid={$group->getGUID()}") . '">';
		$link_end = '</a>';
		
		$content .= elgg_format_element('div', ['class' => 'mtm'], elgg_echo('group_tools:special_states:auto_join:fix', [$link_start, $link_end]));
	}
}

// draw content
echo elgg_view_module('info', $title, $content);
