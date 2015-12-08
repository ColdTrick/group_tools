<?php

$status = get_input('status');

// breadcrumn
elgg_push_breadcrumb(elgg_echo('discussion'));

// build page elements
$options = array(
	'type' => 'object',
	'subtype' => 'groupforumtopic',
	'order_by' => 'e.last_action desc',
	'limit' => 20,
	'full_view' => false,
);

switch ($status) {
	case 'open':
	case 'closed':
		$options['metadata_name_value_pairs'] = array(
			'status' => $status,
		);
		break;
}

$content = elgg_list_entities_from_metadata($options);
if (empty($content)) {
	$content = elgg_echo('discussion:none');
}

// build page
$params = array(
	'title' => elgg_echo('discussion:latest'),
	'content' => $content,
	'filter_context' => '',
);
$body = elgg_view_layout('content', $params);

// draw page
echo elgg_view_page($title, $body);
