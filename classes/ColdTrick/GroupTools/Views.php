<?php

namespace ColdTrick\GroupTools;

use Elgg\ViewsService;

/**
 * View event handler
 */
class Views {
	
	/**
	 * @var null|array view vars needed for group edit approval reasons
	 */
	protected static $group_edit_approval_reason_view_vars;
	
	/**
	 * Does some view preparation
	 *
	 * @param \Elgg\Event $event 'view_vars', 'resources/groups/all'
	 *
	 * @return null|array
	 */
	public static function prepareGroupAll(\Elgg\Event $event): ?array {
		if (!empty(get_input('sort_by')) || !empty(get_input('sort'))) {
			return null;
		}
		
		$default_filter = elgg_get_plugin_setting('group_listing', 'group_tools');
		if ($default_filter === 'yours' && !elgg_is_logged_in()) {
			$default_filter = 'all';
		}
		
		$filter = get_input('filter', $default_filter);
		
		$vars = $event->getValue();
		
		// support for 'old' tabs
		switch ($filter) {
			case 'popular':
				set_input('sort', 'popular');
				set_input('filter', 'all');
				$vars['filter_sorting_selected'] = 'popular';
				break;
				
			default:
				set_input('filter', $filter);
				$sorting = elgg_get_plugin_setting("group_listing_{$filter}_sorting", 'group_tools', 'newest');
				switch ($sorting) {
					case 'alpha':
						set_input('sort_by', [
							'property_type' => 'metadata',
							'property' => 'name',
							'direction' => 'asc',
						]);
						$vars['filter_sorting_selected'] = 'sort:name:asc';
						break;
						
					case 'newest':
						set_input('sort_by', [
							'property_type' => 'attribute',
							'property' => 'time_created',
							'direction' => 'desc',
						]);
						$vars['filter_sorting_selected'] = 'sort:time_created:desc';
						break;
						
					case 'popular':
						set_input('sort', $sorting);
						$vars['filter_sorting_selected'] = 'popular';
						break;
				}
				break;
		}
		
		return $vars;
	}
	
	/**
	 * Change some inputs when listing users in livesearch for group invites
	 *
	 * @param \Elgg\Event $event 'view_vars', 'page/components/list'
	 *
	 * @return null|array
	 */
	public static function livesearchUserListing(\Elgg\Event $event): ?array {
		$group_guid = (int) get_input('group_guid');
		$group_invite = (bool) get_input('group_invite');
		if ($group_guid < 1 || empty($group_invite)) {
			return null;
		}
		
		$group = get_entity($group_guid);
		if (!$group instanceof \ElggGroup) {
			return null;
		}
		
		$vars = $event->getValue();
		if (elgg_extract('type', $vars) !== 'user') {
			return null;
		}
		
		$vars['item_view'] = 'group_tools/group_invite/user';
		$vars['group'] = $group;
		
		return $vars;
	}
	
	/**
	 * Set correct value to show/hide group owner transfer
	 *
	 * @param \Elgg\Event $event 'view_vars', 'groups/edit/access'
	 *
	 * @return null|array
	 */
	public static function allowGroupOwnerTransfer(\Elgg\Event $event): ?array {
		$vars = $event->getValue();
		
		$group = elgg_extract('entity', $vars);
		$user = elgg_get_logged_in_user_entity();
		if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
			// create a new group
			return null;
		}
		
		$setting = elgg_get_plugin_setting('admin_transfer', 'group_tools');
		switch ($setting) {
			case 'admin':
				$vars['show_group_owner_transfer'] = $user->isAdmin();
				break;
				
			case 'owner':
				$vars['show_group_owner_transfer'] = ($group->owner_guid === $user->guid || $user->isAdmin());
				break;
				
			default:
				$vars['show_group_owner_transfer'] = false;
				break;
		}
		
