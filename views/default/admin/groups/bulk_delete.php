<?php

$limit = (int) get_input('limit', 50);
if ($limit < 1) {
	$limit = 50;
}

$offset = (int) get_input('offset', 0);
if ($offset < 1) {
	$offset = 0;
}

$options = [
	'type' => 'group',
	'limit' => $limit,
	'offset' => $offset,
	'sort_by' => [
		'property' => 'name',
		'direction' => 'ASC',
	],
];

$group_count = elgg_count_entities($options);
if ($group_count < 1) {
	echo elgg_echo('groups:none');
	return;
}

elgg_import_esm('admin/groups/bulk_delete');

$batch = new ElggBatch('elgg_get_entities', $options);

$delete_button = elgg_view('input/submit', [
	'text' => elgg_echo('group_tools:delete_selected'),
	'class' => 'elgg-button-submit float-alt mvs',
	'data-confirm' => elgg_echo('deleteconfirm:plural'),
]);

$form_data = $delete_button;

$form_data .= '<table class="elgg-table">';

$form_data .= '<thead>';
$form_data .= '<tr>';
$form_data .= elgg_format_element('th', ['class' => 'center'], elgg_view('input/checkbox', ['name' => 'checkall', 'default' => false]));
$form_data .= elgg_format_element('th', [], elgg_echo('groups:group'));
$form_data .= elgg_format_element('th', ['class' => 'center'], elgg_echo('groups:edit'));
$form_data .= elgg_format_element('th', ['class' => 'center'], elgg_echo('delete'));
$form_data .= '</tr>';
$form_data .= '</thead>';

// add group rows
$rows = [];
foreach ($batch as $group) {
	$cells = [];
	
	// brief view
	$icon = elgg_view_entity_icon($group, 'tiny');
	$params = [
		'entity' => $group,
		'metadata' => '',
		'subtitle' => $group->briefdescription,
	];
	$list_body = elgg_view('group/elements/summary', $params);
	$group_summary = elgg_view_image_block($icon, $list_body);
	
	$cells[] = elgg_format_element('td', ['class' => 'center'], elgg_view('input/checkbox', [
		'name' => 'group_guids[]',
		'value' => $group->guid,
		'default' => false,
	]));
	$cells[] = elgg_format_element('td', [], $group_summary);
	$cells[] = elgg_format_element('td', ['class' => 'center'], elgg_view('output/url', [
		'icon' => 'settings-alt',
		'text' => false,
		'title' => elgg_echo('edit'),
		'href' => elgg_generate_entity_url($group, 'edit'),
	]));
	$cells[] = elgg_format_element('td', ['class' => 'center'], elgg_view('output/url', [
		'icon' => 'delete-alt',
		'text' => false,
		'title' => elgg_echo('delete'),
		'confirm' => elgg_echo('deleteconfirm'),
		'href' => elgg_generate_action_url('entity/delete', [
			'guid' => $group->guid,
		]),
	]));
	
	$rows[] = elgg_format_element('tr', [], implode(PHP_EOL, $cells));
}

$form_data .= elgg_format_element('tbody', [], implode(PHP_EOL, $rows));

$form_data .= '</table>';

$form_data .= $delete_button;

// pagination
$form_data .= elgg_view('navigation/pagination', [
	'limit' => $limit,
	'offset' => $offset,
	'count' => $group_count,
]);

echo elgg_view('input/form', [
	'id' => 'group-tools-admin-bulk-delete',
	'action' => 'action/group_tools/admin/bulk_delete',
	'body' => $form_data,
]);
