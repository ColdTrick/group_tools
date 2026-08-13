<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Notifications\InstantNotificationEventHandler;

/**
 * Send a status notification to the user that the group invitations have been processed
 */
class GroupInviteResultHandler extends InstantNotificationEventHandler {
	
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
		
		return elgg_echo('group_tools:notification:invite_results:subject', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationSummary(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		
		return elgg_echo('group_tools:notification:invite_results:summary', [$group->getDisplayName()]);
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$group = $this->getEventEntity();
		$results = (array) $this->getParam('results');
		$adding = (bool) $this->getParam('adding');
		
		if ($adding) {
			return elgg_echo('group_tools:notification:invite_results:body:adding', [
				$group->getDisplayName(),
				(int) elgg_extract('joined', $results),
				(int) elgg_extract('already_invited', $results),
				(int) elgg_extract('member', $results),
				$group->getURL(),
			]);
		}
		
		return elgg_echo('group_tools:notification:invite_results:body', [
			$group->getDisplayName(),
			(int) elgg_extract('invited', $results),
			(int) elgg_extract('already_invited', $results),
			(int) elgg_extract('member', $results),
			$group->getURL(),
		]);
	}
}
