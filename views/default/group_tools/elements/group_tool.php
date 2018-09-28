<?php
/**
 * Display one group tool option
 *
 * @uses $vars['group_tool'] the group tool options
 * @uses $vars['value']      the current value of this option
 * @uses $vars['name']       (optional) the name of the input
 * @uses $vars['class']      (optional) class to be put on the wrapper div
 */

/* @var $group_tool \Elgg\Groups\Tool */
$group_tool = elgg_extract('group_tool', $vars);
$value = elgg_extract('value', $vars);

$group_tool_toggle_name = elgg_extract('name', $vars, $group_tool->mapMetadataName());

$help = null;
// optional description
$lan_key = "{$group_tool->name}:group_tool_option:description";
if (elgg_language_key_exists($lan_key)) {
	$help = elgg_echo($lan_key);
}

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => $group_tool->getLabel(),
	'#class' => elgg_extract_class($vars),
	'#help' => $help,
	'name' => $group_tool_toggle_name,
	'value' => 'yes',
	'default' => 'no',
	'checked' => ($value === 'yes'),
	'switch' => true,
]);
