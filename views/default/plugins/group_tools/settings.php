<?php
/**
 * Plugin settings for group tools
 */

/** @var \ElggPlugin $plugin */
$plugin = elgg_extract('entity', $vars);

$listing_options = [
	'all' => elgg_echo('groups:all'),
	'yours' => elgg_echo('groups:yours'),
	'open' => elgg_echo('group_tools:groups:sorting:open'),
	'closed' => elgg_echo('group_tools:groups:sorting:closed'),
	'featured' => elgg_echo('status:featured'),
	'suggested' => elgg_echo('group_tools:groups:sorting:suggested'),
	'member' => elgg_echo('group_tools:groups:sorting:member'),
	'managed' => elgg_echo('group_tools:groups:sorting:managed'),
];

$listing_sorting_options = [
	'newest' => elgg_echo('sort:newest'),
	'alpha' => elgg_echo('sort:alpha'),
	'popular' => elgg_echo('sort:popular'),
];

$listing_supported_sorting = [
	'all',
	'yours',
	'open',
	'closed',
	'featured',
	'member',
	'managed',
];

// group management settings
$general_fields = [
	[
		'#type' => 'switch',
		'#label' => elgg_echo('group_tools:settings:auto_suggest_groups'),
		'#help' => elgg_echo('group_tools:settings:auto_suggest_groups:help'),
		'name' => 'params[auto_suggest_groups]',
		'value' => $plugin->auto_suggest_groups,
	],
	[
		'#type' => 'switch',
		'#label' => elgg_echo('group_tools:settings:multiple_admin'),
		'name' => 'params[multiple_admin]',
		'value' => $plugin->multiple_admin,
	],
	[
		'#type' => 'switch',
		'#label' => elgg_echo('group_tools:settings:mail'),
		'name' => 'params[mail]',
		'value' => $plugin->mail,
	],
	[
		'#type' => 'switch',
		'#label' => elgg_echo('group_tools:settings:mail:members'),
		'#help' => elgg_echo('group_tools:settings:mail:members:description'),
		'name' => 'params[mail_members]',
		'value' => $plugin->mail_members,
	],
	[
		'#type' => 'switch',
		'#label' => elgg_echo('group_tools:settings:related_groups'),
		'#help' => elgg_echo('group_tools:settings:related_groups:help'),
		'name' => 'params[related_groups]',
		'value' => $plugin->related_groups,
	],
];

$general_settings = '';
foreach ($general_fields as $field) {
	$general_settings .= elgg_view_field($field);
}

echo elgg_view_module('info', elgg_echo('group_tools:settings:management:title'), $general_settings);

// group edit settings
$group_edit = '';

// do admins have to approve new groups
if (elgg_get_plugin_setting('limited_groups', 'groups', 'no') !== 'yes') {
	// only if group creation isn't limited to admins
	$group_edit .= elgg_view_field([
		'#type' => 'switch',
		'#label' => elgg_echo('group_tools:settings:admin_approve'),
		'#help' => elgg_echo('group_tools:settings:admin_approve:description'),
		'name' => 'params[admin_approve]',
		'value' => $plugin->admin_approve,
	]);
	
	$group_edit .= elgg_view_field([
		'#type' => 'switch',
		'#label' => elgg_echo('group_tools:settings:creation_reason'),
		'#help' => elgg_echo('group_tools:settings:creation_reason:description'),
		'name' => 'params[creation_reason]',
		'value' => $plugin->creation_reason,
	]);
}

$group_edit .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:concept_groups'),
	'#help' => elgg_echo('group_tools:settings:concept_groups:description'),
	'name' => 'params[concept_groups]',
	'value' => $plugin->concept_groups,
]);

$group_edit .= elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo('group_tools:settings:concept_groups_retention'),
	'#help' => elgg_echo('group_tools:settings:concept_groups_retention:description'),
	'name' => 'params[concept_groups_retention]',
	'value' => $plugin->concept_groups_retention,
	'min' => 0,
]);

$group_edit .= elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('group_tools:settings:admin_transfer'),
	'name' => 'params[admin_transfer]',
	'options_values' => [
		'no' => elgg_echo('option:no'),
		'admin' => elgg_echo('group_tools:settings:admin_transfer:admin'),
		'owner' => elgg_echo('group_tools:settings:admin_transfer:owner'),
	],
	'value' => $plugin->admin_transfer,
]);

$group_edit .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:owner_transfer_river'),
	'name' => 'params[owner_transfer_river]',
	'value' => $plugin->owner_transfer_river,
]);

$group_edit .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:create_based_on_preset'),
	'#help' => elgg_echo('group_tools:settings:create_based_on_preset:help'),
	'name' => 'params[create_based_on_preset]',
	'value' => $plugin->create_based_on_preset,
]);

$group_edit .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:simple_tool_presets'),
	'#help' => elgg_echo('group_tools:settings:simple_tool_presets:help'),
	'name' => 'params[simple_tool_presets]',
	'value' => $plugin->simple_tool_presets,
]);

$group_edit .= elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('groups:allowhiddengroups'),
	'#help' => elgg_echo('group_tools:settings:allow_hidden_groups:help'),
	'name' => 'params[allow_hidden_groups]',
	'options_values' => [
		'no' => elgg_echo('option:no'),
		'admin' => elgg_echo('group_tools:settings:admin_only'),
		'yes' => elgg_echo('option:yes'),
	],
	'value' => $plugin->allow_hidden_groups ?: elgg_get_plugin_setting('hidden_groups', 'groups', 'no'),
]);

