<?php

namespace ColdTrick\GroupTools;

class Group {
	
	/**
	 * Listen to the groups/edit action and register events
	 *
	 * @return void
	 */
	public static function editActionListener() {
		
		elgg_register_event_handler('update:after', 'group', '\ColdTrick\GroupTools\Group::acceptMembershipRequests');
	}
	
	/**
	 * Automaticly accept pending membership request for open groups
	 *
	 * @param string     $event  the name of the event
	 * @param string     $type   the type of the event
	 * @param \ElggGroup $entity entity to which the event applies
	 *
	 * @return void
	 */
	public static function acceptMembershipRequests($event, $type, $entity) {
		
		if (!($entity instanceof \ElggGroup) || !$entity->canEdit()) {
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
			groups_join_group($entity, $requesting_user);
		}
	}
}
