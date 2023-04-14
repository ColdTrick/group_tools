<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the page menu
 */
class Page {
	
	/**
	 * Add a menu option to the page menu of groups
	 *
	 * @param \Elgg\Event $event 'register', 'menu:page'
	 *
	 * @return null|MenuItems
	 */
	public static function registerGroupMail(\Elgg\Event $event): ?MenuItems {
		if (!elgg_is_logged_in()) {
			return null;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup || !elgg_in_context('groups')) {
			return null;
		}
		
		if (!group_tools_group_mail_enabled($page_owner) && !group_tools_group_mail_members_enabled($page_owner)) {
			return null;
		}
		
		/* @var $return_value MenuItems */
		$return_value = $event->getValue();
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'mail',
			'text' => elgg_echo('group_tools:menu:mail'),
			'href' => elgg_generate_url('add:object:group_tools_group_mail', [
				'guid' => $page_owner->guid,
			]),
		]);
		
		return $return_value;
	}
}
