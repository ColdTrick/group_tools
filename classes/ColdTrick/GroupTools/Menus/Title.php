<?php

namespace ColdTrick\GroupTools\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the title menu
 */
class Title {
	
	/**
	 * Change the name/function of the group join button
	 *
	 * @param \Elgg\Event $event 'register', 'menu:title'
	 *
	 * @return null|MenuItems
	 */
	public static function groupMembership(\Elgg\Event $event): ?MenuItems {
		if (!elgg_in_context('groups')) {
			return null;
		}
		
		$entity = $event->getEntityParam();
		$user = elgg_get_logged_in_user_entity();
		if (!$entity instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return null;
		}
		
		/* @var $menu_items MenuItems */
		$menu_items = $event->getValue();
		
		$menu_item = $menu_items->get('groups:joinrequest');
		if (!$menu_item instanceof \ElggMenuItem) {
			return null;
		}
		
		if ($user->hasRelationship($entity->guid, 'membership_request')) {
			// user already requested to join this group
			$menu_item->setText(elgg_echo('group_tools:joinrequest:already'));
			$menu_item->setTooltip(elgg_echo('group_tools:joinrequest:already:tooltip'));
			$menu_item->setHref(elgg_generate_action_url('groups/killrequest', [
				'user_guid' => $user->guid,
				'group_guid' => $entity->guid,
			]));
		} elseif ($entity->hasRelationship($user->guid, 'invited')) {
			// the user was invited, so let him/her join
			$menu_item->setName('groups:join');
			$menu_item->setText(elgg_echo('groups:join'));
			$menu_item->setTooltip(elgg_echo('group_tools:join:already:tooltip'));
			$menu_item->setHref(elgg_generate_action_url('groups/join', [
				'user_guid' => $user->guid,
				'group_guid' => $entity->guid,
			]));
		} elseif (group_tools_check_domain_based_group($entity, $user)) {
			// user has a matching email domain
			$menu_item->setName('groups:join');
			$menu_item->setText(elgg_echo('groups:join'));
			$menu_item->setTooltip(elgg_echo('group_tools:join:domain_based:tooltip'));
			$menu_item->setHref(elgg_generate_action_url('groups/join', [
				'user_guid' => $user->guid,
				'group_guid' => $entity->guid,
			]));
		} elseif (group_tools_join_motivation_required($entity)) {
			// a join motivation is required
			$menu_item->setHref("ajax/view/group_tools/forms/motivation?guid={$entity->guid}");
			
			$menu_item->addLinkClass('elgg-lightbox');
			$opts = 'data-colorbox-opts';
			$menu_item->$opts = json_encode([
				'width' => '600px',
			]);
		}

		$menu_items->add($menu_item);
		
		return $menu_items;
	}
	
	/**
	 * Change the text of the group invite button, and maybe add it for group members
	 *
	 * @param \Elgg\Event $event 'register', 'menu:title'
	 *
	 * @return null|MenuItems
	 */
	public static function groupInvite(\Elgg\Event $event): ?MenuItems {
		if (!elgg_in_context('groups')) {
			return null;
		}
		
		$user = elgg_get_logged_in_user_entity();
		if (!$user instanceof \ElggUser) {
			return null;
		}
		
		/* @var $menu_items MenuItems */
		$menu_items = $event->getValue();
		
		$invite_found = false;
		$menu_item = $menu_items->get('groups:invite');
		if ($menu_item instanceof \ElggMenuItem) {
			$invite_found = true;
			
			$invite_friends = elgg_get_plugin_setting('invite_friends', 'group_tools');
			$invite = elgg_get_plugin_setting('invite', 'group_tools');
			$invite_email = elgg_get_plugin_setting('invite_email', 'group_tools');
			$invite_csv = elgg_get_plugin_setting('invite_csv', 'group_tools');
			
			if (in_array('yes', [$invite, $invite_csv, $invite_email])) {
				$menu_item->setText(elgg_echo('group_tools:groups:invite'));
				
				$menu_items->add($menu_item);
			} elseif ($invite_friends === 'no') {
				$menu_items->remove('groups:invite');
			}
		}
		
		// maybe allow normal users to invite new members
		if (!elgg_in_context('group_profile') || $invite_found) {
			return $menu_items;
		}
		
		// this is only allowed for group members
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup || !$entity->isMember($user)) {
			return null;
		}
		
