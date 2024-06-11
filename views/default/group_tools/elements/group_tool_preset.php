<?php
/**
 * Group tool preset wrapper
 *
 * @uses $vars['values']       the current configuration
 * @uses $vars['index']        the index of the configuration
 * @uses $vars['wrapper_vars'] additional vars to set on the wrapper
 */

$index = elgg_extract('index', $vars, 'i');
$values = elgg_extract('values', $vars);
$wrapper_vars = (array) elgg_extract('wrapper_vars', $vars);

$group_tools = elgg()->group_tools->all();

$title = elgg_format_element('div', ['class' => 'group-tools-group-preset-title'], elgg_extract('title', $values, elgg_echo('title')));

$menu = elgg_view('output/url', [
	'icon' => 'edit',
	'text' => elgg_echo('edit'),
	'href' => false,
	'class' => ['group-tools-admin-edit-tool-preset', 'mrm'],
]);
$menu .= elgg_view('output/url', [
	'icon' => 'delete',
	'text' => elgg_echo('delete'),
	'href' => false,
	'class' => 'group-tools-admin-delete-tool-preset',
]);

$description = elgg_view('output/longtext', [
	'class' => ['elgg-quiet', 'mtn', 'group-tools-group-preset-description'],
	'value' => elgg_extract('description', $values, elgg_echo('description')),
]);

$edit = elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('title'),
	'name' => "params[{$index}][title]",
	'value' => elgg_extract('title', $values),
	'class' => 'group-tools-admin-change-tool-preset-title',
]);

$edit .= elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('description'),
	'name' => "params[{$index}][description]",
	'value' => elgg_extract('description', $values),
	'class' => 'group-tools-admin-change-tool-preset-description',
]);

/* @var $group_tool \Elgg\Groups\Tool */
foreach ($group_tools as $group_tool) {
	$metadata_name = $group_tool->mapMetadataName();
	
	$edit .= elgg_view('groups/edit/tool', [
		'tool' => $group_tool,
		'value' => elgg_extract($metadata_name, $values['tools']),
		'name' => "params[{$index}][tools][{$metadata_name}]",
		'class' => 'mbs',
	]);
}

$edit = elgg_format_element('div', ['class' => ['group-tools-group-preset-edit', 'hidden']], $edit);

$wrapper_vars['class'] = elgg_extract_class($wrapper_vars, ['group-tools-group-preset-wrapper']);
$wrapper_vars['image_alt'] = $menu;

echo elgg_view_image_block('', $title . $description . $edit, $wrapper_vars);