$group_edit .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:auto_accept_membership_requests'),
	'#help' => elgg_echo('group_tools:settings:auto_accept_membership_requests:help'),
	'name' => 'params[auto_accept_membership_requests]',
	'value' => $plugin->auto_accept_membership_requests,
]);

echo elgg_view_module('info', elgg_echo('group_tools:settings:edit:title'), $group_edit);

// listing settings
$body = elgg_echo('group_tools:settings:listing:description');

$listing_tab_rows = [];
// header rows
$cells = [];
$cells[] = elgg_format_element('th', ['rowspan' => 2], '&nbsp;');
$cells[] = elgg_format_element('th', ['rowspan' => 2, 'class' => 'center'], elgg_echo('group_tools:settings:listing:enabled'));
$cells[] = elgg_format_element('th', ['rowspan' => 2, 'class' => 'center'], elgg_echo('group_tools:settings:listing:default_short'));
$cells[] = elgg_format_element('th', ['colspan' => 3, 'class' => 'center'], elgg_echo('sort'));
$listing_tab_rows[] = elgg_format_element('tr', [], implode('', $cells));

$cells = [];
foreach ($listing_sorting_options as $label) {
	$cells[] = elgg_format_element('th', ['class' => 'center'], $label);
}

$listing_tab_rows[] = elgg_format_element('tr', [], implode('', $cells));

foreach ($listing_options as $tab => $tab_title) {
	$cells = [];
	
	// tab name
	$cells[] = elgg_format_element('td', [], $tab_title);
	
	// tab enabled
	$tab_setting_name = "group_listing_{$tab}_available";
	$checkbox_options = [
		'name' => "params[{$tab_setting_name}]",
		'value' => 1,
	];
	$tab_value = $plugin->{$tab_setting_name};
	if ($tab_value !== '0') {
		$checkbox_options['checked'] = true;
	}
	
	$cells[] = elgg_format_element('td', [
		'class' => 'center',
		'title' => elgg_echo('group_tools:settings:listing:available'),
	], elgg_view('input/checkbox', $checkbox_options));
	
	// default tab
	$cells[] = elgg_format_element('td', [
		'class' => 'center',
		'title' => elgg_echo('group_tools:settings:listing:default'),
	], elgg_view('input/radio', [
		'name' => 'params[group_listing]',
		'value' => $plugin->group_listing,
		'options' => [
			'' => $tab,
		],
	]));
	
	// sorting options
	if (in_array($tab, $listing_supported_sorting)) {
		$sorting_name = "group_listing_{$tab}_sorting";
		$sorting_options = [
			'name' => "params[{$sorting_name}]",
			'value' => $plugin->{$sorting_name} ?: 'newest',
		];
		foreach ($listing_sorting_options as $sort => $translation) {
			$sorting_options['options'] = [
				'' => $sort,
			];
			
			$cells[] = elgg_format_element('td', ['class' => 'center',], elgg_view('input/radio', $sorting_options));
		}
	} else {
		$cells[] = elgg_format_element('td', ['colspan' => 3], '&nbsp;');
	}
	
	// add to table rows
	$listing_tab_rows[] = elgg_format_element('tr', [], implode('', $cells));
}

$body .= elgg_format_element('table', ['class' => 'elgg-table-alt'], implode('', $listing_tab_rows));

echo elgg_view_module('info', elgg_echo('group_tools:settings:listing:title'), $body);

// notifications
$body = '';

// show toggle for group notification settings
$body .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:notifications:notification_toggle'),
	'#help' => elgg_echo('group_tools:settings:notifications:notification_toggle:description'),
	'name' => 'params[notification_toggle]',
	'value' => $plugin->notification_toggle,
]);

echo elgg_view_module('info', elgg_echo('group_tools:settings:notifications:title'), $body);

// group invite settings
$invite_settings = '';

$invite_settings .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:invite_email'),
	'name' => 'params[invite_email]',
	'value' => $plugin->invite_email,
]);

$invite_settings .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:invite_csv'),
	'name' => 'params[invite_csv]',
	'value' => $plugin->invite_csv,
]);

$invite_settings .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:domain_based'),
	'#help' => elgg_echo('group_tools:settings:domain_based:description'),
	'name' => 'params[domain_based]',
	'value' => $plugin->domain_based,
]);

$invite_settings .= elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('group_tools:settings:join_motivation'),
	'#help' => elgg_echo('group_tools:settings:join_motivation:description'),
	'name' => 'params[join_motivation]',
	'options_values' => [
		'no' => elgg_echo('option:no'),
		'yes_off' => elgg_echo('group_tools:settings:default_off'),
		'yes_on' => elgg_echo('group_tools:settings:default_on'),
		'required' => elgg_echo('group_tools:settings:required'),
	],
	'value' => $plugin->join_motivation,
]);

echo elgg_view_module('info', elgg_echo('group_tools:settings:invite:title'), $invite_settings);

// group content settings
$group_content = '';

$group_content .= elgg_view_field([
	'#type' => 'switch',
	'#label' => elgg_echo('group_tools:settings:search_index'),
	'name' => 'params[search_index]',
	'value' => $plugin->search_index,
]);

$group_content .= elgg_view_field([
	'#type' => 'number',
	'#label' => elgg_echo('group_tools:settings:stale_timeout'),
	'#help' => elgg_echo('group_tools:settings:stale_timeout:help'),
	'name' => 'params[stale_timeout]',
	'value' => $plugin->stale_timeout,
	'min' => 0,
	'max' => 9999,
]);

echo elgg_view_module('info', elgg_echo('group_tools:settings:content:title'), $group_content);
