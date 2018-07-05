<?php

elgg_gatekeeper();

echo elgg_list_entities([
	'type' => 'group',
	'owner_guid' => elgg_get_logged_in_user_guid(),
	'no_results' => elgg_echo('groups:none'),
]);
