<?php

elgg_gatekeeper();

$groups = group_tools_get_suggested_groups(elgg_get_logged_in_user_entity(), 9);
if (empty($groups)) {
	echo elgg_echo('group_tools:suggested_groups:none');
	return;
}

// list suggested groups
echo elgg_view('output/text', [
	'value' => elgg_echo('group_tools:suggested_groups:info'),
]);

echo elgg_view('group_tools/suggested', [
	'groups' => $groups,
]);
