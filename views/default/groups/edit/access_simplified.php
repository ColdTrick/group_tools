<?php

elgg_import_esm('groups/edit/access_simplified');

$open_text = elgg_format_element('h2', [], elgg_echo('group_tools:edit:access_simplified:open'));
$open_text .= elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:edit:access_simplified:open:description'),
]);

$closed_text = elgg_format_element('h2', [], elgg_echo('group_tools:edit:access_simplified:closed'));
$closed_text .= elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:edit:access_simplified:closed:description'),
]);

$open_text = elgg_format_element('div', [
	'class' => 'group-tools-simplified-option elgg-state-active',
	'data-group-type' => 'open',
], $open_text);

$closed_text = elgg_format_element('div', [
	'class' => 'group-tools-simplified-option',
	'data-group-type' => 'closed',
], $closed_text);

echo elgg_format_element('div', ['class' => ['group-tools-edit-access-simple', 'group-tools-simplified-options']], $open_text . $closed_text);

$params = [
	'_group_tools_simplified_deadloop' => true,
];
$params = $params + $vars;
echo elgg_format_element('div', ['class' => 'hidden'], elgg_view('groups/edit/access', $params));
