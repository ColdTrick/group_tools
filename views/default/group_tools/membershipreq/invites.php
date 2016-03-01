<?php

$group = elgg_extract('entity', $vars);
$invitations = elgg_extract('invitations', $vars);

if (empty($invitations) || !is_array($invitations)) {
	echo elgg_view('output/longtext', [
		'value' => elgg_echo('group_tools:groups:membershipreq:invitations:none'),
	]);
	return;
}

$lis = [];
foreach ($invitations as $user) {
	$icon = elgg_view_entity_icon($user, 'tiny', ['use_hover' => 'true']);
	
	$user_title = elgg_view('output/url', [
		'href' => $user->getURL(),
		'text' => $user->name,
		'is_trusted' => true,
	]);
	
	$url = "action/groups/killinvitation?user_guid={$user->getGUID()}&group_guid={$group->getGUID()}";
	$delete_button = elgg_view('output/url', [
		'href' => $url,
		'confirm' => elgg_echo('group_tools:groups:membershipreq:invitations:revoke:confirm'),
		'text' => elgg_echo('revoke'),
		'class' => 'elgg-button elgg-button-delete mlm',
	]);
	
	$body = elgg_format_element('h4', [], $user_title);
	
	$lis[] = elgg_format_element('li', ['class' => 'elgg-item'], elgg_view_image_block($icon, $body, ['image_alt' => $delete_button]));
}

// show list
echo elgg_format_element('ul', ['class' => 'elgg-list'], implode('', $lis));

// pagination
echo elgg_view('navigation/pagination', $vars);
