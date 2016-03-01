<?php
/**
 * Cleanup the group profile sidebar
 */

$group = elgg_extract('entity', $vars);

if (!($group instanceof ElggGroup) || !$group->canEdit()) {
	return;
}

// load js
elgg_require_js('group_tools/group_edit');

$noyes_options = [
	'no' => elgg_echo('option:no'),
	'yes' => elgg_echo('option:yes'),
];

$featured_options = [
	'no' => elgg_echo('option:no'),
	1 => 1,
	2 => 2,
	3 => 3,
	4 => 4,
	5 => 5,
	6 => 6,
	7 => 7,
	8 => 8,
	9 => 9,
	10 => 10,
	15 => 15,
	20 => 20,
	25 => 25,
];

$featured_sorting = [
	'time_created' => elgg_echo('group_tools:cleanup:featured_sorting:time_created'),
	'alphabetical' => elgg_echo('sort:alpha'),
];

$prefix = 'group_tools:cleanup:';

$form_body = elgg_format_element('div', ['class' => 'elgg-quiet'], elgg_echo('group_tools:cleanup:description'));

// cleanup owner block
$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:owner_block');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'owner_block',
	'options_values' => $noyes_options,
	'value' => $group->getPrivateSetting("{$prefix}owner_block"),
	'class' => 'mls',
]);
$form_body .= elgg_format_element('span', [
	'alt' => elgg_echo('group_tools:explain'),
	'title' => elgg_echo('group_tools:cleanup:owner_block:explain'),
	'onmouseover' => 'elgg.group_tools.cleanup_highlight("owner_block");',
	'onmouseout' => 'elgg.group_tools.cleanup_unhighlight("owner_block");',
	'class' => 'float-alt',
], elgg_view_icon('info'));
$form_body .= '</div>';

// hide group actions
$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:actions');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'actions',
	'options_values' => $noyes_options,
	'value' => $group->getPrivateSetting("{$prefix}actions"),
	'class' => 'mls',
]);
$form_body .= elgg_format_element('span', [
	'alt' => elgg_echo('group_tools:explain'),
	'title' => elgg_echo('group_tools:cleanup:actions:explain'),
	'class' => 'float-alt',
], elgg_view_icon('info'));
$form_body .= '</div>';

// hide group menu items
$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:menu');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'menu',
	'options_values' => $noyes_options,
	'value' => $group->getPrivateSetting("{$prefix}menu"),
	'class' => 'mls',
]);
$form_body .= elgg_format_element('span', [
	'alt' => elgg_echo('group_tools:explain'),
	'title' => elgg_echo('group_tools:cleanup:menu:explain'),
	'onmouseover' => 'elgg.group_tools.cleanup_highlight("menu");',
	'onmouseout' => 'elgg.group_tools.cleanup_unhighlight("menu");',
	'class' => 'float-alt',
], elgg_view_icon('info'));
$form_body .= '</div>';

// hide group search
$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:search');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'search',
	'options_values' => $noyes_options,
	'value' => $group->getPrivateSetting("{$prefix}search"),
	'class' => 'mls',
]);
$form_body .= elgg_format_element('span', [
	'alt' => elgg_echo('group_tools:explain'),
	'title' => elgg_echo('group_tools:cleanup:search:explain'),
	'onmouseover' => 'elgg.group_tools.cleanup_highlight("search");',
	'onmouseout' => 'elgg.group_tools.cleanup_unhighlight("search");',
	'class' => 'float-alt',
], elgg_view_icon('info'));
$form_body .= '</div>';

// hide group members
$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:members');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'members',
	'options_values' => $noyes_options,
	'value' => $group->getPrivateSetting("{$prefix}members"),
	'class' => 'mls',
]);
$form_body .= elgg_format_element('span', [
	'alt' => elgg_echo('group_tools:explain'),
	'title' => elgg_echo('group_tools:cleanup:members:explain'),
	'onmouseover' => 'elgg.group_tools.cleanup_highlight("members");',
	'onmouseout' => 'elgg.group_tools.cleanup_unhighlight("members");',
	'class' => 'float-alt',
], elgg_view_icon('info'));
$form_body .= '</div>';

// show featured groups
$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:featured');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'featured',
	'options_values' => $featured_options,
	'value' => $group->getPrivateSetting("{$prefix}featured"),
	'class' => 'mls',
]);
$form_body .= elgg_format_element('span', [
	'alt' => elgg_echo('group_tools:explain'),
	'title' => elgg_echo('group_tools:cleanup:featured:explain'),
	'onmouseover' => 'elgg.group_tools.cleanup_highlight("featured");',
	'onmouseout' => 'elgg.group_tools.cleanup_unhighlight("featured");',
	'class' => 'float-alt',
], elgg_view_icon('info'));
$form_body .= '</div>';

$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:featured_sorting');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'featured_sorting',
	'options_values' => $featured_sorting,
	'value' => $group->getPrivateSetting("{$prefix}featured_sorting"),
	'class' => 'mls'
]);
$form_body .= '</div>';

// hide my status
$form_body .= '<div>';
$form_body .= elgg_echo('group_tools:cleanup:my_status');
$form_body .= ':' . elgg_view('input/select', [
	'name' => 'my_status',
	'options_values' => $noyes_options,
	'value' => $group->getPrivateSetting("{$prefix}my_status"),
	'class' => 'mls',
]);
$form_body .= elgg_format_element('span', [
	'alt' => elgg_echo('group_tools:explain'),
	'title' => elgg_echo('group_tools:cleanup:my_status:explain'),
	'onmouseover' => 'elgg.group_tools.cleanup_highlight("my_status");',
	'onmouseout' => 'elgg.group_tools.cleanup_unhighlight("my_status");',
	'class' => 'float-alt',
], elgg_view_icon('info'));
$form_body .= '</div>';

// buttons
$form_body .= '<div>';
$form_body .= elgg_view('input/hidden', ['name' => 'group_guid', 'value' => $group->getGUID()]);
$form_body .= elgg_view('input/submit', ['value' => elgg_echo('save')]);
$form_body .= '</div>';

// make body
$title = elgg_echo('group_tools:cleanup:title');
$body = elgg_view('input/form', [
	'action' => 'action/group_tools/cleanup',
	'body' => $form_body,
]);

// show body
echo elgg_view_module('info', $title, $body);
