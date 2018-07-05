<?php

namespace ColdTrick\GroupTools;

class Router {
		
	/**
	 * Enables registration on the site if disabled and a valid group invite code is provided
	 *
	 * Both access to the registration page and the registration action
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param null   $params       supplied params
	 *
	 * @return void
	 */
	public static function allowRegistration($hook, $type, $return_value, $params) {
		
		$registration_allowed = (bool) elgg_get_config('allow_registration');
		if ($registration_allowed) {
			return;
		}
		
		// check for a group invite code
		$group_invitecode = get_input('group_invitecode');
		if (empty($group_invitecode)) {
			return;
		}
		
		// check if the code is valid
		if (group_tools_check_group_email_invitation($group_invitecode)) {
			// we have a valid code, so allow registration
			elgg_set_config('allow_registration', true);
		}
	}
}
