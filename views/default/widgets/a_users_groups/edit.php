<?php
/**
 * Elgg group widget edit view
 *
 * added: sort_by options
 */

/* @var $widget \ElggWidget */
$widget = elgg_extract('entity', $vars);

if ($widget->context !== 'profile') {
	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('widgets:a_users_groups:sort_by'),
		'name' => 'params[sort_by]',
		'value' => $widget->sort_by,
		'options_values' => [
			'alpha' => elgg_echo('sort:alpha'),
			'activity' => elgg_echo('widgets:a_users_groups:sort_by:activity'),
			'join_date' => elgg_echo('widgets:a_users_groups:sort_by:join_date'),
		],
	]);
}

echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'label' => elgg_echo('groups:widget:num_display'),
	'default' => 4,
]);
