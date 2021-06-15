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
		if (!$group instanceof \ElggGroup || $event->getAction() !== 'admin_approval') {
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
			'limit' => false,
		]);
		/* @var $user \ElggUser */
		foreach ($batch as $user) {
			if (!(bool) elgg_get_plugin_user_setting('notify_approval', $user->guid, 'group_tools')) {
				// only if the admin wants the notifications
				continue;
			}
			
			$notification_settings = $user->getNotificationSettings();
			if (empty($notification_settings)) {
				continue;
			}
			
			$return[$user->guid] = array_keys(array_filter($notification_settings));
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
