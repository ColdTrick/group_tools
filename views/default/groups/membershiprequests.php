<?php
/**
 * A group"s member requests
 *
 * @uses $vars["entity"]   ElggGroup
 * @uses $vars["requests"] Array of ElggUsers who requested membership
 * @uses $vars["invitations"] Array of ElggUsers who where invited
 */

$group = elgg_extract("entity", $vars);
$requests = elgg_extract("requests", $vars);

if (!empty($requests) && is_array($requests)) {
	elgg_load_js('lightbox');
	elgg_load_css('lightbox');
	
	$content = "<ul class='elgg-list'>";
	
	foreach ($requests as $user) {
		$icon = elgg_view_entity_icon($user, "tiny", array("use_hover" => "true"));

		$user_title = elgg_view("output/url", array(
			"href" => $user->getURL(),
			"text" => $user->name,
			"is_trusted" => true,
		));

		$url = "action/groups/addtogroup?user_guid=" . $user->getGUID() . "&group_guid=" . $group->getGUID();
		$accept_button = elgg_view("output/url", array(
			"href" => $url,
			"text" => elgg_echo("accept"),
			"class" => "elgg-button elgg-button-submit group-tools-accept-request",
			"rel" => $user->getGUID(),
			"is_action" => true
		));

		$form_vars = array(
			'id' => "group-kill-request-{$user->getGUID()}",
			'data-guid' => $user->getGUID(),
		);
		$body_vars = array(
			'group' => $group,
			'user' => $user,
		);
		$decline_form = elgg_view_form('groups/killrequest', $form_vars, $body_vars);
		
		$delete_button = elgg_format_element('div', array('class' => 'hidden'), $decline_form);
		$delete_button .= elgg_view("output/url", array(
			"href" => false,
			"text" => elgg_echo("decline"),
			"class" => "elgg-button elgg-button-delete mlm elgg-lightbox",
			"rel" => $user->getGUID(),
			"data-colorbox-opts" => json_encode(array(
				'inline' => true,
				'href' => "#group-kill-request-{$user->getGUID()}",
				'width' => '600px',
				'closeButton' => false
			)),
		));
		

		$body = "<h4>$user_title</h4>";
		$alt = $accept_button . $delete_button;

		// build output
		$user_listing = elgg_view_image_block($icon, $body, array("image_alt" => $alt));
		
		$attr = array(
			'class' => 'elgg-item',
			'data-guid' => $user->getGUID(),
		);
		$content .= elgg_format_element('li', $attr, $user_listing);
	}
	
	$content .= "</ul>";
	
	// pagination
	$content .= elgg_view("navigation/pagination", $vars);
} else {
	$content = elgg_view("output/longtext", array("value" => elgg_echo("groups:requests:none")));
}

echo $content;
