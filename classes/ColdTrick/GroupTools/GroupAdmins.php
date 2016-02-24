<?php

namespace ColdTrick\GroupTools;

class GroupAdmins {
	
	/**
	 * Cleanup group admin status on group leave
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param array  $params supplied params
	 *
	 * @return void|bool
	 */
	public static function groupLeave($event, $type, $params) {
		
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		if (!($user instanceof \ElggUser) || !($group instanceof \ElggGroup)) {
			return;
		}
		
		// is the user a group admin
		if (!check_entity_relationship($user->getGUID(), 'group_admin', $group->getGUID())) {
			return;
		}
		
		return remove_entity_relationship($user->getGUID(), 'group_admin', $group->getGUID());
	}
	
	/**
	 * Notify the group admins about a membership request
	 *
	 * @param string            $event        create
	 * @param string            $type         membership_request
	 * @param \ElggRelationship $relationship the created membership request relation
	 *
	 * @return void
	 */
	public static function membershipRequest($event, $type, $relationship) {
		
		if (!($relationship instanceof \ElggRelationship)) {
			return;
		}
		
		if ($relationship->relationship !== 'membership_request') {
			return;
		}
		
		// only send a message if group admins are allowed
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		$user = get_user($relationship->guid_one);
		$group = get_entity($relationship->guid_two);
		if (empty($user) || !($group instanceof \ElggGroup)) {
			return;
		}
		
		// Notify group admins
		$options = [
			'relationship' => 'group_admin',
			'relationship_guid' => $group->getGUID(),
			'inverse_relationship' => true,
			'type' => 'user',
			'limit' => false,
			'wheres' => [
				"e.guid <> {$group->getOwnerGUID()}",
			],
		];
		
		$url = elgg_normalize_url("groups/requests/{$group->getGUID()}");
		$subject = elgg_echo('groups:request:subject', [
			$user->name,
			$group->name,
		]);
		
		$admins = new \ElggBatch('elgg_get_entities_from_relationship', $options);
		foreach ($admins as $a) {
			$body = elgg_echo('groups:request:body', [
				$a->name,
				$user->name,
				$group->name,
				$user->getURL(),
				$url,
			]);
				
			notify_user($a->getGUID(), $user->getGUID(), $subject, $body);
		}
	}
	
	/**
	 * Allow group admins (not owners) to also edit group content
	 *
	 * @param string $hook         the 'permissions_check' hook
	 * @param string $type         for the 'group' type
	 * @param bool   $return_value the current value
	 * @param array  $params       supplied params to help change the outcome
	 *
	 * @return void|true
	 */
	public function permissionsCheck($hook, $type, $return_value, $params) {
		
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		$user = elgg_extract('user', $params);

		if (!($entity instanceof ElggGroup) || !($user instanceof ElggUser)) {
			return;
		}
		
		if (!$entity->isMember($user)) {
			return;
		}
		
		if (check_entity_relationship($user->getGUID(), 'group_admin', $entity->getGUID())) {
			return true;
		}
	}
}
