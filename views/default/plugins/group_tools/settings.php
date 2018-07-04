<?php
/**
 * Plugin settings for group tools
 */

$plugin = elgg_extract('entity', $vars);

$listing_options = [
	'all' => elgg_echo('groups:all'),
	'yours' => elgg_echo('groups:yours'),
	'open' => elgg_echo('group_tools:groups:sorting:open'),
	'closed' => elgg_echo('group_tools:groups:sorting:closed'),
	'ordered' => elgg_echo('group_tools:groups:sorting:ordered'),
	'featured' => elgg_echo('status:featured'),
	'suggested' => elgg_echo('group_tools:groups:sorting:suggested'),
];
if (elgg_is_active_plugin('discussions')) {
	$listing_options['discussion'] = elgg_echo('discussion:latest');
}
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
];

$suggested_groups = [];
if (!empty($plugin->suggested_groups)) {
	$suggested_groups = string_to_tag_array($plugin->suggested_groups);
}

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
		'#label' => elgg_echo('group_tools:settings:member_export'),
		'#help' => elgg_echo('group_tools:settings:member_export:description'),
		'name' => 'params[member_export]',
		'checked' => $plugin->member_export === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
	],
];

// do admins have to approve new groups
if (elgg_get_plugin_setting('limited_groups', 'groups', 'no') !== 'yes') {
	// only is group creation isn't limited to admins
	$general_fields[] = [
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:settings:admin_approve'),
		'#help' => elgg_echo('group_tools:settings:admin_approve:description'),
		'name' => 'params[admin_approve]',
		'checked' => $plugin->admin_approve === 'yes',
		'switch' => true,
		'default' => 'no',
		'value' => 'yes',
	];
}

$general_settings = '';
foreach ($general_fields as $field) {
	$general_settings .= elgg_view_field($field);
}

echo elgg_view_module('info', elgg_echo('group_tools:settings:management:title'), $general_settings);

// group edit settings
$group_edit = '';
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
	'#label' => elgg_echo('group_tools:settings:simple_create_form'),
	'#help' => elgg_echo('group_tools:settings:simple_create_form:help'),
	'name' => 'params[simple_create_form]',
	'checked' => $plugin->simple_create_form === 'yes',
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
	$tab_value = $plugin->$tab_setting_name;
	if ($tab_value !== '0') {
		if (in_array($tab, ['ordered', 'featured'])) {
			// these tabs are default disabled
			if ($tab_value !== null) {
				$checkbox_options['checked'] = true;
			}
		} else {
			$checkbox_options['checked'] = true;
		}
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
			'value' => !empty($plugin->$sorting_name) ? $plugin->$sorting_name : 'newest',
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

// auto set notifications
$auto_notifications = elgg_echo('group_tools:settings:auto_notification');

$selected_methods = group_tools_get_default_group_notification_settings();
$methods = elgg_get_notification_methods();

$auto_notifications_lis = [];
foreach ($methods as $method) {
	$auto_notifications_lis[] = elgg_format_element('li', [], elgg_view('input/checkbox', [
		'label' => elgg_echo("notification:method:{$method}"),
		'name' => "params[auto_notification_{$method}]",
		'value' => '1',
		'checked' => in_array($method, $selected_methods),
	]));
}
$auto_notifications .= elgg_format_element('ul', ['class' => 'mll'], implode('', $auto_notifications_lis));

$body .= elgg_format_element('div', [], $auto_notifications);

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
	'#label' => elgg_echo('group_tools:settings:invite_friends'),
	'name' => 'params[invite_friends]',
	'checked' => $plugin->invite_friends === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

$invite_settings .= elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('group_tools:settings:invite'),
	'name' => 'params[invite]',
	'checked' => $plugin->invite === 'yes',
	'switch' => true,
	'default' => 'no',
	'value' => 'yes',
]);

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
	'#label' => elgg_echo('group_tools:settings:invite_email:match'),
	'name' => 'params[invite_email_match]',
	'checked' => $plugin->invite_email_match === 'yes',
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
	'#type' => 'select',
	'#label' => elgg_echo('group_tools:settings:invite_members'),
	'#help' => elgg_echo('group_tools:settings:invite_members:description'),
	'name' => 'params[invite_members]',
	'options_values' => [
		'no' => elgg_echo('option:no'),
		'yes_off' => elgg_echo('group_tools:settings:default_off'),
		'yes_on' => elgg_echo('group_tools:settings:default_on'),
	],
	'value' => $plugin->invite_members,
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

// default group access
// set a context so we can do stuff
elgg_push_context('group_tools_default_access');

$group_content = elgg_view_field([
	'#type' => 'access',
	'#label' => elgg_echo('group_tools:settings:default_access'),
	'name' => 'params[group_default_access]',
	'value' => $plugin->group_default_access,
]);

// restore context
elgg_pop_context();

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

// list all special state groups (features/suggested)
$tabs = [];
$content = '';

// featured
$options = [
	'type' => 'group',
	'limit' => false,
	'metadata_name_value_pairs' => [
		'name' => 'featured_group',
		'value' => 'yes',
	],
];

