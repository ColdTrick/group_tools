<?php
/**
 * Renders a list of featured groups
 *
 * Note: this view has a corresponding view in the rss view type, changes should be reflected
 */

echo elgg_view('groups/listing/all', [
	'options' => [
		'metadata_name_value_pairs' => [
			'name' => 'featured_group',
			'value' => 'yes',
		],
		'no_results' => elgg_echo('groups:nofeatured'),
	],
]);
