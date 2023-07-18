<?php

$auto_joins = (string) elgg_get_plugin_setting('auto_join', 'group_tools');
if (!empty($auto_joins)) {
	$auto_joins = elgg_string_to_array($auto_joins);
} else {
	$auto_joins = [];
}

echo elgg_view_field([
	'#type' => 'grouppicker',
	'#label' => elgg_echo('group_tools:form:admin:auto_join:group'),
	'#help' => elgg_echo('group_tools:form:admin:auto_join:group:help'),
	'name' => 'group_guids',
	'values' => $auto_joins,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'text' => elgg_echo('save'),
]);
elgg_set_form_footer($footer);