$featured_groups = elgg_get_entities_from_metadata($options);
if (!empty($featured_groups)) {
	$tabs[] = [
		'text' => elgg_echo('status:featured'),
		'href' => '#group-tools-special-states-featured',
		'selected' => true,
	];
	
	$content .= '<div id="group-tools-special-states-featured">';
	$content .= elgg_view('output/longtext', [
		'value' => elgg_echo('group_tools:settings:special_states:featured:description'),
	]);
	
	$content .= '<table class="elgg-table mtm">';
	
	$content .= '<thead><tr>';
	$content .= elgg_format_element('th', ['colspan' => 2], elgg_echo('groups:name'));
	$content .= '</tr></thead>';
	
	foreach ($featured_groups as $group) {
		$content .= '<tr>';
		$content .= elgg_format_element('td', [], elgg_view('output/url', [
			'href' => $group->getURL(),
			'text' => $group->name,
		]));
		$content .= elgg_format_element('td', ['style' => 'width: 25px;'], elgg_view('output/url', [
			'href' => "action/groups/featured?group_guid={$group->getGUID()}",
			'title' => elgg_echo('remove'),
			'text' => elgg_view_icon('delete'),
			'confirm' => true,
		]));
		$content .= '</tr>';
	}
	
	$content .= '</table>';
	$content .= '</div>';
}

// suggested
if (!empty($suggested_groups)) {
	$class = '';
	$selected = true;
	if (!empty($tabs)) {
		$class = 'hidden';
		$selected = false;
	}
	$tabs[] = [
		'text' => elgg_echo('group_tools:settings:special_states:suggested'),
		'href' => '#group-tools-special-states-suggested',
		'selected' => $selected,
	];
	
	$content .= "<div id='group-tools-special-states-suggested' class='{$class}'>";
	$content .= elgg_view('output/longtext', [
		'value' => elgg_echo('group_tools:settings:special_states:suggested:description'),
	]);
	
	$content .= '<table class="elgg-table mtm">';
	
	$content .= '<tr>';
	$content .= elgg_format_element('th', ['colspan' => 2], elgg_echo('groups:name'));
	$content .= '</tr>';
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'guids' => $suggested_groups,
	];
	
	$groups = new ElggBatch('elgg_get_entities', $options);
	foreach ($groups as $group) {
		$content .= '<tr>';
		$content .= elgg_format_element('td', [], elgg_view('output/url', [
			'href' => $group->getURL(),
			'text' => $group->name,
		]));
		$content .= elgg_format_element('td', ['style' => 'width: 25px;'], elgg_view('output/url', [
			'href' => "action/group_tools/admin/toggle_special_state?group_guid={$group->getGUID()}&state=suggested",
			'title' => elgg_echo('remove'),
			'text' => elgg_view_icon('delete'),
			'confirm' => true,
		]));
		$content .= '</tr>';
	}
	
	$content .= '</table>';
	$content .= '</div>';
}

if (!empty($tabs)) {
	$navigation = '';
	if (count($tabs) > 1) {
		elgg_require_js('group_tools/settings');
		$navigation = elgg_view('navigation/tabs', [
			'tabs' => $tabs,
			'id' => 'group-tools-special-states-tabs',
		]);
	}
	
	echo elgg_view_module('info', elgg_echo('group_tools:settings:special_states'), $navigation . $content);
}

// fix some problems with groups
$rows = [];

// check missing acl members
$missing_acl_members = group_tools_get_missing_acl_users();
if (!empty($missing_acl_members)) {
	$rows[] = [
		elgg_echo('group_tools:settings:fix:missing', [count($missing_acl_members)]),
		elgg_view('output/url', [
			'href' => 'action/group_tools/admin/fix_acl?fix=missing',
			'text' => elgg_echo('group_tools:settings:fix_it'),
			'class' => 'elgg-button elgg-button-action',
			'is_action' => true,
			'style' => 'white-space: nowrap;',
			'confirm' => true,
		]),
	];
}

// check excess acl members
$excess_acl_members = group_tools_get_excess_acl_users();
if (!empty($excess_acl_members)) {
	$rows[] = [
		elgg_echo('group_tools:settings:fix:excess', [count($excess_acl_members)]),
		elgg_view('output/url', [
			'href' => 'action/group_tools/admin/fix_acl?fix=excess',
			'text' => elgg_echo('group_tools:settings:fix_it'),
			'class' => 'elgg-button elgg-button-action',
			'is_action' => true,
			'style' => 'white-space: nowrap;',
			'confirm' => true,
		]),
	];
}

// check groups without acl
$wrong_groups = group_tools_get_groups_without_acl();
if (!empty($wrong_groups)) {
	$rows[] = [
		elgg_echo('group_tools:settings:fix:without', [count($wrong_groups)]),
		elgg_view('output/url', [
			'href' => 'action/group_tools/admin/fix_acl?fix=without',
			'text' => elgg_echo('group_tools:settings:fix_it'),
			'class' => 'elgg-button elgg-button-action',
			'is_action' => true,
			'style' => 'white-space: nowrap;',
			'confirm' => true,
		]),
	];
}

// fix everything at once
if (count($rows) > 1) {
	$rows[] = [
		elgg_echo('group_tools:settings:fix:all:description'),
		elgg_view('output/url', [
			'href' => 'action/group_tools/admin/fix_acl?fix=all',
			'text' => elgg_echo('group_tools:settings:fix:all'),
			'class' => 'elgg-button elgg-button-action',
			'is_action' => true,
			'style' => 'white-space: nowrap;',
			'confirm' => true,
		]),
	];
}

if (!empty($rows)) {
	$content = '<table class="elgg-table">';
	
	foreach ($rows as $row) {
		$content .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
	}
	
	$content .= '</table>';
	
	echo elgg_view_module('info', elgg_echo('group_tools:settings:fix:title'), $content);
}
