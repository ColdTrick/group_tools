<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the annotation menu
 */
class Annotation {

	/**
	 * Add menu items to the email invitation annotation menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:annotation'
	 *
	 * @return null|MenuItems
	 */
	public static function registerEmailInvitation(\Elgg\Event $event): ?MenuItems {
		$annotation = $event->getParam('annotation');
		if (!$annotation instanceof \ElggAnnotation || $annotation->name !== 'email_invitation') {
			return null;
		}
		
		$group = $annotation->getOwnerEntity();
		if (!$group instanceof \ElggGroup || !$group->canEdit()) {
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$result->remove('delete');
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'revoke',
			'text' => elgg_echo('revoke'),
			'href' => elgg_generate_action_url('group_tools/revoke_email_invitation', [
				'annotation_id' => $annotation->id,
				'group_guid' => $group->guid,
			]),
			'confirm' => elgg_echo('group_tools:groups:membershipreq:invitations:revoke:confirm'),
			'link_class' => 'elgg-button elgg-button-delete',
		]);
		
		return $result;
	}
	
	/**
	 * Add menu items to the email invitation annotation menu
	 *
	 * @param \Elgg\Event $event 'register', 'menu:annotation'
	 *
	 * @return null|MenuItems
	 */
	public static function registerEmailInvitationUser(\Elgg\Event $event): ?MenuItems {
		$annotation = $event->getParam('annotation');
		if (!$annotation instanceof \ElggAnnotation || $annotation->name !== 'email_invitation') {
			return null;
		}
		
		$user = elgg_get_logged_in_user_entity();
		if (!$user instanceof \ElggUser) {
			return null;
		}
		
		list($secret, $email) = explode('|', $annotation->value);
		
		if ($email !== $user->email) {
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'accept',
			'text' => elgg_echo('accept'),
			'href' => elgg_generate_action_url('groups/email_invitation', [
				'invitecode' => $secret,
			]),
			'link_class' => 'elgg-button elgg-button-submit',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'decline',
			'text' => elgg_echo('delete'),
			'href' => elgg_generate_action_url('groups/decline_email_invitation', [
				'invitecode' => $secret,
			]),
			'confirm' => elgg_echo('groups:invite:remove:check'),
			'link_class' => 'elgg-button elgg-button-delete',
		]);
		
		return $result;
	}
}
