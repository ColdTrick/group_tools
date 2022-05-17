<?php

elgg_gatekeeper();

echo elgg_view('groups/listing/all', [
	'options' => [
		'owner_guid' => elgg_get_logged_in_user_guid(),
	],
]);
