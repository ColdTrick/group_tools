<?php
/**
 * Plugin settings for group tools
 */

/* @var $plugin \ElggPlugin */
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
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:auto_suggest_groups'),
		'#help' => elgg_echo('group_tools:settings:auto_suggest_groups:help'),
		'name' => 'params[auto_suggest_groups]',
		'checked' => $plugin->auto_suggest_groups === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
	],
	[
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:multiple_admin'),
		'name' => 'params[multiple_admin]',
		'checked' => $plugin->multiple_admin === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
	],
	[
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:mail'),
		'name' => 'params[mail]',
		'checked' => $plugin->mail === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
	],
	[
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:mail:members'),
		'#help' => elgg_echo('group_tools:settings:mail:members:description'),
		'name' => 'params[mail_members]',
		'checked' => $plugin->mail_members === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
	],
	[
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:related_groups'),
		'#help' => elgg_echo('group_tools:settings:related_groups:help'),
		'name' => 'params[related_groups]',
		'checked' => $plugin->related_groups === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
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
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:admin_approve'),
		'#help' => elgg_echo('group_tools:settings:admin_approve:description'),
		'name' => 'params[admin_approve]',
		'checked' => $plugin->admin_approve === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
	]);
	
	$group_edit .= elgg_view_field([
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:creation_reason'),
		'#help' => elgg_echo('group_tools:settings:creation_reason:description'),
		'name' => 'params[creation_reason]',
		'checked' => (bool) $plugin->creation_reason,
		'switch' => true,
		'default' => 0,
		'value' => 1,
	]);
}

$group_edit .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:concept_groups'),
	'#help' => elgg_echo('group_tools:settings:concept_groups:description'),
	'name' => 'params[concept_groups]',
	'checked' => (bool) $plugin->concept_groups,
	'switch' => true,
	'default' => 0,
	'value' => 1,
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
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:owner_transfer_river'),
	'name' => 'params[owner_transfer_river]',
	'checked' => (bool) $plugin->owner_transfer_river,
	'switch' => true,
	'default' => 0,
	'value' => 1,
]);

$group_edit .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:simple_access_tab'),
	'#help' => elgg_echo('group_tools:settings:simple_access_tab:help'),
	'name' => 'params[simple_access_tab]',
	'checked' => $plugin->simple_access_tab === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$group_edit .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:create_based_on_preset'),
	'#help' => elgg_echo('group_tools:settings:create_based_on_preset:help'),
	'name' => 'params[create_based_on_preset]',
	'checked' => $plugin->create_based_on_preset === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$group_edit .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:simple_tool_presets'),
	'#help' => elgg_echo('group_tools:settings:simple_tool_presets:help'),
	'name' => 'params[simple_tool_presets]',
	'checked' => $plugin->simple_tool_presets === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
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
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:auto_accept_membership_requests'),
	'#help' => elgg_echo('group_tools:settings:auto_accept_membership_requests:help'),
	'name' => 'params[auto_accept_membership_requests]',
	'checked' => $plugin->auto_accept_membership_requests === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
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
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:notifications:notification_toggle'),
	'#help' => elgg_echo('group_tools:settings:notifications:notification_toggle:description'),
	'name' => 'params[notification_toggle]',
	'checked' => $plugin->notification_toggle === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

echo elgg_view_module('info', elgg_echo('group_tools:settings:notifications:title'), $body);

// group invite settings
$invite_settings = '';

$invite_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:invite_email'),
	'name' => 'params[invite_email]',
	'checked' => $plugin->invite_email === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$invite_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:invite_csv'),
	'name' => 'params[invite_csv]',
	'checked' => $plugin->invite_csv === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$invite_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:domain_based'),
	'#help' => elgg_echo('group_tools:settings:domain_based:description'),
	'name' => 'params[domain_based]',
	'checked' => $plugin->domain_based === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
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
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:search_index'),
	'name' => 'params[search_index]',
	'checked' => $plugin->search_index === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
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
