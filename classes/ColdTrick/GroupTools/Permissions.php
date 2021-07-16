<?php

namespace ColdTrick\GroupTools;

class Permissions {
	
	/**
	 * Allow a group to be transfered by the correct user
	 *
	 * @param \Elgg\Hook $hook 'permissions_check', 'group'
	 *
	 * @return void|bool
	 */
	public static function allowGroupOwnerTransfer(\Elgg\Hook $hook) {
		
		if (!empty($hook->getValue())) {
			// already has access
			return;
		}
		
		$group = $hook->getEntityParam();
		if (!$group instanceof \ElggGroup) {
			return;
		}
		
		return true;
	}
	
	/**
	 * Allow group admins (not owners) to also edit group content
	 *
	 * @param \Elgg\Hook $hook 'permissions_check', 'group'
	 *
	 * @return void|true
	 */
	public static function allowGroupAdminsToEdit(\Elgg\Hook $hook) {
		
		if ($hook->getValue()) {
			// already has access
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return;
		}
		
		$entity = $hook->getEntityParam();
		$user = $hook->getParam('user');
		if (!$entity instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return;
		}
		
		if (!$entity->isMember($user)) {
			return;
		}
		
		return check_entity_relationship($user->guid, 'group_admin', $entity->guid);
	}
}
