<?php
/**
 * Forum topic entity view
 *
 * @package ElggGroups
*/

$full = elgg_extract('full_view', $vars, false);
$topic = elgg_extract('entity', $vars, false);

if (!($topic instanceof ElggObject)) {
	return;
}

$poster = $topic->getOwnerEntity();
$group = $topic->getContainerEntity();
$excerpt = elgg_get_excerpt($topic->description);

$poster_icon = elgg_view_entity_icon($poster, 'tiny');
$poster_link = elgg_view('output/url', [
	'href' => $poster->getURL(),
	'text' => $poster->name,
	'is_trusted' => true,
]);
$poster_text = elgg_echo('groups:started', [$poster_link]);
$container_text = '';
if ($group instanceof ElggGroup) {
	if (!elgg_get_page_owner_guid() || (elgg_get_page_owner_guid() != $group->getGUID())) {
		$container_text = elgg_echo('groups:ingroup') . ' ' . elgg_view('output/url', [
			'text' => $group->name,
			'href' => $group->getURL(),
			'is_trusted' => true,
		]);
	}
}

$tags = elgg_view('output/tags', ['tags' => $topic->tags]);
$date = elgg_view_friendly_time($topic->time_created);

$replies_link = '';
$reply_text = '';
$num_replies = elgg_get_entities([
	'type' => 'object',
	'subtype' => 'discussion_reply',
	'container_guid' => $topic->getGUID(),
	'count' => true,
]);

if ($num_replies != 0) {
	$last_reply = elgg_get_entities([
		'type' => 'object',
		'subtype' => 'discussion_reply',
		'container_guid' => $topic->getGUID(),
		'limit' => 1,
	]);
	$last_reply = $last_reply[0];
	
	$reply_poster = $last_reply->getOwnerEntity();
	$reply_time = elgg_view_friendly_time($last_reply->time_created);
	$reply_link = elgg_view('output/url', [
		'text' => $reply_poster->name,
		'href' => $reply_poster->getURL(),
		'is_trusted' => true,
	]);
	$reply_text = elgg_echo('groups:updated', [$reply_link, $reply_time]);

	$replies_link = elgg_view('output/url', [
		'href' => $topic->getURL() . '#group-replies',
		'text' => elgg_echo('group:replies') . " ($num_replies)",
		'is_trusted' => true,
	]);
}

// do not show the metadata and controls in widget view
$metadata = '';
if (!elgg_in_context('widgets')) {
	$metadata = elgg_view_menu('entity', [
		'entity' => $vars['entity'],
		'handler' => 'discussion',
		'sort_by' => 'priority',
		'class' => 'elgg-menu-hz',
	]);
}

if ($full) {
	$title = '';
	if ($topic->status == 'closed') {
		$title .= elgg_format_element('span', ['title' => elgg_echo('groups:topicisclosed')], elgg_view_icon('lock-closed'));
	}
	$title .= $topic->title;
	$subtitle = "$poster_text $date $replies_link";
	
	$params = [
		'entity' => $topic,
		'metadata' => $metadata,
		'title' => $title,
		'subtitle' => $subtitle,
		'tags' => $tags,
	];
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);
	
	$body = elgg_view('output/longtext', ['value' => $topic->description]);
	
	echo elgg_view('object/elements/full', [
		'entity' => $topic,
		'icon' => $poster_icon,
		'summary' => $summary,
		'body' => $body,
	]);
	
} else {
	// brief view
	$title = '';
	if ($topic->status == 'closed') {
		$title .= elgg_format_element('span', ['title' => elgg_echo('groups:topicisclosed')], elgg_view_icon('lock-closed'));
	}
	$title .= elgg_view('output/url', [
		'text' => $topic->title,
		'href' => $topic->getURL(),
		'is_trusted' => true,
	]);
	
	$subtitle = "$poster_text $container_text $date $replies_link <span class='groups-latest-reply'>$reply_text</span>";
	
	$params = [
		'entity' => $topic,
		'metadata' => $metadata,
		'title' => $title,
		'subtitle' => $subtitle,
		'tags' => $tags,
		'content' => $excerpt,
	];
	$params = $params + $vars;
	$list_body = elgg_view('object/elements/summary', $params);
	
	echo elgg_view_image_block($poster_icon, $list_body);
}
