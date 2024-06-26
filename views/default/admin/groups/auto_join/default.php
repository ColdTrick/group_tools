<?php

$content = elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:admin:auto_join:default:description'),
]);

$auto_joins = elgg_get_plugin_setting('auto_join', 'group_tools');
if (!empty($auto_joins)) {
	$auto_joins = elgg_string_to_array($auto_joins);
	
	$rows = [];
	
	// header
	$row = [];
	$row[] = elgg_format_element('th', ['style' => 'width: 40px;', 'class' => 'center'], '&nbsp;');
	$row[] = elgg_format_element('th', [], elgg_echo('groups:name'));
	
	$rows[] = elgg_format_element('thead', [], elgg_format_element('tr', [], implode('', $row)));
	
	$groups = new \ElggBatch('elgg_get_entities', [
		'type' => 'group',
		'limit' => false,
		'guids' => $auto_joins,
	]);
	
	foreach ($groups as $group) {
		$row = [];
		
		$row[] = elgg_format_element('td', ['style' => 'width: 40px;', 'class' => 'center'], elgg_view_entity_icon($group, 'tiny'));
		$row[] = elgg_format_element('td', [], elgg_view('output/url', [
			'href' => $group->getURL(),
			'text' => $group->getDisplayName(),
		]));
		
		$rows[] = elgg_format_element('tr', [], implode('', $row));
	}
	
	$content .= elgg_format_element('table', ['class' => 'elgg-table-alt mtm'], implode('', $rows));
} else {
	$content .= elgg_echo('group_tools:admin:auto_join:default:none');
}

$menu = elgg_view('output/url', [
	'text' => elgg_echo('edit'),
	'icon' => 'edit',
	'href' => 'ajax/form/group_tools/admin/auto_join/default',
	'class' => [
		'elgg-lightbox',
	],
	'data-colorbox-opts' => json_encode([
		'maxWidth' => '650px',
	]),
]);

echo elgg_view_module('info', elgg_echo('group_tools:admin:auto_join:default'), $content, ['menu' => $menu]);
