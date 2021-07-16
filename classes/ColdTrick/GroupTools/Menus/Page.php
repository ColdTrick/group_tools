<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

class Page {
	
	/**
	 * Registers admin page menu items
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:page'
	 *
	 * @return MenuItems
	 */
	public static function registerAdminItems(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('admin')) {
			return;
		}
		
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups',
			'text' => elgg_echo('admin:groups'),
			'section' => 'configure',
		]);

		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:tool_presets',
			'href' => 'admin/groups/tool_presets',
			'text' => elgg_echo('admin:groups:tool_presets'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);

		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:bulk_delete',
			'href' => 'admin/groups/bulk_delete',
			'text' => elgg_echo('admin:groups:bulk_delete'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);

		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:auto_join',
			'href' => 'admin/groups/auto_join',
			'text' => elgg_echo('admin:groups:auto_join'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);

		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:featured',
			'href' => 'admin/groups/featured',
			'text' => elgg_echo('admin:groups:featured'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);

		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:settings',
			'href' => 'admin/plugin_settings/groups',
			'text' => elgg_echo('settings'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:suggested',
			'href' => 'admin/groups/suggested',
			'text' => elgg_echo('admin:groups:suggested'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:admin_approval',
			'href' => 'admin/groups/admin_approval',
			'text' => elgg_echo('admin:groups:admin_approval'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'groups:repair',
			'href' => 'admin/groups/repair',
			'text' => elgg_echo('admin:groups:repair'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);
				
		return $result;
	}
	
	/**
	 * Add a menu option to the page menu of groups
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:page'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function registerGroupMail(\Elgg\Hook $hook) {
		
		if (!elgg_is_logged_in()) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup || !elgg_in_context('groups')) {
			return;
		}
		
		if (!group_tools_group_mail_enabled($page_owner) && !group_tools_group_mail_members_enabled($page_owner)) {
			return;
		}
		
		$return_value = $hook->getValue();
		
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
