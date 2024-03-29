<?php
/**
 * Annotation view of an e-mail invitation to a group
 *
 * @note To add or remove from the annotation menu, register handlers for the menu:annotation hook.
 *
 * @uses $vars['annotation'] the invitation
 */

$annotation = elgg_extract('annotation', $vars);
if (!$annotation instanceof \ElggAnnotation) {
	return;
}

$owner = $annotation->getOwnerEntity();
if (!$owner instanceof \ElggGroup) {
	return;
}

$page_owner = elgg_get_page_owner_entity();

// value of the annotation is in the format 'secret|e-mail address'
list(, $email) = explode('|', $annotation->value);

$icon = elgg_view_entity_icon($owner, 'tiny');

$title_text = '';
if ($page_owner->guid !== $owner->guid) {
	$title_text = elgg_view('output/url', [
		'text' => $owner->getDisplayName(),
		'href' => $owner->getURL(),
		'is_trusted' => true,
	]);
} else {
	$title_text = elgg_view('output/email', [
		'value' => $email,
	]);
}

$title = elgg_format_element('h4', [], $title_text);

$menu = elgg_view_menu('annotation', [
	'annotation' => $annotation,
	'class' => 'elgg-menu-hz',
]);

$friendlytime = elgg_view_friendly_time($annotation->time_created);
$friendlytime = elgg_format_element('span', ['class' => 'elgg-subtext'], $friendlytime);

$body = elgg_format_element('div', ['class' => 'mbn'], $title . $friendlytime);

echo elgg_view_image_block($icon, $body, ['image_alt' => $menu]);
