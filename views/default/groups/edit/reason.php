<?php
/**
 * Ask for a reason why the group shold be approved
 *
 * In order for the values to be saved they need a name like 'reasons[some_question]'
 * for presentation reasons please use the language key 'group_tools:group:edit:reason:<some_question>'
 */

echo elgg_view('output/longtext', [
	'value' => elgg_echo('group_tools:group:edit:reason:description'),
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('group_tools:group:edit:reason:question'),
	'name' => 'reasons[question]',
	'value' => elgg_extract('question', elgg_extract('reasons', $vars, [])),
	'required' => true,
]);
