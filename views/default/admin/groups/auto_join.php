<?php

// default auto joins

$content = elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:admin:auto_join:default:description'),
]);

$auto_joins = elgg_get_plugin_setting('auto_join', 'group_tools');
if (!empty($auto_joins)) {
	$auto_joins = string_to_tag_array($auto_joins);
	
	$rows = [];
	
	// header
	$row = [];
	$row[] = elgg_format_element('th', [], '&nbsp;');
	$row[] = elgg_format_element('th', ['colspan' => 2], elgg_echo('groups:name'));
	
	$rows[] = elgg_format_element('tr', [], implode('', $row));
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'guids' => $auto_joins,
	];
	
	$groups = new ElggBatch('elgg_get_entities', $options);
	foreach ($groups as $group) {
		$row = [];
		
		$row[] =  elgg_format_element('td', ['style' => 'width: 40px;', 'class' => 'center'], elgg_view_entity_icon($group, 'tiny'));
		$row[] = elgg_format_element('td', [], elgg_view('output/url', [
			'href' => $group->getURL(),
			'text' => $group->name,
		]));
		$row[] = elgg_format_element('td', ['style' => 'width: 25px;'], elgg_view('output/url', [
			'href' => "action/group_tools/toggle_special_state?group_guid={$group->getGUID()}&state=auto_join",
			'title' => elgg_echo('remove'),
			'text' => elgg_view_icon('delete'),
			'confirm' => true,
		]));
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$content .= elgg_format_element('table', ['class' => 'elgg-table-alt mtm'], implode('', $rows));
} else {
	$content .= elgg_echo('group_tools:admin:auto_join:default:none');
}

$title = elgg_view('output/url', [
	'text' => elgg_view_icon('plus-circle'),
	'href' => 'ajax/form/group_tools/admin/auto_join',
	'title' => elgg_echo('add'),
	'class' => [
		'float-alt',
		'elgg-lightbox',
	],
	'data-colorbox-opts' => json_encode([
		'maxWidth' => '650px',
	]),
]);
$title .= elgg_echo('group_tools:admin:auto_join:default');

echo elgg_view_module('inline', $title, $content);

// additional auto joins

// exclusive auto joins