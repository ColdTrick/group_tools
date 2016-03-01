<?php
/**
* A user's group invitations
*
* @uses $vars['invitations'] Array of ElggGroups
*/

$user = elgg_extract('user', $vars);
$invitations = elgg_extract('invitations', $vars);
$email_invites = elgg_extract('email_invitations', $vars, false);

if ((!empty($invitations) && is_array($invitations)) || (!empty($email_invites) && is_array($email_invites))) {
	
	echo '<ul class="elgg-list mbm">';
	
	// normal invites
	if (!empty($invitations)) {
		foreach ($invitations as $group) {
			if (!($group instanceof ElggGroup)) {
				continue;
			}
			
			$icon = elgg_view_entity_icon($group, 'tiny', ['use_hover' => true]);

			$group_title = elgg_view('output/url', [
				'href' => $group->getURL(),
				'text' => $group->name,
				'is_trusted' => true,
			]);

			$url = "action/groups/join?user_guid={$user->getGUID()}&group_guid={$group->getGUID()}";
			$accept_button = elgg_view('output/url', [
				'href' => $url,
				'text' => elgg_echo('accept'),
				'class' => 'elgg-button elgg-button-submit',
				'is_trusted' => true,
				'is_action' => true,
			]);

			$url = "action/groups/killinvitation?user_guid={$user->getGUID()}&group_guid={$group->getGUID()}";
			$delete_button = elgg_view('output/url', [
				'href' => $url,
				'confirm' => elgg_echo('groups:invite:remove:check'),
				'text' => elgg_echo('delete'),
				'class' => 'elgg-button elgg-button-delete mlm',
			]);

			$body = elgg_format_element('h4', [], $group_title);
			$body .= elgg_format_element('p', ['class' => 'elgg-subtext'], $group->briefdescription);
			
			$alt = $accept_button . $delete_button;

			echo elgg_format_element('li', ['class' => 'pvs'], elgg_view_image_block($icon, $body, ['image_alt' => $alt]));
		}
	}
	
	// auto detected email invitations
	if (!empty($email_invites)) {
		foreach ($email_invites as $group) {
			$icon = elgg_view_entity_icon($group, 'tiny', ['use_hover' => 'true']);
		
			$group_title = elgg_view('output/url', [
				'href' => $group->getURL(),
				'text' => $group->name,
				'is_trusted' => true,
			]);
			
			$invitecode = group_tools_generate_email_invite_code($group->getGUID(), $user->email);
			$url = "action/groups/email_invitation?invitecode={$invitecode}";
			$accept_button = elgg_view('output/url', [
				'href' => $url,
				'text' => elgg_echo('accept'),
				'class' => 'elgg-button elgg-button-submit',
				'is_trusted' => true,
				'is_action' => true,
			]);
			
			$url = "action/groups/decline_email_invitation?invitecode={$invitecode}";
			$delete_button = elgg_view('output/url', [
				'href' => $url,
				'confirm' => elgg_echo('groups:invite:remove:check'),
				'text' => elgg_echo('delete'),
				'class' => 'elgg-button elgg-button-delete mlm',
			]);
		
			$body = elgg_format_element('h4', [], $group_title);
			$body .= elgg_format_element('p', ['class' => 'elgg-subtext'], $group->briefdescription);
			
			$alt = $accept_button . $delete_button;
			
			echo elgg_format_element('li', ['class' => 'pvs'], elgg_view_image_block($icon, $body, ['image_alt' => $alt]));
		}
	}
	
	echo '</ul>';
} else {
	echo elgg_format_element('p', ['class' => 'mts'], elgg_echo('groups:invitations:none'));
}

// list membership requests
if (elgg_get_context() == 'groups') {
	// get requests
	$requests = elgg_extract('requests', $vars);
	
	$title = elgg_echo('group_tools:group:invitations:request');
	
	if (!empty($requests) && is_array($requests)) {
		$content = '<ul class="elgg-list">';
		
		foreach ($requests as $group) {
			$icon = elgg_view_entity_icon($group, 'tiny', ['use_hover' => true]);
			
			$group_title = elgg_view('output/url', [
				'href' => $group->getURL(),
				'text' => $group->name,
				'is_trusted' => true,
			]);
			
			$url = "action/groups/killrequest?user_guid={$user->getGUID()}&group_guid={$group->getGUID()}";
			$delete_button = elgg_view('output/url', [
				'href' => $url,
				'confirm' => elgg_echo('group_tools:group:invitations:request:revoke:confirm'),
				'text' => elgg_echo('revoke'),
				'class' => 'elgg-button elgg-button-delete mlm',
			]);
			
			$body = elgg_format_element('h4', [], $group_title);
			$body .= elgg_format_element('p', ['class' => 'elgg-subtext'], $group->briefdescription);
			
			$alt = $delete_button;
			
			$content .= elgg_format_element('li', ['class' => 'pvs'], elgg_view_image_block($icon, $body, ['image_alt' => $alt]));
		}
		
		$content .= '</ul>';
	} else {
		$content = elgg_echo('group_tools:group:invitations:request:non_found');
	}
	
	echo elgg_view_module('info', $title, $content);
	
	// show e-mail invitation form
	if (elgg_extract('invite_email', $vars, false)) {
		// make the form for the email invitations
		$form_body = elgg_format_element('div', [], elgg_echo('group_tools:groups:invitation:code:description'));
		$form_body .= elgg_view('input/text', [
			'name' => 'invitecode',
			'value' => get_input('invitecode'),
			'class' => 'mbm',
		]);
		
		$form_body .= elgg_format_element('div', [], elgg_view('input/submit', ['value' => elgg_echo('submit')]));
		
		$form = elgg_view('input/form', [
			'body' => $form_body,
			'action' => 'action/groups/email_invitation',
		]);
		
		echo elgg_view_module('info', elgg_echo('group_tools:groups:invitation:code:title'), $form);
	}
}
