<?php
/**
 * Show an approval reason for a group.
 * This is why a group creator thinks a site admin should approve their group
 *
 * @uses $vars['annotation'] the annotation to show
 */

$annotation = elgg_extract('annotation', $vars);
if (!$annotation instanceof ElggAnnotation) {
	return;
}

$label = substr($annotation->name, strlen('approval_reason:'));

if (elgg_language_key_exists("group_tools:group:edit:reason:{$label}")) {
	$label = elgg_echo("group_tools:group:edit:reason:{$label}");
}

echo elgg_format_element('strong', [], $label);
echo elgg_view('output/longtext', [
	'value' => unserialize($annotation->value),
]);
