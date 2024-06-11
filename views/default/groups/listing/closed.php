<?php
/**
 * Renders a list of closed/membership request groups
 *
 * @uses $vars['options'] Additional listing options
 */

$options = (array) elgg_extract('options', $vars);

$closed_options = [
	'metadata_name_value_pairs' => [
		'name' => 'membership',
		'value' => ACCESS_PUBLIC,
		'operand' => '<>',
	],
];

$vars['options'] = array_merge($options, $closed_options);

echo elgg_view('groups/listing/all', $vars);