		// we're on a group profile page, but haven't found the invite button yet
		// so check if it should be here
		if (!group_tools_allow_members_invite($entity)) {
			return null;
		}
		
		// normal users are allowed to invite users
		$invite_friends = elgg_get_plugin_setting('invite_friends', 'group_tools');
		$invite = elgg_get_plugin_setting('invite', 'group_tools');
		$invite_email = elgg_get_plugin_setting('invite_email', 'group_tools');
		$invite_csv = elgg_get_plugin_setting('invite_csv', 'group_tools');
		
		if (in_array('yes', [$invite, $invite_csv, $invite_email])) {
			$text = elgg_echo('group_tools:groups:invite');
		} elseif ($invite_friends !== 'no') {
			$text = elgg_echo('groups:invite');
		} else {
			// not allowed
			return null;
		}
		
		$menu_items[] = \ElggMenuItem::factory([
			'name' => 'groups:invite',
			'icon' => 'user-plus',
			'href' => elgg_generate_url('invite:group:group', ['guid' => $entity->guid]),
			'text' => $text,
			'link_class' => 'elgg-button elgg-button-action',
		]);
		
		return $menu_items;
	}
	
	/**
	 * Change the text on the group membership status button
	 *
	 * @param \Elgg\Event $event 'register', 'menu:title'
	 *
	 * @return null|MenuItems
	 */
	public static function groupAdminStatus(\Elgg\Event $event): ?MenuItems {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup || !$entity->canEdit()) {
			return null;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			return null;
		}
		
		$user = elgg_get_logged_in_user_entity();
		
		if ($entity->owner_guid === $user->guid) {
			// owner
			return null;
		} elseif (!$entity->isMember($user)) {
			// not member
			return null;
		}
		
		if (!$user->hasRelationship($entity->guid, 'group_admin')) {
			return null;
		}
		
		/* @var $result MenuItems */
		$result = $event->getValue();
		
		$menu_item = $result->get('group-dropdown');
		if (!$menu_item instanceof \ElggMenuItem) {
			return null;
		}
		
		$menu_item->setText(elgg_echo('group_tools:multiple_admin:status:group_admin'));
		
		$result->add($menu_item);
		
		return $result;
	}
	
	/**
	 * Change title menu buttons for a group pending admin approval
	 *
	 * @param \Elgg\Event $event 'register', 'menu:title'
	 *
	 * @return null|MenuItems
	 */
	public static function pendingApproval(\Elgg\Event $event): ?MenuItems {
		if (!elgg_in_context('group_profile')) {
			return null;
		}
		
		if (elgg_get_plugin_setting('admin_approve', 'group_tools') !== 'yes') {
			return null;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup || $page_owner->access_id !== ACCESS_PRIVATE) {
			return null;
		}
		
		$allowed_items = [
			'edit',
			'delete',
			'trash',
		];
		
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		// cleanup all items
		$items = [];
		foreach ($allowed_items as $name) {
			$item = $return->get($name);
			if (!$item instanceof \ElggMenuItem) {
				continue;
			}
			
			$items[] = $item;
			
			// some items could be in a submenu
			$parent_name = $item->getParentName();
			if (!empty($parent_name)) {
				$items[] = $return->get($parent_name);
			}
		}
		
		$return->fill($items);
		
		if (!elgg_is_admin_logged_in()) {
			return $return;
		}
		
		// add admin actions
		// approve
		$return[] = \ElggMenuItem::factory([
			'name' => 'approve',
			'text' => elgg_echo('approve'),
			'href' => elgg_generate_action_url('group_tools/admin/approve', [
				'guid' => $page_owner->guid,
			]),
			'confirm' => true,
			'class' => 'elgg-button elgg-button-action',
		]);
		
		// decline
		$return[] = \ElggMenuItem::factory([
			'name' => 'decline',
			'text' => elgg_echo('decline'),
			'href' => "#group-tools-admin-approve-decline-{$page_owner->guid}",
			'class' => [
				'elgg-button',
				'elgg-button-delete',
				'elgg-lightbox-inline',
			],
		]);
		
		return $return;
	}
	
	/**
	 * Change title menu buttons for a concept group
	 *
	 * @param \Elgg\Event $event 'register', 'menu:title'
	 *
	 * @return null|MenuItems
	 */
	public static function conceptGroup(\Elgg\Event $event): ?MenuItems {
		if (!elgg_in_context('group_profile')) {
			return null;
		}
		
		if (!(bool) elgg_get_plugin_setting('concept_groups', 'group_tools')) {
			return null;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup || $page_owner->access_id !== ACCESS_PRIVATE || !(bool) $page_owner->is_concept) {
			return null;
		}
		
		$allowed_items = [
			'edit',
			'delete',
			'trash',
			'entity_explorer', // developer tools
			'opensearch_inspect', // OpenSearch
		];
		
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		// cleanup all items
		$items = [];
		foreach ($allowed_items as $name) {
			$item = $return->get($name);
			if (!$item instanceof \ElggMenuItem) {
				continue;
			}
			
			$items[] = $item;
			
			// some items could be in a submenu
			$parent_name = $item->getParentName();
			if (!empty($parent_name)) {
				$items[] = $return->get($parent_name);
			}
		}
		
		$return->fill($items);
		
		if (!$page_owner->canEdit()) {
			return $return;
		}
		
		// add owner action
		// publish
		if (elgg_get_plugin_setting('admin_approve', 'group_tools') === 'yes' && !elgg_is_admin_logged_in()) {
			// ask to be approved
			$text = elgg_echo('group_tools:group:concept:profile:approve');
			$confirm = elgg_echo('group_tools:group:concept:profile:approve:confirm');
		} else {
			$text = elgg_echo('group_tools:group:concept:profile:publish');
			$confirm = elgg_echo('group_tools:group:concept:profile:publish:confirm');
		}
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'remove_concept',
			'text' => $text,
			'href' => elgg_generate_action_url('group_tools/remove_concept_status', [
				'guid' => $page_owner->guid,
			]),
			'confirm' => $confirm,
			'class' => 'elgg-button elgg-button-action',
		]);
		
		return $return;
	}
	
	/**
	 * Add group tool presets to the add button
	 *
	 * @param \Elgg\Event $event 'register', 'menu:title'
	 *
	 * @return null|MenuItems
	 */
	public static function addGroupToolPresets(\Elgg\Event $event): ?MenuItems {
		$user = elgg_get_logged_in_user_entity();
		if (empty($user) || !elgg_in_context('groups')) {
			return null;
		}
		
		if (elgg_get_plugin_setting('create_based_on_preset', 'group_tools') !== 'yes') {
			return null;
		}
		
		/* @var $return MenuItems */
		$return = $event->getValue();
		
		$add_button = $return->get('add');
		if (!$add_button instanceof \ElggMenuItem) {
			return null;
		}
				
		$url = elgg_generate_url('add:group:group', [
			'guid' => $user->guid,
		]);
		
		if ($add_button->getHref() !== $url) {
			// not the group add button
			return null;
		}
	
		$presets = group_tools_get_tool_presets();
		if (empty($presets)) {
			return null;
		}
		
		$add_button->setChildMenuOptions([
			'display' => 'dropdown',
			'data-position' => json_encode([
				'at' => 'right bottom',
				'my' => 'right top',
				'collision' => 'fit fit',
			]),
		]);
		
		$add_button->setHref(false);
		
		foreach ($presets as $index => $preset) {
			$menu_item = \ElggMenuItem::factory([
				'name' => "add:preset:{$index}",
				'text' => elgg_echo('group_tools:menu:title:add:preset', [elgg_extract('title', $preset)]),
				'title' => elgg_extract('description', $preset),
				'href' => elgg_http_add_url_query_elements($url, [
					'group_tools_preset' => elgg_extract('title', $preset),
				]),
				'parent_name' => $add_button->getName(),
			]);
			
			$return->add($menu_item);
		}
		
		return $return;
	}
}
