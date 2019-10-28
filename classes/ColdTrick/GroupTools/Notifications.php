<?php

namespace ColdTrick\GroupTools;

class Notifications {
	
	/**
	 * Get the subscribers for a new group which needs admin approval
	 *
	 * @param \Elgg\Hook $hook 'get', 'subscriptions'
	 *
	 * @return void|array
	 */
	public static function adminApprovalSubs(\Elgg\Hook $hook) {
		
		$event = $hook->getParam('event');
		if (!$event instanceof \Elgg\Notifications\NotificationEvent) {
			return;
		}
		
		$group = $event->getObject();
		if (!$group instanceof \ElggGroup) {
			return;
		}
		
		$action = $event->getAction();
		if ($action !== 'admin_approval') {
			return;
		}
		
		$return = $hook->getValue();
		
		// get all admins
		$batch = elgg_get_entities([
			'type' => 'user',
			'metadata_name_value_pairs' => [
				'name' => 'admin',
				'value' => 'yes',
			],
			'batch' => true,
		]);
		/* @var $user \ElggUser */
		foreach ($batch as $user) {
			$notification_settings = $user->getNotificationSettings();
			if (empty($notification_settings)) {
				continue;
			}
			
			$return[$user->guid] = [];
			foreach ($notification_settings as $method => $active) {
				if (!$active) {
					continue;
				}
				$return[$user->guid][] = $method;
			}
		}
		
		return $return;
	}
	
	/**
	 * Get the subscribers for a new group which needs admin approval
	 *
	 * @param \Elgg\Hook $hook 'prepare', 'notification:admin_approval:group:group'
	 *
	 * @return void|\Elgg\Notifications\Notification
	 */
	public static function prepareAdminApprovalMessage(\Elgg\Hook $hook) {
		
		$return = $hook->getValue();
		if (!$return instanceof \Elgg\Notifications\Notification) {
			return;
		}
		
		$actor = $return->getSender();
		$recipient = $return->getRecipient();
		
		$language = $hook->getParam('language');
		$event = $hook->getParam('event');
		if (!$event instanceof \Elgg\Notifications\NotificationEvent) {
			return;
		}
		
		$group = $event->getObject();
		if (!$group instanceof \ElggGroup) {
			return;
		}
		
		$return->subject = elgg_echo('group_tools:group:admin_approve:admin:subject', [$group->getDisplayName()], $language);
		$return->summary = elgg_echo('group_tools:group:admin_approve:admin:summary', [$group->getDisplayName()], $language);
		$return->body = elgg_echo('group_tools:group:admin_approve:admin:message', [
			$recipient->getDisplayName(),
			$actor->getDisplayName(),
			$group->getDisplayName(),
			$group->getURL(),
			elgg_normalize_url('admin/groups/admin_approval'),
		], $language);
		
		return $return;
	}
}
