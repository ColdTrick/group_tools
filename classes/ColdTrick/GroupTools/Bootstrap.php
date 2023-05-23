<?php

namespace ColdTrick\GroupTools;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap
 */
class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function load() {
		$events = $this->elgg()->events;
		$events->registerHandler('route:config', 'account:register', __NAMESPACE__ . '\Router\GroupInviteRegistrationGatekeeper::register');
		$events->registerHandler('route:config', 'action:register', __NAMESPACE__ . '\Router\GroupInviteRegistrationGatekeeper::register');
	}
}
