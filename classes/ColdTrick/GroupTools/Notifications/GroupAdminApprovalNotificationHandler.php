<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\NotificationEventHandler;

/**
 * Notification handler for groups pending admin approval
 */
class GroupAdminApprovalNotificationHandler extends NotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	public function getSubscriptions(): array {
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
			$settings = $user->getNotificationSettings('group_tools_group_approval');
			$settings = array_keys(array_filter($settings));
			if (empty($settings)) {
				continue;
			}
			
			$return[$user->guid] = $settings;
		}
		
		return $return;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$group = $this->getGroup();
		
		return elgg_echo('group_tools:group:admin_approve:admin:subject', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$group = $this->getGroup();
		
		return elgg_echo('group_tools:group:admin_approve:admin:summary', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$group = $this->getGroup();
		$actor = $this->event->getActor();
		
		return elgg_echo('group_tools:group:admin_approve:admin:message', [
			$actor->getDisplayName(),
			$group->getDisplayName(),
			$group->getURL(),
			elgg_normalize_url('admin/groups/admin_approval'),
		]);
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
	 * {@inheritdoc}
	 */
	public static function isConfigurableByUser(): bool {
		return false;
	}
}
