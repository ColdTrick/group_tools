<?php

namespace ColdTrick\GroupTools;

class Access {
	
	/**
	 * Change the default access in group context
	 *
	 * @param \Elgg\Hook $hook 'default', 'access'
	 *
	 * @return void|int
	 */
	public static function setGroupDefaultAccess(\Elgg\Hook $hook) {
		
		// check if the page owner is a group
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			return;
		}
		
		// check if the group as a default access set
		$group_access = $page_owner->getPrivateSetting('elgg_default_access');
		if ($group_access !== null) {
			return (int) $group_access;
		}
		
		// if the group hasn't set anything check if there is a site setting for groups
		$site_group_access = elgg_get_plugin_setting('group_default_access', 'group_tools');
		if ($site_group_access === null) {
			// no site setting and no group, so leave default
			return;
		}
		
		switch ($site_group_access) {
			case GROUP_TOOLS_GROUP_ACCESS_DEFAULT:
				$acl = $page_owner->getOwnedAccessCollection('group_acl');
				if (!$acl instanceof \ElggAccessCollection) {
					return;
				}
				return $acl->id;
				break;
			default:
				return (int) $site_group_access;
				break;
		}
	}
	
	/**
	 * Validate that the group default access isn't above the group settings for content
	 *
	 * @param \Elgg\Hook $hook 'default', 'access'
	 *
	 * @return void|int
	 */
	public static function validateGroupDefaultAccess(\Elgg\Hook $hook) {
		
		// check if the page owner is a group
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			return;
		}
		
		if ($page_owner->getContentAccessMode() !== \ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY) {
			return;
		}
		
		$return_value = $hook->getValue();
		if (($return_value !== ACCESS_PUBLIC) && ($return_value !== ACCESS_LOGGED_IN)) {
			return;
		}
		
		// default access is higher than content access level, so lower
		elgg_log("Default access for the group {$page_owner->getDisplayName()} is set more public than the content access level", 'NOTICE');
		
		$acl = $page_owner->getOwnedAccessCollection('group_acl');
		if (!$acl instanceof \ElggAccessCollection) {
			return;
		}
		
		return (int) $acl->id;
	}
	
	/**
	 * Change the access list when selecting group default access
	 *
	 * @param \Elgg\Hook $hook 'access:collections:write', 'user'
	 *
	 * @return void|array
	 */
	public static function defaultAccessOptions(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('group_tools_default_access')) {
			return;
		}
		
		$return_value = $hook->getValue();
		if (!is_array($return_value)) {
			return;
		}
		
		// unset ACCESS_PRIVATE & ACCESS_FRIENDS;
		if (isset($return_value[ACCESS_PRIVATE])) {
			unset($return_value[ACCESS_PRIVATE]);
		}
		
		if (isset($return_value[ACCESS_FRIENDS])) {
			unset($return_value[ACCESS_FRIENDS]);
		}
		
		// reverse the array
		$return_value = array_reverse($return_value, true);
		
		// add group option
		$return_value[GROUP_TOOLS_GROUP_ACCESS_DEFAULT] = elgg_echo('group_tools:default:access:group');
		
		return $return_value;
	}
	
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
