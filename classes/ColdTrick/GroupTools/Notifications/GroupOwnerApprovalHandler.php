<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Notify the group owner that their group is queued for approval
 */
class GroupOwnerApprovalHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSubject($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:owner:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationSummary($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:owner:subject', [$entity->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$entity = $this->getEventEntity();
		if (!$entity instanceof \ElggGroup) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		return elgg_echo('group_tools:group:admin_approve:owner:message', [
			$entity->getDisplayName(),
			$entity->getURL(),
		]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationMethods(): array {
		return ['email'];
	}
}
