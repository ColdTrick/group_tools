<?php

namespace ColdTrick\GroupTools;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 */
	public function load() {
		$hooks = $this->elgg()->hooks;
		$hooks->registerHandler('route:config', 'account:register', __NAMESPACE__ . '\Router\GroupInviteRegistrationGatekeeper::register');
		$hooks->registerHandler('route:config', 'action:register', __NAMESPACE__ . '\Router\GroupInviteRegistrationGatekeeper::register');
	}
	
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
	}
}
