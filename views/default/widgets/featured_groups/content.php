<?php
/**
 * content of the featured groups widget
 */

$widget = elgg_extract('entity', $vars);

$num_display = (int) $widget->num_display ?: 5;

$show_random = $widget->show_random;

$featured = elgg_list_entities([
	'type' => 'group',
	'limit' => $num_display,
	'full_view' => false,
	'pagination' => false,
	'metadata_name_value_pairs' => ['featured_group' => 'yes'],
	'order_by' => 'RAND()',
]);

$random = '';
if ($show_random == 'yes') {
	$dbprefix = elgg_get_config('dbprefix');
	
	$random_options = [
		'type' => 'group',
		'limit' => 1,
		'order_by' => 'RAND()',
		'wheres' => ["NOT EXISTS (
			SELECT 1 FROM {$dbprefix}metadata md
			WHERE md.entity_guid = e.guid
				AND md.name = 'featured_group'
				AND md.value = 'yes')",
		],
	];
	
	$random = elgg_list_entities($random_options);
}

$list = $featured . $random;
if (empty($list)) {
	$list = elgg_echo('notfound');
}

echo $list;
