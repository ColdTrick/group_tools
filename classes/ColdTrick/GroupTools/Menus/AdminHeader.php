<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the admin_header menu
 */
class AdminHeader {
	
	/**
	 * Registers admin page menu items
	 *
	 * @param \Elgg\Event $event 'register', 'menu:admin_header'
	 *
	 * @return null|MenuItems
	 */
	public static function registerAdminItems(\Elgg\Event $event): ?MenuItems {
		if (!elgg_in_context('admin')) {
			return null;
		}
		
		/* @var $return MenuItems */
		$result = $event->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups',
			'text' => elgg_echo('admin:groups'),
			'href' => false,
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:tool_presets',
			'href' => 'admin/groups/tool_presets',
			'text' => elgg_echo('admin:groups:tool_presets'),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:bulk_delete',
			'href' => 'admin/groups/bulk_delete',
			'text' => elgg_echo('admin:groups:bulk_delete'),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:auto_join',
			'href' => 'admin/groups/auto_join',
			'text' => elgg_echo('admin:groups:auto_join'),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:featured',
			'href' => 'admin/groups/featured',
			'text' => elgg_echo('admin:groups:featured'),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:suggested',
			'href' => 'admin/groups/suggested',
			'text' => elgg_echo('admin:groups:suggested'),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:admin_approval',
			'href' => 'admin/groups/admin_approval',
			'text' => elgg_echo('admin:groups:admin_approval'),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:repair',
			'href' => 'admin/groups/repair',
			'text' => elgg_echo('admin:groups:repair'),
			'parent_name' => 'groups',
		]);
		
		return $result;
	}
}
