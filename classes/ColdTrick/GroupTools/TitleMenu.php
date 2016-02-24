<?php

namespace ColdTrick\GroupTools;

class TitleMenu {
	
	/**
	 * Change the name/function of the group join button
	 *
	 * @param string          $hook         the name of the hook
	 * @param string          $type         the type of the hook
	 * @param \ElggMenuItem[] $return_value current return value
	 * @param array           $params       supplied params
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function groupMembership($hook, $type, $return_value, $params) {
		
		if (!elgg_in_context('groups')) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		$user = elgg_get_logged_in_user_entity();
		
		if (!($page_owner instanceof \ElggGroup) || !($user instanceof \ElggUser)) {
			return;
		}
		
		if (empty($return_value) || !is_array($return_value)) {
			return;
		}
		
		foreach ($return_value as $menu_item) {
			// group join button?
			if ($menu_item->getName() !== 'groups:joinrequest') {
				continue;
			}
			
			if (check_entity_relationship($user->getGUID(), 'membership_request', $page_owner->getGUID())) {
				// user already requested to join this group
				$menu_item->setText(elgg_echo('group_tools:joinrequest:already'));
				$menu_item->setTooltip(elgg_echo('group_tools:joinrequest:already:tooltip'));
				$menu_item->setHref("action/groups/killrequest?user_guid={$user->getGUID()}&group_guid={$page_owner->getGUID()}");
				$menu_item->is_action = true;
				
			} elseif (check_entity_relationship($page_owner->getGUID(), 'invited', $user->getGUID())) {
				// the user was invited, so let him/her join
				$menu_item->setName('groups:join');
				$menu_item->setText(elgg_echo('groups:join'));
				$menu_item->setTooltip(elgg_echo('group_tools:join:already:tooltip'));
				$menu_item->setHref("action/groups/join?user_guid={$user->getGUID()}&group_guid={$page_owner->getGUID()}");
				$menu_item->is_action = true;
				
			} elseif (group_tools_check_domain_based_group($page_owner, $user)) {
				// user has a matching email domain
				$menu_item->setName('groups:join');
				$menu_item->setText(elgg_echo('groups:join'));
				$menu_item->setTooltip(elgg_echo('group_tools:join:domain_based:tooltip'));
				$menu_item->setHref("action/groups/join?user_guid={$user->getGUID()}&group_guid={$page_owner->getGUID()}");
				$menu_item->is_action = true;
				
			}
			
			break;
		}

		return $return_value;
	}
	
	/**
	 * Change the text of the group invite button, and maybe add it for group members
	 *
	 * @param string          $hook         the name of the hook
	 * @param string          $type         the type of the hook
	 * @param \ElggMenuItem[] $return_value current return value
	 * @param array           $params       supplied params
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function groupInvite($hook, $type, $return_value, $params) {
		
		if (!elgg_in_context('groups')) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		$user = elgg_get_logged_in_user_entity();
		
		if (!($page_owner instanceof \ElggGroup) || !($user instanceof \ElggUser)) {
			return;
		}
		
		if (empty($return_value) || !is_array($return_value)) {
			return;
		}
		
		// change invite menu text
		$invite_found = false;
		foreach ($return_value as $menu_item) {
			
			if ($menu_item->getName() !== 'groups:invite') {
				continue;
			}
			
			$invite_found = true;
			
			$invite = elgg_get_plugin_setting('invite', 'group_tools');
			$invite_email = elgg_get_plugin_setting('invite_email', 'group_tools');
			$invite_csv = elgg_get_plugin_setting('invite_csv', 'group_tools');
			
			if (in_array('yes', [$invite, $invite_csv, $invite_email])) {
				$menu_item->setText(elgg_echo('group_tools:groups:invite'));
			}
		}
		
		// maybe allow normal users to invite new members
		if (!elgg_in_context('group_profile') || $invite_found) {
			return $return_value;
		}
		
		// this is only allowed for group members
		if (!$page_owner->isMember($user)) {
			return $return_value;
		}
		
		// we're on a group profile page, but haven't found the invite button yet
		// so check if it should be here
		$setting = elgg_get_plugin_setting('invite_members', 'group_tools');
		if (!in_array($setting, ['yes_off', 'yes_on'])) {
			return $return_value;
		}
		
		// check group settings
		$invite_members = $page_owner->invite_members;
		if (empty($invite_members)) {
			$invite_members = 'no';
			if ($setting == 'yes_on') {
				$invite_members = 'yes';
			}
		}
		
		if ($invite_members !== 'yes') {
			return $return_value;
		}
		
		// normal users are allowed to invite users
		$invite = elgg_get_plugin_setting('invite', 'group_tools');
		$invite_email = elgg_get_plugin_setting('invite_email', 'group_tools');
		$invite_csv = elgg_get_plugin_setting('invite_csv', 'group_tools');
		
		if (in_array('yes', [$invite, $invite_csv, $invite_email])) {
			$text = elgg_echo('group_tools:groups:invite');
		} else {
			$text = elgg_echo('groups:invite');
		}
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'groups:invite',
			'href' => "groups/invite/{$page_owner->getGUID()}",
			'text' => $text,
			'link_class' => 'elgg-button elgg-button-action',
		]);
		
		return $return_value;
	}
	
	/**
	 *
	 * @param string          $hook         the name of the hook
	 * @param string          $type         the type of the hook
	 * @param \ElggMenuItem[] $return_value current return value
	 * @param array           $params       supplied params
	 *
	 * @return void|\ElggMenuItem[]
	 */
	public static function exportGroupMembers($hook, $type, $return_value, $params) {
		
		if (!elgg_in_context('groups')) {
			return;
		}
		
		$page_owner = elgg_get_page_owner_entity();
		if (!($page_owner instanceof \ElggGroup)) {
			return;
		}
		
		if (!is_array($return_value)) {
			return;
		}
		
		// group member export
		$group_members_page = elgg_normalize_url("groups/members/{$page_owner->getGUID()}");
		if (strpos(current_page_url(), $group_members_page) === false) {
			return;
		}
		
		if (!$page_owner->canEdit() || (elgg_get_plugin_setting('member_export', 'group_tools') !== 'yes')) {
			return;
		}
		
		$return_value[] = \ElggMenuItem::factory([
			'name' => 'member_export',
			'text' => elgg_echo('group_tools:member_export:title_button'),
			'href' => "action/group_tools/member_export?group_guid={$page_owner->getGUID()}",
			'is_action' => true,
			'link_class' => 'elgg-button elgg-button-action',
		]);
		
		return $return_value;
	}
}
