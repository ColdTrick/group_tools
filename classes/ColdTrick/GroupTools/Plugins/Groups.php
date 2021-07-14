<?php

namespace ColdTrick\GroupTools\Plugins;

use Elgg\Collections\Collection;
use Elgg\Groups\Tool;

class Groups {
	
	/**
	 * Register group tools
	 *
	 * @param \Elgg\Hook $hook 'tool_options', 'group'
	 *
	 * @return Collection
	 */
	public static function registerGroupTools(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		$tools = $hook->getValue();
		
		if (group_tools_multiple_admin_enabled()) {
			// multiple admins can only be configured for:
			// - new groups
			// - by the group owner (not other admins)
			// - by a site admin
			if (!$entity instanceof \ElggGroup || $entity->owner_guid === elgg_get_logged_in_user_guid() || elgg_is_admin_logged_in()) {
				// add group tool option
				$tools[] = new Tool('group_multiple_admin_allow', [
					'label' => elgg_echo('group_tools:multiple_admin:group_tool_option'),
					'default_on' => false,
				]);
			}
		}
		
		if (group_tools_group_mail_members_enabled()) {
			$tools[] = new Tool('mail_members', [
				'label' => elgg_echo('group_tools:tools:mail_members'),
				'default_on' => false,
			]);
		}
		
		return $tools;
	}
}
