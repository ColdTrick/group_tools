<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggUser) {
	return;
}

$input_name = elgg_extract('input_name', $vars);

// reset the viewtype so we can render html views
$viewtype = elgg_get_viewtype();
elgg_set_viewtype('default');

$icon = elgg_view_entity_icon($entity, 'tiny', [
	'use_link' => false,
	'href' => false,
	'use_hover' => false,
]);

$title_text = $entity->getDisplayName();
$group = elgg_extract('group', $vars);
if ($group instanceof \ElggGroup && $group->isMember($entity)) {
	$title_text .= elgg_format_element('span', ['class' => ['mls', 'elgg-subtext']], elgg_echo('group_tools:groups:invite:member'));
}

$title = elgg_format_element('h3', [], $title_text);

$label = elgg_view_image_block($icon, $title, [
	'class' => 'elgg-autocomplete-item',
]);

$data = $entity->toObject();
$data->label = $label;
$data->value = $entity->username;
$data->icon = $icon;

if ($input_name) {
	$data->html = elgg_view('group_tools/invite/user_html', $vars);
}

echo json_encode($data);

// restore viewtype
elgg_set_viewtype($viewtype);
