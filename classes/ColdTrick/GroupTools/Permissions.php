<?php

namespace ColdTrick\GroupTools;

/**
 * Permission event handler
 */
class Permissions {
	
	/**
	 * Allow a group to be transferred by the correct user
	 *
	 * @param \Elgg\Event $event 'permissions_check', 'group'
	 *
	 * @return null|bool
	 */
	public static function allowGroupOwnerTransfer(\Elgg\Event $event): ?bool {
		if (!empty($event->getValue())) {
			// already has access
			return null;
		}
		
		$group = $event->getEntityParam();
		if (!$group instanceof \ElggGroup) {
			return null;
		}
		
		return true;
	}
	
	/**
	 * Allow group admins (not owners) to also edit group content
	 *
	 * @param \Elgg\Event $event 'permissions_check', 'group'
	 *
	 * @return null|bool
	 */
	public static function allowGroupAdminsToEdit(\Elgg\Event $event): ?bool {
		if ($event->getValue()) {
			// already has access
			return null;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			// group admins not enabled
			return null;
		}
		
		$entity = $event->getEntityParam();
		$user = $event->getParam('user');
		if (!$entity instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return null;
		}
		
		if (!$entity->isMember($user)) {
			return null;
		}
		
		return $user->hasRelationship($entity->guid, 'group_admin');
	}
}
