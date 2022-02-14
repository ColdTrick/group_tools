<?php

namespace ColdTrick\GroupTools;

use Elgg\Database\QueryBuilder;

class Membership {
	
	/**
	 * Show notification settings on group join
	 *
	 * @var bool
	 */
	protected static $NOTIFICATIONS_TOGGLE;
	
	/**
	 * Listen to the delete of a membership request
	 *
	 * @param \Elgg\Event $event 'delete', 'relationship'
	 *
	 * @return void
	 */
	public static function deleteRequest(\Elgg\Event $event) {
		
		$relationship = $event->getObject();
		if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'membership_request') {
			// not a membership request
			return;
		}
		
		$group = get_entity($relationship->guid_two);
		$user = get_user($relationship->guid_one);
		if (empty($user) || !$group instanceof \ElggGroup) {
			return;
		}
		
		$logged_in_user = elgg_get_logged_in_user_entity();
		if (!$logged_in_user instanceof \ElggUser) {
			// some backgroup process is cleaning this
			return;
		}
		
		// remove join motivations
		elgg_delete_annotations([
			'guid' => $group->guid,
			'annotation_owner_guid' => $user->guid,
			'annotation_name' => 'join_motivation',
			'limit' => false,
		]);
		
		// notify requesting user about declined request
		$action_pattern = '/action\/groups\/killrequest/i';
		if (!preg_match($action_pattern, current_page_url())) {
			// not in the action, so do nothing
			return;
		}
		
		if ($user->guid === $logged_in_user->guid) {
			// user kills own request
			return;
		}
		
		$reason = get_input('reason');
		if (empty($reason)) {
			$body = elgg_echo('group_tools:notify:membership:declined:message', [
				$group->getDisplayName(),
				$group->getURL(),
			], $user->getLanguage());
		} else {
			$body = elgg_echo('group_tools:notify:membership:declined:message:reason', [
				$group->getDisplayName(),
				$reason,
				$group->getURL(),
			], $user->getLanguage());
		}
		
		$subject = elgg_echo('group_tools:notify:membership:declined:subject', [
			$group->getDisplayName(),
		], $user->getLanguage());
		
