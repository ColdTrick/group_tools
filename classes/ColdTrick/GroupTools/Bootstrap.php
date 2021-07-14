<?php

namespace ColdTrick\GroupTools;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function init() {
		if (is_callable('profile_manager_add_custom_field_type')) {
			profile_manager_add_custom_field_type('custom_group_field_types', 'group_tools_preset', elgg_echo('group_tools:profile:field:group_tools_preset'), [
				'user_editable' => true,
				'output_as_tags' => true,
				'admin_only' => true,
			]);
		}
		
		elgg_register_notification_event('group', 'group', ['admin_approval']);
		elgg_register_notification_event('object', 'group_tools_group_mail', ['enqueue']);

		$this->registerViews();
		$this->registerEvents();
		$this->registerHooks();
	}
	
	protected function registerViews() {
		elgg_register_ajax_view('forms/group_tools/admin/auto_join/additional');
		elgg_register_ajax_view('forms/group_tools/admin/auto_join/default');
		elgg_register_ajax_view('forms/groups/killrequest');
		elgg_register_ajax_view('group_tools/elements/auto_join_match_pattern');
		elgg_register_ajax_view('group_tools/forms/motivation');
		elgg_register_ajax_view('group_tools/group/reasons');
		elgg_register_ajax_view('group_tools/group/suggested');
	}
	
	protected function registerEvents() {
		$event = $this->elgg()->events;
		
		$event->registerHandler('create', 'user', __NAMESPACE__ . '\Membership::createUserEmailInvitedGroups');
		$event->registerHandler('create', 'user', __NAMESPACE__ . '\Membership::createUserGroupInviteCode');
		$event->registerHandler('create', 'user', __NAMESPACE__ . '\Membership::createUserDomainBasedGroups');
		$event->registerHandler('create', 'user', __NAMESPACE__ . '\Membership::autoJoinGroups');
		$event->registerHandler('delete', 'relationship', 'ColdTrick\GroupTools\Membership::deleteRequest');
		$event->registerHandler('join', 'group', __NAMESPACE__ . '\Membership::groupJoin');
		$event->registerHandler('leave', 'group', __NAMESPACE__ . '\GroupAdmins::groupLeave');
		$event->registerHandler('login:after', 'user', __NAMESPACE__ . '\Membership::autoJoinGroupsLogin');
	}
	
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('action:validate', 'groups/join', __NAMESPACE__ . '\Membership::groupJoinAction');
		$hooks->registerHandler('action:validate', 'register', __NAMESPACE__ . '\Router::allowRegistration');
		$hooks->registerHandler('action:validate', 'groups/edit', __NAMESPACE__ . '\Group::editActionListener');
		$hooks->registerHandler('cron', 'fiveminute', __NAMESPACE__ . '\Membership::autoJoinGroupsCron');
		$hooks->registerHandler('cron', 'daily', __NAMESPACE__ . '\Cron::notifyStaleGroupOwners');
		$hooks->registerHandler('cron', 'daily', __NAMESPACE__ . '\Cron::removeExpiredConceptGroups');
		$hooks->registerHandler('cron', 'weekly', __NAMESPACE__ . '\Cron::notifyConceptGroupOwners');
		$hooks->registerHandler('entity:url', 'object', __NAMESPACE__ . '\WidgetManager::widgetURL');
		$hooks->registerHandler('export_value', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::exportApprovalReasons');
		$hooks->registerHandler('export_value', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::exportGroupAdminsForGroups');
		$hooks->registerHandler('export_value', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::exportGroupAdminsForUsers');
		$hooks->registerHandler('export_value', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::exportStaleInfo');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\GroupAdmins::addGroupAdminsToMembershipRequest');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Notifications::adminApprovalSubs');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\GroupMail::getSubscribers');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::addApprovalReasons');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::addGroupAdminsToGroups');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::addGroupAdminsToUsers');
		$hooks->registerHandler('get_exportable_values', 'csv_exporter', __NAMESPACE__ . '\CSVExporter::addStaleInfo');
		$hooks->registerHandler('group_tool_widgets', 'widget_manager', __NAMESPACE__ . '\WidgetManager::groupToolWidgets');
		$hooks->registerHandler('handlers', 'widgets', __NAMESPACE__ . '\Widgets::unregsiterRelatedGroupsWidget');
		$hooks->registerHandler('head', 'page', __NAMESPACE__ . '\PageLayout::noIndexClosedGroups');
		$hooks->registerHandler('parameters', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::enableSorting');
		$hooks->registerHandler('permissions_check', 'group', __NAMESPACE__ . '\GroupAdmins::permissionsCheck');
		$hooks->registerHandler('prepare', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::setSelected', 550);
		$hooks->registerHandler('prepare', 'notification:admin_approval:group:group', __NAMESPACE__ . '\Notifications::prepareAdminApprovalMessage');
		$hooks->registerHandler('prepare', 'notification:enqueue:object:group_tools_group_mail', __NAMESPACE__ . '\GroupMail::prepareNotification');
		$hooks->registerHandler('prepare', 'notification:membership_request:group:group', __NAMESPACE__ . '\GroupAdmins::prepareMembershipRequestMessage');
		$hooks->registerHandler('register', 'menu:annotation', __NAMESPACE__ . '\Menus\Annotation::registerEmailInvitation');
		$hooks->registerHandler('register', 'menu:annotation', __NAMESPACE__ . '\Menus\Annotation::registerEmailInvitationUser');
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\GroupAdmins::assignGroupAdmin', 501);
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\EntityMenu::relatedGroup');
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\EntityMenu::suggestedGroup');
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\EntityMenu::addRemoveFromGroup');
		$hooks->registerHandler('register', 'menu:entity', __NAMESPACE__ . '\EntityMenu::registerApprovalReasons');
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::removeTabs', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::addTabs', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::addSorting', 550);
		$hooks->registerHandler('register', 'menu:filter:groups/all', __NAMESPACE__ . '\GroupSortMenu::cleanupTabs', 900);
		$hooks->registerHandler('register', 'menu:filter:group/invitations', __NAMESPACE__ . '\Menus\Filter::registerUserEmailInvitations');
		$hooks->registerHandler('register', 'menu:groups_members', __NAMESPACE__ . '\Menus\GroupsMembers::registerGroupAdmins');
		$hooks->registerHandler('register', 'menu:groups_members', __NAMESPACE__ . '\Menus\GroupsMembers::registerEmailInvitations');
		$hooks->registerHandler('register', 'menu:owner_block', __NAMESPACE__ . '\OwnerBlockMenu::relatedGroups');
		$hooks->registerHandler('register', 'menu:page', __NAMESPACE__ . '\PageMenu::registerAdminItems', 501);
		$hooks->registerHandler('register', 'menu:page', __NAMESPACE__ . '\GroupMail::pageMenu');
		$hooks->registerHandler('register', 'menu:relationship', __NAMESPACE__ . '\Menus\Relationship::groupDeclineMembershipReason', 501);
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::groupMembership', 501);
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::groupInvite', 501);
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::groupAdminStatus', 501);
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::exportGroupMembers');
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::pendingApproval', 9999);
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::conceptGroup', 9999); // needs to be after pending approval
		$hooks->registerHandler('register', 'menu:title', __NAMESPACE__ . '\TitleMenu::addGroupToolPresets');
		$hooks->registerHandler('register', 'menu:user_hover', __NAMESPACE__ . '\GroupAdmins::assignGroupAdmin');
		$hooks->registerHandler('send:after', 'notifications', __NAMESPACE__ . '\GroupMail::cleanup');
		$hooks->registerHandler('send:after', 'notifications', __NAMESPACE__ . '\Notifications::sendConfirmationOfGroupAdminApprovalToOwner');
		$hooks->registerHandler('tool_options', 'group', __NAMESPACE__ . '\Tools::registerRelatedGroups');
		$hooks->registerHandler('view_vars', 'groups/edit/access', __NAMESPACE__ . '\Views::allowGroupOwnerTransfer');
		$hooks->registerHandler('view_vars', 'input/form', __NAMESPACE__ . '\Views::allowDoubleSubmitWhenConceptGroupsEnabled');
		$hooks->registerHandler('view_vars', 'page/components/list', __NAMESPACE__ . '\Views::livesearchUserListing');
		$hooks->registerHandler('view_vars', 'relationship/membership_request', __NAMESPACE__ . '\Views::addJoinMotivationToGroupMembershipRequest');
		$hooks->registerHandler('view_vars', 'resources/account/register', __NAMESPACE__ . '\Router::allowRegistration');
		$hooks->registerHandler('view_vars', 'resources/groups/add', __NAMESPACE__ . '\Views::protectGroupAdd');
		$hooks->registerHandler('view_vars', 'resources/groups/all', __NAMESPACE__ . '\Views::prepareGroupAll');
	}
}
