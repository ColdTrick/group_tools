<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Send a notification that the processing of the group invites failed
 */
class GroupInviteFailureHandler extends InstantNotificationEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationMethods(): array {
		return ['email'];
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSubject(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		
		return elgg_echo('group_tools:notification:invite_failure:subject', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		
		return elgg_echo('group_tools:notification:invite_failure:summary', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		
		return elgg_echo('group_tools:notification:invite_failure:body', [
			$group->getDisplayName(),
			$group->getURL(),
		]);
	}
}
