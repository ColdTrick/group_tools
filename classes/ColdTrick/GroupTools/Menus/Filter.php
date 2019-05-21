<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

class Filter {
	
	/**
	 * Add tabs to the group invitations pages
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:filter:group:invitations'
	 *
	 * @return void|MenuItems
	 */
	public static function registerGroupInvitationTabs(\Elgg\Hook $hook) {
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggUser || !$page_owner->canEdit()) {
			return;
		}
		
		$request_count = elgg_get_entities([
			'type' => 'group',
			'relationship' => 'membership_request',
			'relationship_guid' => $page_owner->guid,
			'count' => true,
		]);
		if (empty($request_count)) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		$filter_value = $hook->getParam('filter_value', 'invitations');
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'invitations',
			'text' => elgg_echo('groups:invitations'),
			'href' => elgg_generate_url('collection:group:group:invitations', [
				'username' => $page_owner->username,
			]),
			'selected' => $filter_value === 'invitations',
		]);
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'requests',
			'text' => elgg_echo('group_tools:group:invitations:request'),
			'href' => elgg_generate_url('collection:group:group:membership_requests', [
				'username' => $page_owner->username,
			]),
			'selected' => $filter_value === 'requests',
		]);
		
		
		return $result;
	}
}
