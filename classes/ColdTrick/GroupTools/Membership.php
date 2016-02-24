<?php

namespace ColdTrick\GroupTools;

class Membership {
	
	protected static $AUTO_NOTIFICATIONS;
	
	/**
	 * Load the plugin settings for notification settings on group join
	 *
	 * @return void
	 */
	protected static function loadAutoNotifications() {
		
		if (isset(self::$AUTO_NOTIFICATIONS)) {
			return;
		}
		
		self::$AUTO_NOTIFICATIONS = [];
		
		$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethods();
		if (empty($NOTIFICATION_HANDLERS) || !is_array($NOTIFICATION_HANDLERS)) {
			return;
		}
		
		if (elgg_get_plugin_setting('auto_notification', 'group_tools') === 'yes') { // Backwards compatibility
			self::$AUTO_NOTIFICATIONS = ['email', 'site'];
		}
		
		foreach ($NOTIFICATION_HANDLERS as $method => $foo) {
			$plugin_setting = (int) elgg_get_plugin_setting("auto_notification_{$method}", 'group_tools');
			if (empty($plugin_setting)) {
				continue;
			}
			
			self::$AUTO_NOTIFICATIONS[] = $method;
		}
		
		self::$AUTO_NOTIFICATIONS = array_unique(self::$AUTO_NOTIFICATIONS);
	}
	
	/**
	 * Listen to the delete of a membership request
	 *
	 * @param stirng            $event        the name of the event
	 * @param stirng            $type         the type of the event
	 * @param \ElggRelationship $relationship the relationship
	 *
	 * @return void
	 */
	public static function deleteRequest($event, $type, $relationship) {
		
		if (!($relationship instanceof \ElggRelationship)) {
			return;
		}
		
		if ($relationship->relationship !== 'membership_request') {
			// not a membership request
			return;
		}
		
		$action_pattern = '/action\/groups\/killrequest/i';
		if (!preg_match($action_pattern, current_page_url())) {
			// not in the action, so do nothing
			return;
		}
		
		$group = get_entity($relationship->guid_two);
		$user = get_user($relationship->guid_one);
		
		if (empty($user) || !($group instanceof \ElggGroup)) {
			return;
		}
		
		if ($user->getGUID() === elgg_get_logged_in_user_guid()) {
			// user kills own request
			return;
		}
		
		$reason = get_input('reason');
		if (empty($reason)) {
			$body = elgg_echo('group_tools:notify:membership:declined:message', [
				$user->name,
				$group->name,
				$group->getURL(),
			]);
		} else {
			$body = elgg_echo('group_tools:notify:membership:declined:message:reason', [
				$user->name,
				$group->name,
				$reason,
				$group->getURL(),
			]);
		}
		
		$subject = elgg_echo('group_tools:notify:membership:declined:subject', [
			$group->name,
		]);
		
		$params = [
			'object' => $group,
			'action' => 'delete',
		];
		notify_user($user->getGUID(), $group->getGUID(), $subject, $body, $params);
	}
	
