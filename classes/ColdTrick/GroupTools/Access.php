<?php

namespace ColdTrick\GroupTools;

class Access {
	
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
}
