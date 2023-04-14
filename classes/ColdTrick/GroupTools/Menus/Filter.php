<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the filter menu
 */
class Filter {

	/**
	 * Add menu items to the user group invitations page
	 *
	 * @param \Elgg\Event $event 'register', 'menu:filter:groups/invitations'
	 *
	 * @return null|MenuItems
	 */
	public static function registerUserEmailInvitations(\Elgg\Event $event): ?MenuItems {
		if (elgg_get_plugin_setting('invite_email', 'group_tools') !== 'yes') {
			return null;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggUser || !$page_owner->canEdit()) {
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
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
