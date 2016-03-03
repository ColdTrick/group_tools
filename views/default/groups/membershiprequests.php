<?php
/**
 * A group's member requests
 *
 * @uses $vars['entity']   ElggGroup
 * @uses $vars['requests'] Array of ElggUsers who requested membership
 * @uses $vars['invitations'] Array of ElggUsers who where invited
 */

// load js
elgg_require_js('group_tools/membershiprequests');

$group = elgg_extract('entity', $vars);
$requests = elgg_extract('requests', $vars);

if (empty($requests) || !is_array($requests)) {
	echo elgg_view('output/longtext', ['value' => elgg_echo('groups:requests:none')]);
	return;
}

elgg_load_js('lightbox');
elgg_load_css('lightbox');

$content = '<ul class="elgg-list">';

foreach ($requests as $user) {
	$icon = elgg_view_entity_icon($user, 'tiny', ['use_hover' => true]);
	
	$user_title = elgg_view('output/url', [
		'href' => $user->getURL(),
		'text' => $user->name,
		'is_trusted' => true,
	]);
	
	$url = "action/groups/addtogroup?user_guid={$user->getGUID()}&group_guid={$group->getGUID()}";
	$accept_button = elgg_view('output/url', [
		'href' => $url,
		'text' => elgg_echo('accept'),
		'class' => 'elgg-button elgg-button-submit group-tools-accept-request',
		'rel' => $user->getGUID(),
		'is_action' => true,
	]);
	
	$form_vars = [
		'id' => "group-kill-request-{$user->getGUID()}",
		'data-guid' => $user->getGUID(),
	];
	$body_vars = [
		'group' => $group,
		'user' => $user,
	];
	$decline_form = elgg_view_form('groups/killrequest', $form_vars, $body_vars);
	
	$delete_button = elgg_format_element('div', ['class' => 'hidden'], $decline_form);
	$delete_button .= elgg_view('output/url', [
		'href' => false,
		'text' => elgg_echo('decline'),
		'class' => 'elgg-button elgg-button-delete mlm elgg-lightbox',
		'rel' => $user->getGUID(),
		'data-colorbox-opts' => json_encode([
			'inline' => true,
			'href' => "#group-kill-request-{$user->getGUID()}",
			'width' => '600px',
			'closeButton' => false,
		]),
	]);
	
	$body = elgg_format_element('h4', [], $user_title);
	$alt = $accept_button . $delete_button;
	
	// build output
	$user_listing = elgg_view_image_block($icon, $body, array('image_alt' => $alt));

	$attr = [
		'class' => 'elgg-item',
		'data-guid' => $user->getGUID(),
	];
	$content .= elgg_format_element('li', $attr, $user_listing);
}

$content .= '</ul>';

// pagination
$content .= elgg_view('navigation/pagination', $vars);

echo $content;
