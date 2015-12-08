<?php

$guid = (int) get_input('guid');
$group = get_entity($guid);
if (!elgg_instanceof($group, 'group')) {
	forward('', '404');
}

$status = get_input('status');

// page owner
elgg_set_page_owner_guid($guid);

// breadcrumb
elgg_push_breadcrumb(elgg_echo('discussion'), 'discussion/all');
elgg_push_breadcrumb($group->name, $group->getURL());
elgg_push_breadcrumb(elgg_echo('item:object:groupforumtopic'));

// buttons
elgg_register_title_button();

group_gatekeeper();

// build page elements
$title = elgg_echo('item:object:groupforumtopic');

$options = array(
	'type' => 'object',
	'subtype' => 'groupforumtopic',
	'limit' => 20,
	'order_by' => 'e.last_action desc',
	'container_guid' => $guid,
	'full_view' => false,
);

// limit to a status?
switch ($status) {
	case 'open':
	case 'closed':
		$options['metadata_name_value_pairs'] = array(
			'status' => $status,
		);
		break;
}

$content = elgg_list_entities_from_metadata($options);
if (!$content) {
	$content = elgg_echo('discussion:none');
}

// make page
$params = array(
	'title' => $title,
	'content' => $content,
	'filter_context' => '',
);

$body = elgg_view_layout('content', $params);

// draw page
echo elgg_view_page($title, $body);