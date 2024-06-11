<?php
/**
 * Ask for a reason why the group should be approved
 *
 * In order for the values to be saved they need a name like 'reasons[some_question]'
 * for presentation reasons please use the language key 'group_tools:group:edit:reason:<some_question>'
 */

$entity = elgg_extract('entity', $vars);
if ($entity instanceof \ElggGroup) {
	return;
}

$admin_approve = elgg_get_plugin_setting('admin_approve', 'group_tools') === 'yes';
$admin_approve = $admin_approve && !elgg_is_admin_logged_in();
$ask_reason = (bool) elgg_get_plugin_setting('creation_reason', 'group_tools');
if (!$admin_approve || !$ask_reason) {
	return;
}

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
