<?php

namespace ColdTrick\GroupTools\Router;

use Elgg\Request;
use Elgg\Router\Middleware\RegistrationAllowedGatekeeper;

class GroupInviteRegistrationGatekeeper extends RegistrationAllowedGatekeeper {
	
	/**
	 * {@inheritDoc}
	 */
	public function __invoke(Request $request): void {
		if ($this->validateGroupInvitecode($request)) {
			return;
		}
		
		parent::__invoke($request);
	}
	
	/**
	 * Validate the group invite code
	 *
	 * @param Request $request the request
	 *
	 * @return bool
	 */
	protected function validateGroupInvitecode(Request $request): bool {
		// check for a group invite code
		$group_invitecode = $request->getParam('group_invitecode');
		if (empty($group_invitecode)) {
			return false;
		}
		
		// check if the code is valid
		return group_tools_check_group_email_invitation($group_invitecode) instanceof \ElggGroup;
	}
	
	/**
	 * Change the middleware of the registration to this gatekeeper
	 *
	 * @param \Elgg\Hook $hook 'route:config', 'account:register'|'action:register'
	 *
	 * @return array
	 */
	public static function register(\Elgg\Hook $hook) {
		$route_config = $hook->getValue();
		$middleware = elgg_extract('middleware', $route_config, []);
		
		// find the default registration gatekeeper
		$key = array_search(RegistrationAllowedGatekeeper::class, $middleware);
		if ($key !== false) {
			unset($middleware[$key]);
		}
		
		// add this gatekeeper
		$middleware[] = static::class;
		
		$route_config['middleware'] = $middleware;
		
		return $route_config;
	}
}
