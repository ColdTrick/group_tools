<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

class Filter {

	/**
	 * Add menu items to the user group invitations page
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:groups/invitations'
	 *
	 * @return void|MenuItems
	 */
	public static function registerUserEmailInvitations(\Elgg\Hook $hook) {
		
		if (elgg_get_plugin_setting('invite_email', 'group_tools') !== 'yes') {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggUser || !$page_owner->canEdit()) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'invitations',
			'text' => elgg_echo('group_tools:menu:group:invitations:invitations'),
			'href' => elgg_generate_url('collection:group:group:invitations', [
				'username' => $page_owner->username,
			]),
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'email_invitations',
			'text' => elgg_echo('group_tools:menu:group:invitations:email_invitations'),
			'href' => elgg_generate_url('collection:annotation:email_invitation:user', [
				'username' => $page_owner->username,
			]),
		]);
		
		return $result;
	}
}
