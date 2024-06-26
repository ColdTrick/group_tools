<?php

elgg_import_esm('forms/group_tools/members_search');

echo elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
		[
			'#type' => 'search',
			'#class' => 'elgg-field-stretch',
			'name' => 'members_search',
			'value' => get_input('members_search'),
			'placeholder' => elgg_echo('group_tools:forms:members_search:members_search:placeholder'),
		],
		[
			'#type' => 'submit',
			'icon' => 'search',
			'text' => elgg_echo('search'),
		]
	],
]);
