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

		$this->registerHooks();
	}
	
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\GroupAdmins::addGroupAdminsToMembershipRequest');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\Notifications::adminApprovalSubs');
		$hooks->registerHandler('get', 'subscriptions', __NAMESPACE__ . '\GroupMail::getSubscribers');
		$hooks->registerHandler('prepare', 'notification:admin_approval:group:group', __NAMESPACE__ . '\Notifications::prepareAdminApprovalMessage');
		$hooks->registerHandler('prepare', 'notification:enqueue:object:group_tools_group_mail', __NAMESPACE__ . '\GroupMail::prepareNotification');
		$hooks->registerHandler('prepare', 'notification:membership_request:group:group', __NAMESPACE__ . '\GroupAdmins::prepareMembershipRequestMessage');
		$hooks->registerHandler('send:after', 'notifications', __NAMESPACE__ . '\GroupMail::cleanup');
		$hooks->registerHandler('send:after', 'notifications', __NAMESPACE__ . '\Notifications::sendConfirmationOfGroupAdminApprovalToOwner');
	}
}
