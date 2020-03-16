<?php
/**
 * Edit the widget
 */

/* @var $widget ElggWidget */
$widget = elgg_extract('entity', $vars);

// filter activity
// get filter options
$filter_contents = [
	'0' => elgg_echo('all'),
];
$registered_entities = get_registered_entity_types();
if (!empty($registered_entities)) {
	foreach ($registered_entities as $type => $subtypes) {
		if (empty($subtypes)) {
			return;
		}
		
		foreach ($subtypes as $subtype) {
			$keyname = "item:{$type}:{$subtype}";
			$filter_contents["{$type},{$subtype}"] = elgg_echo($keyname);
		}
	}
}

// make options
echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'max' => 25,
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('filter'),
	'name' => 'params[activity_filter]',
	'value' => $widget->activity_filter,
	'options_values' => $filter_contents,
]);

// only allow a group picker if not in a group already
if ($widget->context !== 'groups') {
	
	$group_picker_options = [
		'#type' => 'grouppicker',
		'#label' => elgg_echo('widgets:group_river_widget:edit:group'),
		'name' => 'params[group_guid]',
		'value' => $widget->group_guid,
		'options' => [],
	];
	
	$owner = $widget->getOwnerEntity();
	if ($owner instanceof ElggUser) {
		$group_picker_options['options']['match_target'] = $owner->guid;
		$group_picker_options['options']['match_owner'] = false;
		$group_picker_options['options']['match_membership'] = true;
	}
	
	echo elgg_view_field($group_picker_options);
}
