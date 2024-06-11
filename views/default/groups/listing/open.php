<?php
/**
 * Renders a list of open groups
 *
 * @uses $vars['options'] Additional listing options
 */

$options = (array) elgg_extract('options', $vars);

$open_options = [
	'metadata_name_value_pairs' => [
		'name' => 'membership',
		'value' => ACCESS_PUBLIC,
	],
];

$vars['options'] = array_merge($options, $open_options);

echo elgg_view('groups/listing/all', $vars);
