<?php

$group = elgg_extract('entity', $vars);
if (!$group instanceof \ElggGroup) {
	return;
}

$fields = [
	[
		'#type' => 'grouppicker',
		'#help' => elgg_echo('group_tools:related_groups:form:description'),
		'name' => 'guid',
		'placeholder' => elgg_echo('group_tools:related_groups:form:placeholder'),
		'limit' => 1,
	],
	[
		'#type' => 'submit',
		'text' => elgg_echo('add'),
	],
	[
		'#type' => 'hidden',
		'name' => 'group_guid',
		'value' => $group->guid,
	],
];

echo elgg_view_field([
	'#type' => 'fieldset',
	'fields' => $fields,
	'align' => 'horizontal',
]);
