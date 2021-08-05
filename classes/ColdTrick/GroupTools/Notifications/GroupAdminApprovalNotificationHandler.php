<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\NotificationEventHandler;

class GroupAdminApprovalNotificationHandler extends NotificationEventHandler {
	
	/**
	 * {@inheritDoc}
	 */
	protected function getSubscriptions(): array {
		$return = [];
		
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
	 * {@inheritDoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$group = $this>getGroup();
		
		return elgg_echo('group_tools:group:admin_approve:admin:subject', [$group->getDisplayName()], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$group = $this>getGroup();
		
		return elgg_echo('group_tools:group:admin_approve:admin:summary', [$group->getDisplayName()], $recipient->getLanguage());
	}
	
	/**
	 * {@inheritDoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$group = $this->getGroup();
		$actor = $this->event->getActor();
		
		return elgg_echo('group_tools:group:admin_approve:admin:message', [
			$recipient->getDisplayName(),
			$actor->getDisplayName(),
			$group->getDisplayName(),
			$group->getURL(),
			elgg_normalize_url('admin/groups/admin_approval'),
		], $recipient->getLanguage());
	}
	
	/**
	 * Get the group from the notification
	 *
	 * @return \ElggGroup
	 */
	protected function getGroup(): \ElggGroup {
		return $this->event->getObject();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public static function isConfigurableByUser(): bool {
		return false;
	}
}
