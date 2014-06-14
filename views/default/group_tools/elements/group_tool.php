<?php
/**
 * Display one group tool option
 * 
 * @uses $vars['group_tool'] the group tool options
 * @uses $vars['value']      the current value of this option
 * @uses $vars['name']       (optional) the name of the input
 * @uses $vars['class']      (optional) class to be put on the wrapper div
 */

$group_tool = elgg_extract("group_tool", $vars);
$value = elgg_extract("value", $vars);

$group_tool_toggle_name = elgg_extract("name", $vars, $group_tool->name . "_enable");

$class = elgg_extract("class", $vars);
if (!empty($class)) {
	$class = "class='" . $class . "'";
}

echo "<div " . $class . ">";

echo elgg_view("input/radio", array(
	"name" => $group_tool_toggle_name,
	"value" => $value,
	"options" => array(
		elgg_echo("option:yes") => "yes",
		elgg_echo("option:no") => "no",
	),
	"align" => "horizontal",
	"class" => "float-alt"
));

echo "<label>" . $group_tool->label . "</label><br />";

// optional description
$lan_key = $group_tool->name . ":group_tool_option:description";
if (elgg_echo($lan_key) != $lan_key) {
	echo elgg_view("output/longtext", array("value" => elgg_echo($lan_key), "class" => "elgg-quiet mtn"));
}

echo "</div>";
