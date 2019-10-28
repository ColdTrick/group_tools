<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

class Annotation {

	/**
	 * Add menu items to the email invitation annotation menu
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:annotation'
	 *
	 * @return void|MenuItems
	 */
	public static function registerEmailInvitation(\Elgg\Hook $hook) {
		
		$annotation = $hook->getParam('annotation');
		if (!$annotation instanceof \ElggAnnotation || $annotation->name !== 'email_invitation') {
			return;
		}
		
		$group = $annotation->getOwnerEntity();
		if (!$group instanceof \ElggGroup || !$group->canEdit()) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		
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
}
