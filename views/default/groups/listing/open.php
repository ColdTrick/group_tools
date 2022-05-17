<?php

echo elgg_view('groups/listing/all', [
	'options' => [
		'metadata_name_value_pairs' => [
			'name' => 'membership',
			'value' => ACCESS_PUBLIC,
		],
	],
]);
