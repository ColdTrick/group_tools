<?php
/**
 * settings for the index group widget
 */

$widget = elgg_extract('entity', $vars);

// filter based on tag fields
$tag_fields = [];
$profile_fields = elgg_get_config('group');
if (!empty($profile_fields)) {
	foreach ($profile_fields as $name => $type) {
		if ($type !== 'tags') {
			continue;
		}
		
		$lan_key = "groups:{$name}";
		$label = $name;
		if (elgg_language_key_exists($lan_key)) {
			$label = elgg_echo($lan_key);
		}
		
		$tag_fields[$name] = $label;
	}
}

echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'name' => 'group_count',
	'default' => 8,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('widgets:index_groups:featured'),
	'name' => 'params[featured]',
	'checked' => $widget->featured === 'yes',
	'default' => 'no',
	'value' => 'yes',
	'switch' => true,
]);

if (!empty($tag_fields)) {
	$tag_fields = array_reverse($tag_fields);
	$tag_fields[''] = elgg_echo('widgets:index_groups:filter:no_filter');
	$tag_fields = array_reverse($tag_fields);
		
	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('widgets:index_groups:filter:field'),
		'name' => 'params[filter_name]',
		'value' => $widget->filter_name,
		'options_values' => $tag_fields,
	]);
	
	echo elgg_view_field([
		'#type' => 'tags',
		'#label' => elgg_echo('widgets:index_groups:filter:value'),
		'name' => 'params[filter_value]',
		'value' => $widget->filter_value,
	]);
}

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('widgets:index_groups:sorting'),
	'name' => 'params[sorting]',
	'value' => $widget->sorting,
	'options_values' => [
		'newest' => elgg_echo('sort:newest'),
		'popular' => elgg_echo('sort:popular'),
	],
]);
