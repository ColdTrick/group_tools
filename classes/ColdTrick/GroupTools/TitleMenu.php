<?php

namespace ColdTrick\GroupTools;

use Elgg\Menu\MenuItems;

class TitleMenu {
	
	/**
	 * Change the name/function of the group join button
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:title'
	 *
	 * @return void|MenuItems
	 */
	public static function groupMembership(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('groups')) {
			return;
		}
		
		$entity = $hook->getEntityParam();
		$user = elgg_get_logged_in_user_entity();
		
		if (!$entity instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return;
		}
		
		/* @var $menu_items MenuItems */
		$menu_items = $hook->getValue();
		
		$menu_item = $menu_items->get('groups:joinrequest');
		if (!$menu_item instanceof \ElggMenuItem) {
			return;
		}
		
		if (check_entity_relationship($user->guid, 'membership_request', $entity->guid)) {
			// user already requested to join this group
			$menu_item->setText(elgg_echo('group_tools:joinrequest:already'));
			$menu_item->setTooltip(elgg_echo('group_tools:joinrequest:already:tooltip'));
			$menu_item->setHref(elgg_generate_action_url('groups/killrequest', [
				'user_guid' => $user->guid,
				'group_guid' => $entity->guid,
			]));
		} elseif (check_entity_relationship($entity->guid, 'invited', $user->guid)) {
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
	 * @param \Elgg\Hook $hook 'register', 'menu:title'
	 *
	 * @return void|MenuItems
	 */
	public static function groupInvite(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('groups')) {
			return;
		}
		
		$entity = $hook->getEntityParam();
		$user = elgg_get_logged_in_user_entity();
		
		if (!$entity instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return;
		}
		
		/* @var $menu_items MenuItems */
		$menu_items = $hook->getValue();
		
		$invite_found = false;
		$menu_item = $menu_items->get('groups:invite');
		if ($menu_item instanceof \ElggMenuItem) {
			$invite_found = true;
			
			$invite_friends = elgg_get_plugin_setting('invite_friends', 'group_tools', 'yes');
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
		if (!$entity->isMember($user)) {
			return;
		}
		
		// we're on a group profile page, but haven't found the invite button yet
		// so check if it should be here
		$setting = elgg_get_plugin_setting('invite_members', 'group_tools');
		if (!in_array($setting, ['yes_off', 'yes_on'])) {
			return;
		}
		
		// check group settings
		$invite_members = $entity->invite_members;
		if (empty($invite_members)) {
			$invite_members = 'no';
			if ($setting == 'yes_on') {
				$invite_members = 'yes';
			}
		}
		
		if ($invite_members !== 'yes') {
			return;
		}
		
		// normal users are allowed to invite users
		$invite_friends = elgg_get_plugin_setting('invite_friends', 'group_tools', 'yes');
		$invite = elgg_get_plugin_setting('invite', 'group_tools');
		$invite_email = elgg_get_plugin_setting('invite_email', 'group_tools');
		$invite_csv = elgg_get_plugin_setting('invite_csv', 'group_tools');
		
		if (in_array('yes', [$invite, $invite_csv, $invite_email])) {
			$text = elgg_echo('group_tools:groups:invite');
		} elseif ($invite_friends !== 'no') {
			$text = elgg_echo('groups:invite');
		} else {
			// not allowed
			return;
		}
		
		$menu_items[] = \ElggMenuItem::factory([
			'name' => 'groups:invite',
			'href' => elgg_generate_url('invite:group:group', ['guid' => $entity->guid]),
			'text' => $text,
			'link_class' => 'elgg-button elgg-button-action',
		]);
		
		return $menu_items;
	}
	
	/**
	 * Change the text on the group membership status button
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:title'
	 *
	 * @return void|MenuItems
	 */
	public static function groupAdminStatus(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup || !$entity->canEdit()) {
			return;
		}
		
		if (!group_tools_multiple_admin_enabled()) {
			return;
		}
		
		$user = elgg_get_logged_in_user_entity();
		
		if ($entity->owner_guid === $user->guid) {
			// owner
			return;
		} elseif (!$entity->isMember($user)) {
			// not member
			return;
		}
		
		if (!check_entity_relationship($user->guid, 'group_admin', $entity->guid)) {
			return;
		}
		
		/* @var $result MenuItems */
		$result = $hook->getValue();
		
		$menu_item = $result->get('group-dropdown');
		if (!$menu_item instanceof \ElggMenuItem) {
			return;
		}
		
		$menu_item->setText(elgg_echo('group_tools:multiple_admin:status:group_admin'));
		
		$result->add($menu_item);
		
		return $result;
	}
	
