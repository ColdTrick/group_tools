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
		
		/** @var MenuItems $return */
		$result = $event->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups',
			'text' => elgg_echo('admin:groups'),
			'href' => false,
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:tool_presets',
			'text' => elgg_echo('admin:groups:tool_presets'),
			'href' => elgg_generate_url('admin', [
				'segments' => 'groups/tool_presets',
			]),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:bulk_delete',
			'text' => elgg_echo('admin:groups:bulk_delete'),
			'href' => elgg_generate_url('admin', [
				'segments' => 'groups/bulk_delete',
			]),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:auto_join',
			'text' => elgg_echo('admin:groups:auto_join'),
			'href' => elgg_generate_url('admin', [
				'segments' => 'groups/auto_join',
			]),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:featured',
			'text' => elgg_echo('admin:groups:featured'),
			'href' => elgg_generate_url('admin', [
				'segments' => 'groups/featured',
			]),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:suggested',
			'text' => elgg_echo('admin:groups:suggested'),
			'href' => elgg_generate_url('admin', [
				'segments' => 'groups/suggested',
			]),
			'parent_name' => 'groups',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:admin_approval',
			'text' => elgg_echo('admin:groups:admin_approval'),
			'href' => elgg_generate_url('admin', [
				'segments' => 'groups/admin_approval',
			]),
			'parent_name' => 'groups',
		]);
		
		return $result;
	}
}
