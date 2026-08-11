<?php

namespace ColdTrick\GroupTools\Plugins;

use Elgg\Collections\Collection;
use Elgg\Groups\Tool;

/**
 * Support for the Groups plugin
 */
class Groups {
	
	/**
	 * Register group tools
	 *
	 * @param \Elgg\Event $event 'tool_options', 'group'
	 *
	 * @return Collection
	 */
	public static function registerGroupTools(\Elgg\Event $event): Collection {
		$tools = $event->getValue();
		
		if (group_tools_group_mail_members_enabled()) {
			$tools[] = new Tool('mail_members', [
				'default_on' => false,
			]);
		}
		
		// related groups
		if (elgg_get_plugin_setting('related_groups', 'group_tools') === 'yes') {
			$tools[] = new Tool('related_groups', [
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
	public static function editActionListener(): void {
		elgg_register_event_handler('update:after', 'group', self::class . '::acceptMembershipRequests');
	}
	
	/**
	 * Automatically accept pending membership request for open groups
	 *
	 * @param \Elgg\Event $event 'update:after', 'group'
	 *
	 * @return void
	 */
	public static function acceptMembershipRequests(\Elgg\Event $event): void {
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
		/** @var \ElggBatch $pending_requests */
		$pending_requests = $entity->getEntitiesFromRelationship([
			'type' => 'user',
			'relationship' => 'membership_request',
			'inverse_relationship' => true,
			'limit' => false,
			'batch' => true,
			'batch_inc_offset' => false,
		]);
		/** @var \ElggUser $requesting_user */
		foreach ($pending_requests as $requesting_user) {
			// join the group
			if (!$entity->join($requesting_user)) {
				$pending_requests->reportFailure();
			}
		}
	}
	
	/**
	 * Cleanup group admin status on group leave
	 *
	 * @param \Elgg\Event $event 'leave', 'group'
	 *
	 * @return null|bool
	 */
	public static function removeGroupAdminOnLeave(\Elgg\Event $event): ?bool {
		$params = $event->getObject();
		
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		if (!$user instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return null;
		}
		
		// is the user a group admin
		if (!$user->hasRelationship($group->guid, 'group_admin')) {
			return null;
		}
		
		return $user->removeRelationship($group->guid, 'group_admin');
	}
}
