<?php
/**
 * Start file for this plugin
 */

// define for default group access
define('GROUP_TOOLS_GROUP_ACCESS_DEFAULT', -10);

require_once(dirname(__FILE__) . '/lib/functions.php');

// default elgg event handlers
elgg_register_event_handler('init', 'system', 'group_tools_init');
elgg_register_event_handler('ready', 'system', 'group_tools_ready');
elgg_register_event_handler('pagesetup', 'system', 'group_tools_pagesetup', 550);

/**
 * called when the Elgg system get initialized
 *
 * @return void
 */
function group_tools_init() {
	
	// extend css & js
	elgg_extend_view('css/elgg', 'css/group_tools/site.css');
	elgg_extend_view('js/elgg', 'js/group_tools/site.js');
	
	// extend page handlers
	elgg_register_plugin_hook_handler('route', 'groups', '\ColdTrick\GroupTools\Router::groups');
	elgg_register_plugin_hook_handler('route', 'livesearch', '\ColdTrick\GroupTools\Router::livesearch');
	
	elgg_register_page_handler('groupicon', '\ColdTrick\GroupTools\GroupIcon::pageHandler');
	elgg_register_plugin_hook_handler('entity:icon:url', 'group', '\ColdTrick\GroupTools\GroupIcon::getIconURL');
	
	// hook on title menu
	elgg_register_plugin_hook_handler('register', 'menu:title', '\ColdTrick\GroupTools\TitleMenu::groupMembership');
	elgg_register_plugin_hook_handler('register', 'menu:title', '\ColdTrick\GroupTools\TitleMenu::groupInvite');
	elgg_register_plugin_hook_handler('register', 'menu:title', '\ColdTrick\GroupTools\TitleMenu::exportGroupMembers');
	elgg_register_plugin_hook_handler('register', 'menu:user_hover', '\ColdTrick\GroupTools\GroupAdmins::assignGroupAdmin');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\GroupTools\GroupAdmins::assignGroupAdmin');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\GroupTools\EntityMenu::relatedGroup');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\GroupTools\EntityMenu::showMemberCount');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\GroupTools\EntityMenu::discussionStatus');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\GroupTools\EntityMenu::showGroupHiddenIndicator');
	elgg_register_plugin_hook_handler('register', 'menu:entity', '\ColdTrick\GroupTools\EntityMenu::removeUserFromGroup');
	elgg_register_plugin_hook_handler('register', 'menu:filter', '\ColdTrick\GroupTools\Membership::filterMenu');
	
	// group admins
	if (group_tools_multiple_admin_enabled()) {
		// add group tool option
		add_group_tool_option('group_multiple_admin_allow', elgg_echo('group_tools:multiple_admin:group_tool_option'), false);
	}
	
	// notify admin on membership request
	elgg_register_event_handler('create', 'relationship', '\ColdTrick\GroupTools\GroupAdmins::membershipRequest');
	// register on group leave
	elgg_register_event_handler('leave', 'group', '\ColdTrick\GroupTools\GroupAdmins::groupLeave');
	// register permissions check hook
	elgg_register_plugin_hook_handler('permissions_check', 'group', '\ColdTrick\GroupTools\GroupAdmins::permissionsCheck');
	
	// register group activity widget
	// 2012-05-03: restored limited functionality of group activity widget, will be fully restored if Elgg fixes widget settings
	elgg_register_widget_type('group_river_widget', elgg_echo('widgets:group_river_widget:title'), elgg_echo('widgets:group_river_widget:description'), ['dashboard', 'profile', 'index', 'groups'], true);
	
	// register group members widget
	elgg_register_widget_type('group_members', elgg_echo('widgets:group_members:title'), elgg_echo('widgets:group_members:description'), ['groups'], false);
	
	// register groups invitations widget
	elgg_register_widget_type('group_invitations', elgg_echo('widgets:group_invitations:title'), elgg_echo('widgets:group_invitations:description'), ['index', 'dashboard'], false);
	
	// register featured groups widget
	elgg_register_widget_type('featured_groups', elgg_echo('groups:featured'), elgg_echo('widgets:featured_groups:description'), ['index']);
	
	// register index groups widget
	elgg_register_widget_type('index_groups', elgg_echo('groups'), elgg_echo('widgets:index_groups:description'), ['index'], true);
	
	// quick start discussion
	elgg_register_widget_type('start_discussion', elgg_echo('group_tools:widgets:start_discussion:title'), elgg_echo('group_tools:widgets:start_discussion:description'), ['index', 'dashboard', 'groups']);
	
	// group invitation
	elgg_register_action('groups/invite', dirname(__FILE__) . '/actions/groups/invite.php');
	
	// manage auto join for groups
	elgg_extend_view('groups/edit', 'group_tools/forms/special_states', 350);
	elgg_register_event_handler('create', 'relationship', '\ColdTrick\GroupTools\Membership::siteJoin');
	
	// show group edit as tabbed
	elgg_extend_view('groups/edit', 'group_tools/group_edit_tabbed', 1);
	
	// cleanup group side menu
	elgg_extend_view('groups/edit', 'group_tools/forms/cleanup', 450);
	
	// group notifications
	elgg_extend_view('groups/edit', 'group_tools/forms/notifications', 375);
	
	// allow group members to invite new members
	elgg_extend_view('groups/edit', 'group_tools/forms/invite_members', 475);
	
	// configure a group welcome message
	elgg_extend_view('groups/edit', 'group_tools/forms/welcome_message');
	
	// configure domain based group join
	elgg_extend_view('groups/edit', 'group_tools/forms/domain_based');
	
	// show group status in owner block
	elgg_extend_view('page/elements/owner_block/extend', 'group_tools/owner_block');
	// show group status in stats (on group profile)
	elgg_extend_view('groups/profile/summary', 'group_tools/group_stats');
	
	if (elgg_is_active_plugin('blog')) {
		elgg_register_widget_type('group_news', elgg_echo('widgets:group_news:title'), elgg_echo('widgets:group_news:description'), ['profile', 'index', 'dashboard'], true);
		elgg_extend_view('css/elgg', 'css/group_tools/group_news.css');
	}
	
	// related groups
	add_group_tool_option('related_groups', elgg_echo('groups_tools:related_groups:tool_option'), false);
	elgg_extend_view('groups/tool_latest', 'group_tools/modules/related_groups');
	elgg_register_widget_type('group_related', elgg_echo('groups_tools:related_groups:widget:title'), elgg_echo('groups_tools:related_groups:widget:description'), ['groups']);
	
	// registration
	elgg_extend_view('register/extend', 'group_tools/register_extend');
	
	// theme sandbox
	elgg_extend_view('theme_sandbox/forms', 'group_tools/theme_sandbox/grouppicker');
	
	// register index widget to show latest discussions
	elgg_register_widget_type('discussion', elgg_echo('discussion:latest'), elgg_echo('widgets:discussion:description'), ['index', 'dashboard'], true);
	elgg_register_widget_type('group_forum_topics', elgg_echo('discussion:group'), elgg_echo('widgets:group_forum_topics:description'), ['groups']);
	
	// register events
	elgg_register_event_handler('join', 'group', '\ColdTrick\GroupTools\Membership::groupJoin');
	elgg_register_event_handler('delete', 'relationship', 'ColdTrick\GroupTools\Membership::deleteRequest');
	elgg_register_event_handler('upgrade', 'system', '\ColdTrick\GroupTools\Upgrade::setGroupMailClassHandler');
	
	// group mail option
	elgg_register_plugin_hook_handler('register', 'menu:page', '\ColdTrick\GroupTools\GroupMail::pageMenu');
	elgg_register_notification_event('object', GroupMail::SUBTYPE, ['enqueue']);
	elgg_register_plugin_hook_handler('prepare', 'notification:enqueue:object:' . GroupMail::SUBTYPE, '\ColdTrick\GroupTools\GroupMail::prepareNotification');
	elgg_register_plugin_hook_handler('get', 'subscriptions', '\ColdTrick\GroupTools\GroupMail::getSubscribers');
	elgg_register_plugin_hook_handler('send:after', 'notifications', '\ColdTrick\GroupTools\GroupMail::cleanup');
	
	// register plugin hooks
	elgg_register_plugin_hook_handler('entity:url', 'object', '\ColdTrick\GroupTools\WidgetManager::widgetURL');
	elgg_register_plugin_hook_handler('default', 'access', '\ColdTrick\GroupTools\Access::setGroupDefaultAccess');
	elgg_register_plugin_hook_handler('default', 'access', '\ColdTrick\GroupTools\Access::validateGroupDefaultAccess', 999999);
	elgg_register_plugin_hook_handler('access:collections:write', 'user', '\ColdTrick\GroupTools\Access::defaultAccessOptions');
	elgg_register_plugin_hook_handler('action', 'groups/join', '\ColdTrick\GroupTools\Membership::groupJoinAction');
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', '\ColdTrick\GroupTools\OwnerBlockMenu::relatedGroups');
	elgg_register_plugin_hook_handler('route', 'register', '\ColdTrick\GroupTools\Router::allowRegistration');
	elgg_register_plugin_hook_handler('action', 'register', '\ColdTrick\GroupTools\Router::allowRegistration');
	elgg_register_plugin_hook_handler('group_tool_widgets', 'widget_manager', '\ColdTrick\GroupTools\WidgetManager::groupToolWidgets');
	
	// actions
	elgg_register_action('group_tools/toggle_admin', dirname(__FILE__) . '/actions/toggle_admin.php');
	elgg_register_action('group_tools/mail', dirname(__FILE__) . '/actions/mail.php');
	elgg_register_action('group_tools/cleanup', dirname(__FILE__) . '/actions/cleanup.php');
	elgg_register_action('group_tools/invite_members', dirname(__FILE__) . '/actions/invite_members.php');
	elgg_register_action('group_tools/welcome_message', dirname(__FILE__) . '/actions/welcome_message.php');
	elgg_register_action('group_tools/domain_based', dirname(__FILE__) . '/actions/domain_based.php');
	elgg_register_action('group_tools/related_groups', dirname(__FILE__) . '/actions/related_groups.php');
	elgg_register_action('group_tools/remove_related_groups', dirname(__FILE__) . '/actions/remove_related_groups.php');
	elgg_register_action('group_tools/member_export', dirname(__FILE__) . '/actions/member_export.php');
	
	elgg_register_action('group_tools/toggle_special_state', dirname(__FILE__) . '/actions/admin/toggle_special_state.php', 'admin');
	elgg_register_action('group_tools/fix_auto_join', dirname(__FILE__) . '/actions/admin/fix_auto_join.php', 'admin');
	elgg_register_action('group_tools/notifications', dirname(__FILE__) . '/actions/admin/notifications.php', 'admin');
	elgg_register_action('group_tools/fix_acl', dirname(__FILE__) . '/actions/admin/fix_acl.php', 'admin');
	elgg_register_action('group_tools/group_tool_presets', dirname(__FILE__) . '/actions/admin/group_tool_presets.php', 'admin');
	elgg_register_action('group_tools/admin/bulk_delete', dirname(__FILE__) . '/actions/admin/bulk_delete.php', 'admin');
	
	elgg_register_action('groups/email_invitation', dirname(__FILE__) . '/actions/groups/email_invitation.php');
	elgg_register_action('groups/decline_email_invitation', dirname(__FILE__) . '/actions/groups/decline_email_invitation.php');
	elgg_register_action('group_tools/revoke_email_invitation', dirname(__FILE__) . '/actions/groups/revoke_email_invitation.php');
	elgg_register_action('groups/edit', dirname(__FILE__) . '/actions/groups/edit.php');
	
	elgg_register_action('group_tools/order_groups', dirname(__FILE__) . '/actions/order_groups.php', 'admin');
	
	elgg_register_action('discussion/toggle_status', dirname(__FILE__) . '/actions/discussion/toggle_status.php');
}

