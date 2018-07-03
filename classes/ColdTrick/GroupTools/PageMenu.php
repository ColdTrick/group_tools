<?php

namespace ColdTrick\GroupTools;

class PageMenu {
	
	/**
	 * Registers admin page menu items
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:page'
	 *
	 * @return \Collection
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
			'name' => 'groups:admin_approval',
			'href' => 'admin/groups/admin_approval',
			'text' => elgg_echo('admin:groups:admin_approval'),
			'parent_name' => 'groups',
			'section' => 'configure',
		]);
				
		return $result;
	}
}