		return $vars;
	}
	
	/**
	 * Show a simplified version of the group access tab (only during creation)
	 *
	 * @param \Elgg\Event $event 'view_vars', 'groups/edit/access'
	 *
	 * @return null|array
	 */
	public static function showSimplefiedAccess(\Elgg\Event $event): ?array {
		$vars = $event->getValue();
		
		$group = elgg_extract('entity', $vars);
		if ($group instanceof \ElggGroup || elgg_get_plugin_setting('simple_access_tab', 'group_tools') !== 'yes') {
			// edit existing group, or not enabled in the settings
			return null;
		}
		
		if ((bool) elgg_extract('_group_tools_simplified_deadloop', $vars, false)) {
			return null;
		}
		
		$vars[ViewsService::OUTPUT_KEY] = elgg_view('groups/edit/access_simplified', $vars);
		
		return $vars;
	}
	
	/**
	 * Show the join motivation on a membership request
	 *
	 * @param \Elgg\Event $event 'view_vars', 'relationship/membership_request'
	 *
	 * @return null|array
	 */
	public static function addJoinMotivationToGroupMembershipRequest(\Elgg\Event $event): ?array {
		$vars = $event->getValue();
		
		$relationship = elgg_extract('relationship', $vars);
		if (!$relationship instanceof \ElggRelationship || $relationship->relationship !== 'membership_request') {
			return null;
		}
		
		$user = get_entity($relationship->guid_one);
		$group = elgg_call(ELGG_IGNORE_ACCESS, function() use ($relationship) {
			return get_entity($relationship->guid_two);
		});
		if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return null;
		}
		
		$motivations = $group->getAnnotations([
			'annotation_name' => 'join_motivation',
			'annotation_owner_guid' => $user->guid,
			'limit' => 1,
		]);
		if (empty($motivations)) {
			return null;
		}
		
		$vars['content'] = elgg_format_element('b', [], elgg_echo('group_tools:join_motivation:listing'));
		$vars['content'] .= elgg_view('output/longtext', [
			'value' => $motivations[0]->value,
			'class' => 'mtn',
		]);
		
		return $vars;
	}
	
	/**
	 * In order to know which submit button is pressed don't disable the submit buttons on submit
	 *
	 * @param \Elgg\Event $event 'view_vars', 'input/form'
	 *
	 * @return null|array
	 */
	public static function allowDoubleSubmitWhenConceptGroupsEnabled(\Elgg\Event $event): ?array {
		$vars = $event->getValue();
		if (elgg_extract('action_name', $vars) !== 'groups/edit') {
			return null;
		}
		
		if (!(bool) elgg_get_plugin_setting('concept_groups', 'group_tools')) {
			return null;
		}
		
		$vars['prevent_double_submit'] = false;
		
		return $vars;
	}
	
	/**
	 * Prepare to add the group approval reasons to the group create form
	 *
	 * @param \Elgg\Event $event 'view_vars', 'forms/groups/edit'
	 *
	 * @return void
	 */
	public static function prepareGroupApprovalReasons(\Elgg\Event $event): void {
		$vars = $event->getValue();
		
		$entity = elgg_extract('entity', $vars);
		
		$admin_approve = elgg_get_plugin_setting('admin_approve', 'group_tools') === 'yes';
		$admin_approve = $admin_approve && !elgg_is_admin_logged_in();
		$ask_reason = (bool) elgg_get_plugin_setting('creation_reason', 'group_tools');
		if (empty($entity) && $admin_approve && $ask_reason) {
			self::$group_edit_approval_reason_view_vars = $vars;
			
			elgg()->events->registerHandler('view_vars', 'page/component/tabs', self::class . '::addGroupApprovalReasons');
		}
	}
	
	/**
	 * Add the group approval reasons to the group create form
	 *
	 * @param \Elgg\Event $event 'view_vars', 'page/components/tabs'
	 *
	 * @return null|array
	 */
	public static function addGroupApprovalReasons(\Elgg\Event $event): ?array {
		$vars = $event->getValue();
		if (elgg_extract('id', $vars) !== 'groups-edit' || empty(self::$group_edit_approval_reason_view_vars)) {
			return null;
		}
		
		$tabs = elgg_extract('tabs', $vars, []);
		
		$tabs[] = [
			'name' => 'approval_reason',
			'priority' => 110,
			'text' => elgg_echo('groups:edit:reason'),
			'content' => elgg_view('groups/edit/reason', self::$group_edit_approval_reason_view_vars),
		];
		
		$vars['tabs'] = $tabs;
		
		return $vars;
	}
}