/**
 * called when the system is ready
 *
 * @return void
 */
function group_tools_ready() {
	// unregister dashboard widget group_activity
	elgg_unregister_widget_type('group_activity');
}

/**
 * called just before a page starts with output
 *
 * @return void
 */
function group_tools_pagesetup() {
	
	$user = elgg_get_logged_in_user_entity();
	$page_owner = elgg_get_page_owner_entity();
	
	// admin menu item
	elgg_register_admin_menu_item('configure', 'group_tool_presets', 'appearance');
	elgg_register_admin_menu_item('administer', 'group_bulk_delete', 'administer_utilities');
	
	if (elgg_in_context('groups') && ($page_owner instanceof ElggGroup)) {
		if ($page_owner->forum_enable == 'no') {
			// unset if not enabled for this plugin
			elgg_unregister_widget_type('group_forum_topics');
		}
		
		if (!empty($user)) {
			// check multiple admin
			if (elgg_get_plugin_setting('multiple_admin', 'group_tools') == 'yes') {
				// extend group members sidebar list
				elgg_extend_view('groups/sidebar/members', 'group_tools/group_admins', 400);
				
				// remove group tool options for group admins
				if (($page_owner->getOwnerGUID() != $user->getGUID()) && !$user->isAdmin()) {
					remove_group_tool_option('group_multiple_admin_allow');
				}
			}
			
			// invitation management
			if ($page_owner->canEdit()) {
				$request_options = [
					'type' => 'user',
					'relationship' => 'membership_request',
					'relationship_guid' => $page_owner->getGUID(),
					'inverse_relationship' => true,
					'count' => true
				];
				
				$requests = elgg_get_entities_from_relationship($request_options);
				
				$postfix = '';
				if (!empty($requests)) {
					$postfix = ' [' . $requests . ']';
				}
				
				if (!$page_owner->isPublicMembership()) {
					elgg_register_menu_item('page', [
						'name' => 'membership_requests',
						'text' => elgg_echo('groups:membershiprequests') . $postfix,
						'href' => "groups/requests/{$page_owner->getGUID()}",
					]);
				} else {
					elgg_register_menu_item('page', [
						'name' => 'membership_requests',
						'text' => elgg_echo('group_tools:menu:invitations') . $postfix,
						'href' => "groups/requests/{$page_owner->getGUID()}",
					]);
				}
			}
		}
	}
	
	if ($page_owner instanceof ElggGroup) {
		if (!$page_owner->isPublicMembership()) {
			if (elgg_get_plugin_setting('search_index', 'group_tools') != 'yes') {
				// closed groups should be indexed by search engines
				elgg_extend_view('page/elements/head', 'metatags/noindex');
			}
		}
		
		// cleanup sidebar
		elgg_extend_view('page/elements/sidebar', 'group_tools/sidebar/cleanup');
	}
}