	/**
	 * add button to export users
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:title'
	 *
	 * @return void|MenuItems
	 */
	public static function exportGroupMembers(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('groups')) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup) {
			return;
		}
		
		// group member export
		$group_members_page = elgg_generate_url('collection:user:user:group_members', [
			'guid' => $page_owner->guid,
		]);
		if (strpos(current_page_url(), $group_members_page) === false) {
			return;
		}
		
		if (!$page_owner->canEdit() || elgg_get_plugin_setting('member_export', 'group_tools') !== 'yes') {
			return;
		}
		
		$return = $hook->getValue();
		
		$return[] = \ElggMenuItem::factory([
			'name' => 'member_export',
			'icon' => 'download',
			'text' => elgg_echo('group_tools:member_export:title_button'),
			'href' => elgg_generate_action_url('group_tools/member_export', [
				'group_guid' => $page_owner->guid,
			]),
			'link_class' => 'elgg-button elgg-button-action',
		]);
		
		return $return;
	}
	
	
	/**
	 * Change title menu buttons for a group pending admin approval
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:title'
	 *
	 * @return void|MenuItems
	 */
	public static function pendingApproval(\Elgg\Hook $hook) {
		
		if (!elgg_in_context('group_profile')) {
			return;
		}
		
		if (elgg_get_plugin_setting('admin_approve', 'group_tools') !== 'yes') {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!$page_owner instanceof \ElggGroup || $page_owner->access_id !== ACCESS_PRIVATE) {
			return;
		}
		
		$allowed_items = [
			'edit',
		];
		
		/* @var $return MenuItems */
		$return = $hook->getValue();
		
		// cleanup all items
		/* @var $menu_item \ElggMenuItem */
		foreach ($return as $menu_item) {
			$name = $menu_item->getName();
			if (in_array($name, $allowed_items)) {
				continue;
			}
			
			$return->remove($name);
		}
		
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
			'href' => elgg_generate_action_url('group_tools/admin/decline', [
				'guid' => $page_owner->guid,
			]),
			'confirm' => elgg_echo('group_tools:group:admin_approve:decline:confirm'),
			'class' => 'elgg-button elgg-button-delete',
		]);
		
		return $return;
	}
	
	/**
	 * Add group tool presets to the add button
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:title'
	 *
	 * @return void|\Elgg\Menu\MenuItems
	 */
	public static function addGroupToolPresets(\Elgg\Hook $hook) {
		
		$user = elgg_get_logged_in_user_entity();
		if (empty($user) || !elgg_in_context('groups')) {
			return;
		}
		
		if (elgg_get_plugin_setting('create_based_on_preset', 'group_tools') !== 'yes') {
			return;
		}
		
		/* @var $return \Elgg\Menu\MenuItems */
		$return = $hook->getValue();
		
		$add_button = $return->get('add');
		if (!$add_button instanceof \ElggMenuItem) {
			return;
		}
				
		$url = elgg_generate_url('add:group:group', [
			'container_guid' => $user->guid,
		]);
		
		if ($add_button->getHref() !== $url) {
			// not the group add button
			return;
		}
	
		$presets = group_tools_get_tool_presets();
		if (empty($presets)) {
			return;
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
