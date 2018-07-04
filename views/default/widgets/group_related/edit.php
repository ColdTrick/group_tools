<?php
/**
 * Edit view of the related groups widget
 */

$widget = elgg_extract('entity', $vars);

echo elgg_view('object/widget/edit/num_display', [
	'entity' => $widget,
	'default' => 4,
]);
