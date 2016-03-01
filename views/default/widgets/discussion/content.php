<?php
/**
 * content of the discussions widget
 */

$widget = elgg_extract('entity', $vars);

$discussion_count = sanitise_int($widget->discussion_count, false);
if ($discussion_count < 1) {
	$discussion_count = 5;
}

$options = [
	'type' => 'object',
	'subtype' => 'groupforumtopic',
	'limit' => $discussion_count,
	'order_by' => 'e.last_action desc',
	'pagination' => false,
	'full_view' => false,
];

if ($widget->group_only == 'yes') {
	$owner = $widget->getOwnerEntity();
	$groups = $owner->getGroups('', false);
	
	if (!empty($groups)) {
		
		$group_guids = array();
		foreach ($groups as $group) {
			$group_guids[] = $group->getGUID();
		}
		$options['container_guids'] = $group_guids;
	}
}

$content = elgg_list_entities($options);
if (empty($content)) {
	$content = elgg_echo('grouptopic:notcreated');
} else {
	$content .= elgg_format_element('div', ['class' => 'elgg-widget-more'], elgg_view('output/url', [
		'text' => elgg_echo('widgets:discussion:more'),
		'href' => 'discussion/all',
		'is_trusted' => true,
	]));
}

// prepend a quick start form
$params = $vars;
$params['embed'] = true;
echo elgg_view('widgets/start_discussion/content', $params);

// view listing of discussions
echo $content;