		$params = [
			'object' => $group,
			'action' => 'delete',
		];
		notify_user($user->guid, $logged_in_user->guid, $subject, $body, $params);
	}
	
	/**
	 * Listen to the group join event
	 *
	 * @param \Elgg\Event $event 'join', 'group'
	 *
	 * @return void
	 */
	public static function groupJoin(\Elgg\Event $event) {
		
		$params = $event->getObject();
		
		$user = elgg_extract('user', $params);
		$group = elgg_extract('group', $params);
		
		if (!$user instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return;
		}
		
		// allow user to change notification settings
		if ($user->guid !== $group->owner_guid) {
			self::notificationsToggle($user, $group);
		}
		
		// cleanup invites and membershiprequests
		self::cleanupGroupInvites($user, $group);
		
		// welcome message
		self::sendWelcomeMessage($user, $group);
	}
	
	/**
	 * Allow a user to change the group notification settings when joined to a group
	 *
	 * @param \ElggUser  $user  the user joining
	 * @param \ElggGroup $group the group joined
	 *
	 * @return void
	 */
	protected static function notificationsToggle(\ElggUser $user, \ElggGroup $group) {
		static $register_once;
		
		if (!isset(self::$NOTIFICATIONS_TOGGLE)) {
			self::$NOTIFICATIONS_TOGGLE = (elgg_get_plugin_setting('notification_toggle', 'group_tools') === 'yes');
		}
		
		if (!self::$NOTIFICATIONS_TOGGLE) {
			return;
		}
		
		$logged_in_user = elgg_get_logged_in_user_entity();
		if (!empty($logged_in_user) && ($logged_in_user->guid === $user->guid)) {
			// user joined group on own action (join public group, accept invite, etc)
			$notifications_enabled = self::notificationsEnabledForGroup($user, $group);
			
			$link_text = elgg_echo('group_tools:notifications:toggle:site:disabled:link');
			$text_key = 'group_tools:notifications:toggle:site:disabled';
			if ($notifications_enabled) {
				$link_text = elgg_echo('group_tools:notifications:toggle:site:enabled:link');
				$text_key = 'group_tools:notifications:toggle:site:enabled';
			}
			
			$link = elgg_view('output/url', [
				'text' => $link_text,
				'href' => elgg_generate_action_url('group_tools/toggle_notifications', [
					'group_guid' => $group->guid,
				]),
			]);
			
			system_message(elgg_echo($text_key, [$link]));
		} else {
			// user was joined by other means (group admin accepted request, added user, etc)
			if (!empty($register_once)) {
				return;
			}
			
			$register_once = true;
			
			elgg_register_plugin_hook_handler('invite_notification', 'group_tools', self::class . '::notificationAddedGroup');
			elgg_register_plugin_hook_handler('email', 'system', self::class . '::notificationEmail', 400);
		}
	}
	
	/**
	 * Cleanup group invitations and membershiprequests
	 *
	 * @param \ElggUser  $user  the user to cleanup for
	 * @param \ElggGroup $group the group to cleanup on
	 *
	 * @return void
	 */
	protected static function cleanupGroupInvites(\ElggUser $user, \ElggGroup $group) {
		
		// cleanup invites
		remove_entity_relationship($group->guid, 'invited', $user->guid);
		
		// and requests
		remove_entity_relationship($user->guid, 'membership_request', $group->guid);
		
		// cleanup email invitations
		$options = [
			'limit' => false,
			'annotation_owner_guid' => $group->guid,
			'annotation_name_value_pairs' => [
				[
					'name' => 'email_invitation',
					'value' => "%|{$user->email}",
					'operand' => 'LIKE',
					'type' => ELGG_VALUE_STRING,
				],
			],
		];
		
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($options){
			elgg_delete_annotations($options);
		});
		
		// join motivation
		$options = [
			'annotation_name' => 'join_motivation',
			'guid' => $group->guid,
			'annotation_owner_guid' => $user->guid,
			'limit' => false,
		];
		elgg_delete_annotations($options);
	}
	
	/**
	 * Send a welcome message to the new user of the group
	 *
	 * @param \ElggUser  $recipient the new user
	 * @param \ElggGroup $group     the group
	 *
	 * @return void
	 */
	protected static function sendWelcomeMessage(\ElggUser $recipient, \ElggGroup $group) {
		
		// get welcome messgae
		$welcome_message = $group->getPluginSetting('group_tools', 'welcome_message', '');
		$check_message = trim(strip_tags($welcome_message));
		if (empty($check_message)) {
			return;
		}
		
		// replace the place holders
		$welcome_message = str_ireplace('[name]', $recipient->getDisplayName(), $welcome_message);
		$welcome_message = str_ireplace('[group_name]', $group->getDisplayName(), $welcome_message);
		$welcome_message = str_ireplace('[group_url]', $group->getURL(), $welcome_message);
		
		// get notification preferences for this group
		$methods = elgg_get_notification_methods();
		if ($group->hasSubscription($recipient->guid, $methods)) {
			$subscription = elgg_echo('on', [], $recipient->getLanguage());
		} else {
			$subscription = elgg_echo('off', [], $recipient->getLanguage());
		}
		$subscription = elgg_format_element('b', [], $subscription);
		
		$welcome_message .= PHP_EOL . PHP_EOL . elgg_echo('group_tools:welcome_message:notifications', [
			$subscription,
		], $recipient->getLanguage());
		
		// subject
		$subject = elgg_echo('group_tools:welcome_message:subject', [$group->getDisplayName()], $recipient->getLanguage());
		
		// mail params
		$mail_params = [
			'object' => $group,
			'action' => 'welcome',
		];
		
		// notify the user
		notify_user($recipient->guid, $group->guid, $subject, $welcome_message, $mail_params);
	}
	
	/**
	 * Validate that the relationship is a site membership relationship
	 *
	 * @param \ElggRelationship $relationship the relationship to check
	 *
	 * @return bool
	 */
	protected static function validateSiteJoinRelationship($relationship) {
		
		if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'member_of_site') {
			return false;
		}
		
		$user_guid = (int) $relationship->guid_one;
		$user = get_user($user_guid);
		if (empty($user)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Listen to the create user
	 *
	 * @param \Elgg\Event $event 'validate:after', 'user'
	 *
	 * @return void
	 */
	public static function autoJoinGroups(\Elgg\Event $event) {
		
		$user = $event->getObject();
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
			// mark the user to check for auto joins when we have more information
			$user->group_tools_check_auto_joins = true;
		});
	}
	
	/**
	 * Handle the auto join groups for users
	 *
	 * @param \Elgg\Hook $hook 'cron', 'fiveminute'
	 *
	 * @return void
	 */
	public static function autoJoinGroupsCron(\Elgg\Hook $hook) {
		
		$time = (int) $hook->getParam('time', time());
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($time) {
			
			$batch = elgg_get_entities([
				'type' => 'user',
				'limit' => false,
				'batch' => true,
				'batch_inc_offset' => false,
				'metadata_name_value_pairs' => [
					'group_tools_check_auto_joins' => true,
				],
				'created_time_upper' => ($time), // 5 minute delay
			]);
			
			$auto_join = false;
			
			/* @var $user \ElggUser */
			foreach ($batch as $user) {
				// prep helper class
				if (empty($auto_join)) {
					$auto_join = new AutoJoin($user);
				} else {
					$auto_join->setUser($user);
				}
				
				// remove user flag
				unset($user->group_tools_check_auto_joins);
				
				// get groups
				$group_guids = $auto_join->getGroupGUIDs();
				if (empty($group_guids)) {
					continue;
				}
				
				$groups = elgg_get_entities([
					'type' => 'group',
					'guids' => $group_guids,
					'limit' => false,
					'batch' => true,
				]);
				/* @var $group \ElggGroup */
				foreach ($groups as $group) {
					$group->join($user);
				}
			}
		});
	}
	
	/**
	 * Check if a user needs to join auto groups (on login)
	 *
	 * @param \Elgg\Event $event 'login:after', 'user'
	 *
	 * @return void
	 */
	public static function autoJoinGroupsLogin(\Elgg\Event $event) {
		
		$user = $event->getObject();
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		if (!isset($user->group_tools_check_auto_joins)) {
			// user is already proccessed
			return;
		}
		
		elgg_call(ELGG_IGNORE_ACCESS, function () use ($user) {
			// prep helper class
			$auto_join = new AutoJoin($user);
			
			// remove user flag
			unset($user->group_tools_check_auto_joins);
			
			// get groups
			$group_guids = $auto_join->getGroupGUIDs();
			if (empty($group_guids)) {
				return;
			}
			
			$groups = elgg_get_entities([
				'type' => 'group',
				'guids' => $group_guids,
				'limit' => false,
				'batch' => true,
			]);
			
			/* @var $group \ElggGroup */
			foreach ($groups as $group) {
				$group->join($user);
			}
		});
	}
	
	/**
	 * Listen to the create member_of_site relationship event to handle new users
	 *
	 * @param \Elgg\Event $event 'validate:after', 'user'
	 *
	 * @return void
	 */
	public static function createUserEmailInvitedGroups(\Elgg\Event $event) {
		
		$user = $event->getObject();
		if ($user instanceof \ElggUser) {
			return;
		}
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($user) {
			// auto detect email invited groups
			$groups = group_tools_get_invited_groups_by_email($user->email);
			if (empty($groups)) {
				return;
			}
			
			foreach ($groups as $group) {
				// join the group
				$group->join($user);
			}
		});
	}
	
	/**
	 * Listen to the create member_of_site relationship event to handle new users
	 *
	 * @param \Elgg\Event $event 'create', 'user'
	 *
	 * @return void
	 */
	public static function createUserGroupInviteCode(\Elgg\Event $event) {
		
		$user = $event->getObject();
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		// check for manual email invited groups
		$group_invitecode = get_input('group_invitecode');
		if (empty($group_invitecode)) {
			return;
		}
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($user, $group_invitecode) {
			$group = group_tools_check_group_email_invitation($group_invitecode);
			if (empty($group)) {
				return;
			}
			
			// join the group
			$group->join($user);
			
			// cleanup the invite code
			elgg_delete_annotations([
				'guid' => $group->guid,
				'annotation_name' => 'email_invitation',
				'wheres' => [
					function(QueryBuilder $qb, $main_alias) use ($group_invitecode) {
						$ors = [
							$qb->compare("{$main_alias}.value", '=', $group_invitecode, ELGG_VALUE_STRING),
							$qb->compare("{$main_alias}.value", 'like', "{$group_invitecode}|%", ELGG_VALUE_STRING),
						];
						
						return $qb->merge($ors, 'OR');
					},
				],
				'annotation_owner_guid' => $group->guid,
				'limit' => false,
			]);
		});
	}
	
	/**
	 * Listen to the create member_of_site relationship event to handle new users
	 *
	 * @param \Elgg\Event $event 'validate:after', 'user'
	 *
	 * @return void
	 */
	public static function createUserDomainBasedGroups(\Elgg\Event $event) {
		
		$user = $event->getObject();
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		// ignore access
		elgg_call(ELGG_IGNORE_ACCESS, function() use ($user) {
			// find domain based groups
			$groups = group_tools_get_domain_based_groups($user);
			if (empty($groups)) {
				return;
			}
			
			foreach ($groups as $group) {
				// join the group
				$group->join($user);
			}
		});
	}
	
	/**
	 * Register a plugin hook, only during the group/join action
	 *
	 * @param \Elgg\Hook $hook 'action:validate', 'groups/join'
	 *
	 * @return void
	 */
	public static function groupJoinAction(\Elgg\Hook $hook) {
		
		// hacky way around a short comming of Elgg core to allow users to join a group
		if (elgg_get_plugin_setting('domain_based', 'group_tools') !== 'yes') {
			return;
		}
		
		elgg_register_plugin_hook_handler('permissions_check', 'group', self::class . '::groupJoinPermission');
	}
	
	/**
	 * A hook on the ->canEdit() of a group. This is done to allow e-mail domain users to join a group
	 *
	 * Note: this is a very hacky way arround a short comming of Elgg core
	 *
	 * @param \Elgg\Hook $hook 'permissions_check', 'group'
	 *
	 * @return void|true
	 */
	public static function groupJoinPermission(\Elgg\Hook $hook) {
		
		if (!empty($hook->getValue())) {
			// already allowed
			return;
		}
		
		if (elgg_get_plugin_setting('domain_based', 'group_tools') !== 'yes') {
			return;
		}
		
		// domain based groups are enabled, lets check if this user is allowed to join based on that
		$group = $hook->getEntityParam();
		$user = $hook->getUserParam();
		if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return;
		}
		
		if (!group_tools_check_domain_based_group($group, $user)) {
			return;
		}
		
		return true;
	}
	
	/**
	 * Add a link to the notifications page so a user can change the group notification settings
	 *
	 * @param \Elgg\Hook $hook 'invite_notification', 'group_tools'
	 *
	 * @return void|string
	 */
	public static function notificationAddedGroup(\Elgg\Hook $hook) {
		
		$group = $hook->getParam('group');
		$user = $hook->getParam('invitee');
		if (!$user instanceof \ElggUser || !$group instanceof \ElggGroup) {
			return;
		}
		
		$return = $hook->getValue();
		
		$notifications_enabled = self::notificationsEnabledForGroup($user, $group);
		$additional_msg = self::generateEmailNotificationText($user, $notifications_enabled);
		
		$return .= PHP_EOL . PHP_EOL . $additional_msg;
		
		return $return;
	}
	
	/**
	 * Append text to an email notification with a link to the group notification settings
	 *
	 * @param \Elgg\Hook $hook 'email', 'system'
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function notificationEmail(\Elgg\Hook $hook) {
		
		$return = $hook->getValue();
		if (!is_array($return)) {
			// someone already send the email
			return;
		}
		
		$mail_params = elgg_extract('params', $return);
		if (!is_array($mail_params)) {
			return;
		}
		
		$action = elgg_extract('action', $mail_params);
		$group = elgg_extract('object', $mail_params);
		if ($action !== 'add_membership' || !$group instanceof \ElggGroup) {
			return;
		}
		
		$notification = elgg_extract('notification', $mail_params);
		if (!$notification instanceof \Elgg\Notifications\Notification) {
			return;
		}
		
		$user = $notification->getRecipient();
		if (!($user instanceof \ElggUser)) {
			return;
		}
		
		$notifications_enabled = self::notificationsEnabledForGroup($user, $group);
		$additional_msg = self::generateEmailNotificationText($user, $notifications_enabled);
		
		$return['body'] .= PHP_EOL . PHP_EOL . $additional_msg;
		
		return $return;
	}
	
	/**
	 * Check if the user is receiving notifications from the group
	 *
	 * @param \ElggUser  $user  the user to check
	 * @param \ElggGroup $group the group to check for
	 *
	 * @return bool
	 */
	public static function notificationsEnabledForGroup(\ElggUser $user, \ElggGroup $group) {
		
		$subscriptions = elgg_get_subscriptions_for_container($group->guid);
		if (!is_array($subscriptions)) {
			return false;
		}
		
		if (!empty($subscriptions[$user->guid])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Generate text to append to an email notification with a link to the group notification settings
	 *
	 * @param \ElggUser $user    the user to generate for
	 * @param bool      $enabled notifications enabled or not
	 *
	 * @return string
	 */
	protected static function generateEmailNotificationText(\ElggUser $user, bool $enabled) {
		
		$notifications_url = elgg_generate_url('settings:notification:groups', [
			'username' => $user->username,
		]);
		if (empty($notifications_url)) {
			return '';
		}
		
		if ($enabled) {
			return elgg_echo('group_tools:notifications:toggle:email:enabled', [$notifications_url], $user->language);
		}
		
		return elgg_echo('group_tools:notifications:toggle:email:disabled', [$notifications_url], $user->language);
	}
}
