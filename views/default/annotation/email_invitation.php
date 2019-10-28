<?php
/**
 * Annotation view of an e-mail invitation to a group
 *
 * @note To add or remove from the annotation menu, register handlers for the menu:annotation hook.
 *
 * @uses $vars['annotation'] the invitation
 */

$annotation = elgg_extract('annotation', $vars);
if (!$annotation instanceof ElggAnnotation) {
	return;
}

$owner = $annotation->getOwnerEntity();
if (!$owner instanceof ElggGroup) {
	return;
}

// value of the annotation is in the format 'secret|e-mail address'
list(, $email) = explode('|', $annotation->value);

$icon = elgg_view_entity_icon($owner, 'tiny');

$title = elgg_format_element('h4', [], elgg_view('output/email', [
	'value' => $email,
]));

$menu = elgg_view_menu('annotation', [
	'annotation' => $annotation,
	'class' => 'elgg-menu-hz',
]);

$friendlytime = elgg_view_friendly_time($annotation->time_created);

$body = <<<HTML
<div class="mbn">
	$title
	<span class="elgg-subtext">
		$friendlytime
	</span>
</div>
HTML;

echo elgg_view_image_block($icon, $body, ['image_alt' => $menu]);
