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
		
		if (elgg_is_active_plugin('blog')) {
			elgg_register_widget_type('group_news', elgg_echo('widgets:group_news:title'), elgg_echo('widgets:group_news:description'), ['profile', 'index', 'dashboard'], true);
			elgg_extend_view('css/elgg', 'css/group_tools/group_news.css');
		}
		
		// related groups
		elgg()->group_tools->register('related_groups', [
			'label' => elgg_echo('groups_tools:related_groups:tool_option'),
			'default_on' => false,
		]);
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
		elgg_extend_view('elgg.css', 'css/group_tools/site.css');
		elgg_extend_view('admin.css', 'css/group_tools/admin.css');
		elgg_extend_view('js/elgg', 'js/group_tools/site.js');
		elgg_extend_view('register/extend', 'group_tools/register_extend');
		elgg_extend_view('groups/profile/summary', 'group_tools/extends/groups/profile/stale_message');
		elgg_extend_view('groups/invitationrequests', 'group_tools/invitationrequests/emailinvitations');
		elgg_extend_view('groups/invitationrequests', 'group_tools/invitationrequests/membershiprequests');
		elgg_extend_view('groups/invitationrequests', 'group_tools/invitationrequests/emailinviteform');
		elgg_extend_view('groups/tool_latest', 'group_tools/modules/related_groups');
		elgg_extend_view('groups/edit', 'group_tools/group_edit_tabbed', 10);
		elgg_extend_view('groups/edit', 'group_tools/extends/groups/edit/admin_approve', 1);
		elgg_extend_view('groups/profile/layout', 'group_tools/extends/groups/edit/admin_approve', 1);
		elgg_extend_view('groups/edit', 'group_tools/forms/special_states', 350);
		elgg_extend_view('groups/edit', 'group_tools/forms/notifications', 375);
		elgg_extend_view('groups/edit', 'group_tools/forms/invite_members', 475);
		elgg_extend_view('groups/edit', 'group_tools/forms/welcome_message');
		elgg_extend_view('groups/edit', 'group_tools/forms/domain_based');
		elgg_extend_view('page/elements/owner_block/extend', 'group_tools/owner_block');
		elgg_extend_view('groups/sidebar/members', 'group_tools/group_admins', 400);
		elgg_extend_view('groups/edit/tools', 'group_tools/extends/groups/edit/tools/group_admins', 400);
		
		elgg_register_ajax_view('group_tools/forms/motivation');
		elgg_register_ajax_view('forms/group_tools/admin/auto_join/default');
		elgg_register_ajax_view('forms/group_tools/admin/auto_join/additional');
		elgg_register_ajax_view('group_tools/elements/auto_join_match_pattern');
	}
	
	protected function registerEvents() {
		elgg_register_event_handler('join', 'group', '\ColdTrick\GroupTools\Membership::groupJoin');
		elgg_register_event_handler('delete', 'relationship', 'ColdTrick\GroupTools\Membership::deleteRequest');
		elgg_register_event_handler('create', 'user', '\ColdTrick\GroupTools\Membership::autoJoinGroups');
		elgg_register_event_handler('login:after', 'user', '\ColdTrick\GroupTools\Membership::autoJoinGroupsLogin');
		elgg_register_event_handler('create', 'relationship', '\ColdTrick\GroupTools\Membership::siteJoinEmailInvitedGroups');
		elgg_register_event_handler('create', 'relationship', '\ColdTrick\GroupTools\Membership::siteJoinGroupInviteCode');
		elgg_register_event_handler('create', 'relationship', '\ColdTrick\GroupTools\Membership::siteJoinDomainBasedGroups');
		elgg_register_event_handler('create', 'relationship', '\ColdTrick\GroupTools\GroupAdmins::membershipRequest');
		elgg_register_event_handler('leave', 'group', '\ColdTrick\GroupTools\GroupAdmins::groupLeave');
	}
	
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('entity:url', 'object', '\ColdTrick\GroupTools\WidgetManager::widgetURL');
		$hooks->registerHandler('default', 'access', '\ColdTrick\GroupTools\Access::setGroupDefaultAccess');
		$hooks->registerHandler('default', 'access', '\ColdTrick\GroupTools\Access::validateGroupDefaultAccess', 999999);
		$hooks->registerHandler('access:collections:write', 'user', '\ColdTrick\GroupTools\Access::defaultAccessOptions');
		$hooks->registerHandler('action', 'groups/join', '\ColdTrick\GroupTools\Membership::groupJoinAction');
		$hooks->registerHandler('register', 'menu:owner_block', '\ColdTrick\GroupTools\OwnerBlockMenu::relatedGroups');
		$hooks->registerHandler('route', 'register', '\ColdTrick\GroupTools\Router::allowRegistration');
		$hooks->registerHandler('action', 'register', '\ColdTrick\GroupTools\Router::allowRegistration');
		$hooks->registerHandler('group_tool_widgets', 'widget_manager', '\ColdTrick\GroupTools\WidgetManager::groupToolWidgets');
		$hooks->registerHandler('head', 'page', '\ColdTrick\GroupTools\PageLayout::noIndexClosedGroups');
		$hooks->registerHandler('permissions_check', 'group', '\ColdTrick\GroupTools\GroupAdmins::permissionsCheck');
		$hooks->registerHandler('register', 'menu:title', '\ColdTrick\GroupTools\TitleMenu::groupMembership');
		$hooks->registerHandler('register', 'menu:title', '\ColdTrick\GroupTools\TitleMenu::groupInvite');
		$hooks->registerHandler('register', 'menu:title', '\ColdTrick\GroupTools\TitleMenu::exportGroupMembers');
		$hooks->registerHandler('register', 'menu:title', '\ColdTrick\GroupTools\TitleMenu::pendingApproval', 9999);
		$hooks->registerHandler('register', 'menu:user_hover', '\ColdTrick\GroupTools\GroupAdmins::assignGroupAdmin');
		$hooks->registerHandler('register', 'menu:entity', '\ColdTrick\GroupTools\GroupAdmins::assignGroupAdmin', 501);
		$hooks->registerHandler('register', 'menu:entity', '\ColdTrick\GroupTools\EntityMenu::relatedGroup');
		$hooks->registerHandler('register', 'menu:page', '\ColdTrick\GroupTools\PageMenu::registerAdminItems', 501);
		$hooks->registerHandler('register', 'menu:membershiprequest', '\ColdTrick\GroupTools\Membership::membershiprequestMenu');
		$hooks->registerHandler('register', 'menu:emailinvitation', '\ColdTrick\GroupTools\Membership::emailinvitationMenu');
		$hooks->registerHandler('register', 'menu:group:membershiprequests', '\ColdTrick\GroupTools\Membership::groupMembershiprequests');
		$hooks->registerHandler('register', 'menu:group:membershiprequest', '\ColdTrick\GroupTools\Membership::groupMembershiprequest');
		$hooks->registerHandler('register', 'menu:group:invitation', '\ColdTrick\GroupTools\Membership::groupInvitation');
		$hooks->registerHandler('register', 'menu:group:email_invitation', '\ColdTrick\GroupTools\Membership::groupEmailInvitation');
		$hooks->registerHandler('register', 'menu:page', '\ColdTrick\GroupTools\Membership::groupProfileSidebar');
		$hooks->registerHandler('register', 'menu:filter:groups/all', '\ColdTrick\GroupTools\GroupSortMenu::removeTabs', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', '\ColdTrick\GroupTools\GroupSortMenu::addTabs', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', '\ColdTrick\GroupTools\GroupSortMenu::addSorting', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', '\ColdTrick\GroupTools\GroupSortMenu::cleanupTabs', 900);
		$hooks->registerHandler('register', 'menu:groups:my_status', '\ColdTrick\GroupTools\MyStatus::registerJoinStatus');
		$hooks->registerHandler('prepare', 'menu:filter:groups/all', '\ColdTrick\GroupTools\GroupSortMenu::setSelected', 550);
		$hooks->registerHandler('route', 'groups', '\ColdTrick\GroupTools\Router::groups');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', '\ColdTrick\GroupTools\CSVExporter::addGroupAdminsToGroups');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', '\ColdTrick\GroupTools\CSVExporter::addGroupAdminsToUsers');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', '\ColdTrick\GroupTools\CSVExporter::addStaleInfo');
		$hooks->registerHandler('export_value', 'csv_exporter', '\ColdTrick\GroupTools\CSVExporter::exportGroupAdminsForGroups');
		$hooks->registerHandler('export_value', 'csv_exporter', '\ColdTrick\GroupTools\CSVExporter::exportGroupAdminsForUsers');
		$hooks->registerHandler('export_value', 'csv_exporter', '\ColdTrick\GroupTools\CSVExporter::exportStaleInfo');
		$hooks->registerHandler('get', 'subscriptions', '\ColdTrick\GroupTools\GroupAdmins::addGroupAdminsToMembershipRequest');
		$hooks->registerHandler('get', 'subscriptions', '\ColdTrick\GroupTools\Notifications::adminApprovalSubs');
		$hooks->registerHandler('prepare', 'notification:admin_approval:group:', '\ColdTrick\GroupTools\Notifications::prepareAdminApprovalMessage');
		$hooks->registerHandler('action', 'groups/edit', '\ColdTrick\GroupTools\Group::editActionListener');
		$hooks->registerHandler('cron', 'fiveminute', '\ColdTrick\GroupTools\Membership::autoJoinGroupsCron');
		$hooks->registerHandler('cron', 'daily', '\ColdTrick\GroupTools\Cron::notifyStaleGroupOwners');
		$hooks->registerHandler('register', 'menu:page', '\ColdTrick\GroupTools\GroupMail::pageMenu');
		$hooks->registerHandler('prepare', 'notification:enqueue:object:group_tools_group_mail', '\ColdTrick\GroupTools\GroupMail::prepareNotification');
		$hooks->registerHandler('get', 'subscriptions', '\ColdTrick\GroupTools\GroupMail::getSubscribers');
		$hooks->registerHandler('send:after', 'notifications', '\ColdTrick\GroupTools\GroupMail::cleanup');
		$hooks->registerHandler('prepare', 'notification:membership_request:group:group', '\ColdTrick\GroupTools\GroupAdmins::prepareMembershipRequestMessage');
	}
}
