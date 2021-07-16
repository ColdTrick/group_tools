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
		
		// related groups
		if (elgg_get_plugin_setting('related_groups', 'group_tools') === 'yes') {
			$tools[] = new \Elgg\Groups\Tool('related_groups', [
				'label' => elgg_echo('groups_tools:related_groups:tool_option'),
				'default_on' => false,
			]);
		}
		
		return $tools;
	}
	
	/**
	 * Listen to the groups/edit action and register events
	 *
	 * @return void
	 */
	public static function editActionListener() {
		
		elgg_register_event_handler('update:after', 'group', self::class . '::acceptMembershipRequests');
	}
	
	/**
	 * Automaticly accept pending membership request for open groups
	 *
	 * @param \Elgg\Event $event 'update:after', 'group'
	 *
	 * @return void
	 */
	public static function acceptMembershipRequests(\Elgg\Event $event) {
		
		$entity = $event->getObject();
		if (!$entity instanceof \ElggGroup || !$entity->canEdit()) {
			return;
		}
		
		if (elgg_get_plugin_setting('auto_accept_membership_requests', 'group_tools') !== 'yes') {
			return;
		}
		
		if (!$entity->isPublicMembership()) {
			return;
		}
		
		// just in case
		set_time_limit(0);
		
		// get pending requests
		$pending_requests = $entity->getEntitiesFromRelationship([
			'type' => 'user',
			'relationship' => 'membership_request',
			'inverse_relationship' => true,
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
		]);
		/* @var $requesting_user \ElggUser */
		foreach ($pending_requests as $requesting_user) {
			// join the group
			$entity->join($requesting_user);
		}
	}
	
	/**
	 * Cleanup group admin status on group leave
	 *
	 * @param \Elgg\Event $event 'leave', 'group'
	 *
	 * @return void|bool
	 */
	public static function removeGroupAdminOnLeave(\Elgg\Event $event) {
		
		$params = $event->getObject();
		
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		if (!$user instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return;
		}
		
		// is the user a group admin
		if (!check_entity_relationship($user->guid, 'group_admin', $group->guid)) {
			return;
		}
		
		return remove_entity_relationship($user->guid, 'group_admin', $group->guid);
	}
}
