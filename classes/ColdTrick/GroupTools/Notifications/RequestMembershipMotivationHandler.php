<?php

namespace ColdTrick\GroupTools\Notifications;

use Elgg\Groups\Notifications\RequestMembershipEventHandler;

/**
 * Extend the default request membership notification with a motivation of the requester
 */
class RequestMembershipMotivationHandler extends RequestMembershipEventHandler {
	
	/**
	 * {@inheritdoc}
	 */
	protected function getNotificationBody(\ElggUser $recipient, string $method): string {
		$motivation = $this->getJoinMotivation();
		$user = $this->getMembershipUser();
		$group = $this->getMembershipGroup();
		if (!$motivation instanceof \ElggAnnotation) {
			return parent::getNotificationBody($recipient, $method);
		}
		
		return elgg_echo('group_tools:join_motivation:notification:body', [
			$user->getDisplayName(),
			$group->getDisplayName(),
			$motivation->value,
			$user->getURL(),
			$this->getNotificationURL($recipient, $method),
		]);
	}
	
	/**
	 * Get the join motivation
	 *
	 * @return \ElggAnnotation|null
	 */
	protected function getJoinMotivation(): ?\ElggAnnotation {
		$group = $this->getMembershipGroup();
		$user = $this->getMembershipUser();
		if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return null;
		}
		
		$annotations = $group->getAnnotations([
			'name' => 'join_motivation',
			'annotation_owner_guid' => $user->guid,
			'limit' => 1,
		]);
		
		return $annotations ? $annotations[0] : null;
	}
}