	/**
	 * Listen to the group join event
	 *
	 * @param string $event  the name of the event
	 * @param string $type   the type of the event
	 * @param array  $params supplied params
	 *
	 * @return void
	 */
	public static function groupJoin($event, $type, $params) {
		
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		
		if (!($user instanceof \ElggUser) || !($group instanceof \ElggGroup)) {
			return;
		}
		
		// load notification settings
		self::loadAutoNotifications();
		
		if (!empty(self::$AUTO_NOTIFICATIONS)) {
			// subscribe the user to the group
			$NOTIFICATION_HANDLERS = _elgg_services()->notifications->getMethods();
			foreach ($NOTIFICATION_HANDLERS as $method => $dummy) {
				if (!in_array($method, self::$AUTO_NOTIFICATIONS)) {
					continue;
				}
				
				add_entity_relationship($user->getGUID(), "notify{$method}", $group->getGUID());
			}
		}
		
		// cleanup invites
		remove_entity_relationship($group->getGUID(), 'invited', $user->getGUID());
		
		// and requests
		remove_entity_relationship($user->getGUID(), 'membership_request', $group->getGUID());
		
		// cleanup email invitations
		$options = [
			'annotation_name' => 'email_invitation',
			'annotation_value' => group_tools_generate_email_invite_code($group->getGUID(), $user->email),
			'limit' => false,
		];
		
		if (elgg_is_logged_in()) {
			elgg_delete_annotations($options);
		} elseif ($annotations = elgg_get_annotations($options)) {
			group_tools_delete_annotations($annotations);
		}
		
		// welcome message
		$welcome_message = $group->getPrivateSetting('group_tools:welcome_message');
		$check_message = trim(strip_tags($welcome_message));
		if (!empty($check_message)) {
			// replace the place holders
			$welcome_message = str_ireplace('[name]', $user->name, $welcome_message);
			$welcome_message = str_ireplace('[group_name]', $group->name, $welcome_message);
			$welcome_message = str_ireplace('[group_url]', $group->getURL(), $welcome_message);
			
			// subject
			$subject = elgg_echo('group_tools:welcome_message:subject', [$group->name]);
			
			// notify the user
			notify_user($user->getGUID(), $group->getGUID(), $subject, $welcome_message);
		}
	}
	
	/**
	 * Listen to the create member_of_site relationship event to handle new users
	 *
	 * @param string            $event        the name of the event
	 * @param string            $type         the type of the event
	 * @param \ElggRelationship $relationship supplied param
	 *
	 * @return void
	 */
	public static function siteJoin($event, $type, $relationship) {
		
		if (!($relationship instanceof \ElggRelationship) || ($relationship->relationship !== 'member_of_site')) {
			return;
		}
		
		$user_guid = (int) $relationship->guid_one;
		$site_guid = (int) $relationship->guid_two;
		
		$user = get_user($user_guid);
		if (empty($user)) {
			return;
		}
		
		// ignore access
		$ia = elgg_set_ignore_access(true);
		
		// add user to the auto join groups
		$auto_joins = elgg_get_plugin_setting('auto_join', 'group_tools');
		if (!empty($auto_joins)) {
			$auto_joins = string_to_tag_array($auto_joins);
			
			foreach ($auto_joins as $group_guid) {
				$group = get_entity($group_guid);
				if (empty($group) || !($group instanceof \ElggGroup)) {
					continue;
				}
				
				if ($group->site_guid !== $site_guid) {
					continue;
				}
				
				// join the group
				$group->join($user);
			}
		}
		
		// auto detect email invited groups
		$groups = group_tools_get_invited_groups_by_email($user->email, $site_guid);
		if (!empty($groups)) {
			foreach ($groups as $group) {
				// join the group
				$group->join($user);
			}
		}
		
		// check for manual email invited groups
		$group_invitecode = get_input('group_invitecode');
		if (!empty($group_invitecode)) {
			$group = group_tools_check_group_email_invitation($group_invitecode);
			if (!empty($group)) {
				// join the group
				$group->join($user);
				
				// cleanup the invite code
				$group_invitecode = sanitise_string($group_invitecode);
				
				$options = [
					'guid' => $group->getGUID(),
					'annotation_name' => 'email_invitation',
					'wheres' => [
						"(v.string = '{$group_invitecode}' OR v.string LIKE '{$group_invitecode}|%')",
					],
					'annotation_owner_guid' => $group->getGUID(),
					'limit' => 1,
				];
				
				// ignore access in order to cleanup the invitation
				$ia2 = elgg_set_ignore_access(true);
				
				elgg_delete_annotations($options);
				
				// restore access
				elgg_set_ignore_access($ia2);
			}
		}
		
		// find domain based groups
		$groups = group_tools_get_domain_based_groups($user, $site_guid);
		if (!empty($groups)) {
			foreach ($groups as $group) {
				// join the group
				$group->join($user);
			}
		}
		
		// restore access settings
		elgg_set_ignore_access($ia);
	}
}
