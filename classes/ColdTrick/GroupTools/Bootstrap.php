<?php

namespace ColdTrick\GroupTools;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		
		// group admins
		if (group_tools_multiple_admin_enabled()) {
			// add group tool option
			elgg()->group_tools->register('group_multiple_admin_allow', [
				'label' => elgg_echo('group_tools:multiple_admin:group_tool_option'),
				'default_on' => false,
			]);
		}
		
		// unregister dashboard widget group_activity, because our version is better ;)
		// @todo is this still needed?
		elgg_unregister_widget_type('group_activity');
		
		// group tools
		if (group_tools_group_mail_members_enabled()) {
			elgg()->group_tools->register('mail_members', [
				'label' => elgg_echo('group_tools:tools:mail_members'),
				'default_on' => false,
			]);
		}
		
		elgg_register_notification_event('group', null, ['admin_approval']);
		elgg_register_notification_event('object', 'group_tools_group_mail', ['enqueue']);

		$this->registerViews();
		$this->registerEvents();
		$this->registerHooks();
	}
	
	protected function registerViews() {
		elgg_extend_view('admin.css', 'css/group_tools/admin.css');
		elgg_extend_view('elgg.css', 'css/group_tools/site.css');
		elgg_extend_view('groups/edit', 'group_tools/group_edit_tabbed', 10);
		elgg_extend_view('groups/edit', 'group_tools/extends/groups/edit/admin_approve', 1);
		elgg_extend_view('groups/edit', 'group_tools/forms/notifications', 375);
		elgg_extend_view('groups/edit', 'group_tools/forms/invite_members', 475);
		elgg_extend_view('groups/edit', 'group_tools/forms/welcome_message');
		elgg_extend_view('groups/edit', 'group_tools/forms/domain_based');
		elgg_extend_view('groups/edit/tools', 'group_tools/extends/groups/edit/tools/group_admins', 400);
		elgg_extend_view('groups/invitationrequests', 'group_tools/invitationrequests/emailinvitations');
		elgg_extend_view('groups/invitationrequests', 'group_tools/invitationrequests/membershiprequests');
		elgg_extend_view('groups/invitationrequests', 'group_tools/invitationrequests/emailinviteform');
		elgg_extend_view('groups/profile/layout', 'group_tools/extends/groups/edit/admin_approve', 1);
		elgg_extend_view('groups/profile/summary', 'group_tools/extends/groups/profile/stale_message');
		elgg_extend_view('groups/sidebar/members', 'group_tools/group_admins', 400);
		elgg_extend_view('js/elgg', 'js/group_tools/site.js');
		elgg_extend_view('page/elements/owner_block/extend', 'group_tools/owner_block');
		elgg_extend_view('register/extend', 'group_tools/register_extend');
		
		elgg_register_ajax_view('forms/group_tools/admin/auto_join/additional');
		elgg_register_ajax_view('forms/group_tools/admin/auto_join/default');
		elgg_register_ajax_view('group_tools/elements/auto_join_match_pattern');
		elgg_register_ajax_view('group_tools/forms/motivation');
	}
	
	protected function registerEvents() {
		$event = $this->elgg()->events;
		
		$event->registerHandler('create', 'relationship', __NAMESPACE__ . '\Membership::siteJoinEmailInvitedGroups');
		$event->registerHandler('create', 'relationship', __NAMESPACE__ . '\Membership::siteJoinGroupInviteCode');
		$event->registerHandler('create', 'relationship', __NAMESPACE__ . '\Membership::siteJoinDomainBasedGroups');
		$event->registerHandler('create', 'relationship', __NAMESPACE__ . '\GroupAdmins::membershipRequest');
		$event->registerHandler('create', 'user', __NAMESPACE__ . '\Membership::autoJoinGroups');
		$event->registerHandler('delete', 'relationship', 'ColdTrick\GroupTools\Membership::deleteRequest');
		$event->registerHandler('join', 'group', __NAMESPACE__ . '\Membership::groupJoin');
		$event->registerHandler('leave', 'group', __NAMESPACE__ . '\GroupAdmins::groupLeave');
		$event->registerHandler('login:after', 'user', __NAMESPACE__ . '\Membership::autoJoinGroupsLogin');
	}
	
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('access:collections:write', 'user', __NAMESPACE__ . '\Access::defaultAccessOptions');
		$hooks->registerHandler('action', 'groups/join', __NAMESPACE__ . '\Membership::groupJoinAction');
		$hooks->registerHandler('action', 'register', __NAMESPACE__ . '\Router::allowRegistration');
		$hooks->registerHandler('action', 'groups/edit', __NAMESPACE__ . '\Group::editActionListener');
		$hooks->registerHandler('cron', 'fiveminute', __NAMESPACE__ . '\Membership::autoJoinGroupsCron');
		$hooks->registerHandler('cron', 'daily', __NAMESPACE__ . '\Cron::notifyStaleGroupOwners');
		$hooks->registerHandler('default', 'access', __NAMESPACE__ . '\Access::setGroupDefaultAccess');
		$hooks->registerHandler('default', 'access', __NAMESPACE__ . '\Access::validateGroupDefaultAccess', 999999);
		$hooks->registerHandler('entity:url', 'object', __NAMESPACE__ . '\WidgetManager::widgetURL');
		$hooks->registerHandler('export_value', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::exportGroupAdminsForGroups');
		$hooks->registerHandler('export_value', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::exportGroupAdminsForUsers');
		$hooks->registerHandler('export_value', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::exportStaleInfo');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\GroupAdmins::addGroupAdminsToMembershipRequest');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Notifications::adminApprovalSubs');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\GroupMail::getSubscribers');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::addGroupAdminsToGroups');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::addGroupAdminsToUsers');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::addStaleInfo');
		$hooks->registerHandler('group_tool_widgets', 'widget_manager', __NAMESPACE__ . '\WidgetManager::groupToolWidgets');
		$hooks->registerHandler('handlers', 'widgets', __NAMESPACE__ . '\Widgets::unregsiterRelatedGroupsWidget');
		$hooks->registerHandler('head', 'page', __NAMESPACE__ . '\PageLayout::noIndexClosedGroups');
		$hooks->registerHandler('parameters', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::enableSorting');
		$hooks->registerHandler('permissions_check', 'group', __NAMESPACE__ . '\GroupAdmins::permissionsCheck');
		$hooks->registerHandler('prepare', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::setSelected', 550);
		$hooks->registerHandler('prepare', 'notification:admin_approval:group:', __NAMESPACE__ . '\Notifications::prepareAdminApprovalMessage');
		$hooks->registerHandler('prepare', 'notification:enqueue:object:group_tools_group_mail', __NAMESPACE__ . '\GroupMail::prepareNotification');
		$hooks->registerHandler('prepare', 'notification:membership_request:group:group', __NAMESPACE__ . '\GroupAdmins::prepareMembershipRequestMessage');
		$hooks->registerHandler('register', 'menu:emailinvitation', __NAMESPACE__ . '\Membership::emailinvitationMenu');
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\GroupAdmins::assignGroupAdmin', 501);
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\EntityMenu::relatedGroup');
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\EntityMenu::suggestedGroup');
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::removeTabs', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::addTabs', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::addSorting', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::cleanupTabs', 900);
		$hooks->registerHandler('register', 'menu:group:email_invitation', __NAMESPACE__ . '\Membership::groupEmailInvitation');
		$hooks->registerHandler('register', 'menu:group:invitation', __NAMESPACE__ . '\Membership::groupInvitation');
		$hooks->registerHandler('register', 'menu:group:membershiprequest', __NAMESPACE__ . '\Membership::groupMembershiprequest');
		$hooks->registerHandler('register', 'menu:group:membershiprequests', __NAMESPACE__ . '\Membership::groupMembershiprequests');
		$hooks->registerHandler('register', 'menu:membershiprequest', __NAMESPACE__ . '\Membership::membershiprequestMenu');
		$hooks->registerHandler('register', 'menu:owner_block', __NAMESPACE__ . '\OwnerBlockMenu::relatedGroups');
		$hooks->registerHandler('register', 'menu:page', __NAMESPACE__ . '\PageMenu::registerAdminItems', 501);
		$hooks->registerHandler('register', 'menu:page', __NAMESPACE__ . '\Membership::groupProfileSidebar');
		$hooks->registerHandler('register', 'menu:page', __NAMESPACE__ . '\GroupMail::pageMenu');
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::groupMembership');
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::groupInvite');
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::exportGroupMembers');
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::pendingApproval', 9999);
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::addGroupToolPresets');
		$hooks->registerHandler('register', 'menu:user_hover', __NAMESPACE__ . '\GroupAdmins::assignGroupAdmin');
		$hooks->registerHandler('route', 'register', __NAMESPACE__ . '\Router::allowRegistration');
		$hooks->registerHandler('send:after', 'notifications', __NAMESPACE__ . '\GroupMail::cleanup');
		$hooks->registerHandler('tool_options', 'group', __NAMESPACE__ . '\Tools::registerRelatedGroups');
		$hooks->registerHandler('view_vars', 'resources/groups/add', __NAMESPACE__ . '\Views::protectGroupAdd');
		$hooks->registerHandler('view_vars', 'resources/groups/all', __NAMESPACE__ . '\Views::prepareGroupAll');
	}
}
