<?php
/**
 * Renders a list of groups which the user owns
 *
 * @uses $vars['options'] Additional listing options
 */

elgg_gatekeeper();
$options = (array) elgg_extract('options', $vars);

$yours_options = [
	'owner_guid' => elgg_get_logged_in_user_guid(),
];

$vars['options'] = array_merge($options, $yours_options);

echo elgg_view('groups/listing/all', $vars);
