<?php

namespace ColdTrick\GroupTools;

class Router {
	
	/**
	 * Take over the groups page handler in some cases
	 *
	 * @param string $hook         the name of the hook
	 * @param string $type         the type of the hook
	 * @param array  $return_value current return value
	 * @param null   $params       supplied params
	 *
	 * @return void|false
	 */
	public static function groups($hook, $type, $return_value, $params) {
		
		if (empty($return_value) || !is_array($return_value)) {
			return;
		}
		
		$include_file = false;
		$pages_path = elgg_get_plugins_path() . 'group_tools/pages/';
		
		$page = elgg_extract('segments', $return_value);
		switch ($page[0]) {
			case 'all':
				$filter = get_input('filter');
				$default_filter = elgg_get_plugin_setting('group_listing', 'group_tools');
				
				if (empty($filter) && !empty($default_filter)) {
					$filter = $default_filter;
					set_input('filter', $default_filter);
				} elseif (empty($filter)) {
					$filter = 'newest';
					set_input('filter', $filter);
				}
					
				if (in_array($filter, ['yours', 'open', 'closed', 'alpha', 'ordered', 'suggested'])) {
					$include_file = "{$pages_path}groups/all.php";
				}
				
				break;
			case 'suggested':
				$include_file = "{$pages_path}groups/suggested.php";
				break;
			case 'search':
				$include_file = "{$pages_path}groups/search.php";
				break;
			case 'requests':
				set_input('group_guid', $page[1]);
				if (isset($page[2])) {
					set_input('subpage', $page[2]);
				}
					
				$include_file = "{$pages_path}groups/membershipreq.php";
				break;
			case 'invite':
				set_input('group_guid', $page[1]);
					
				$include_file = "{$pages_path}groups/invite.php";
				break;
			case 'mail':
				set_input('group_guid', $page[1]);
		
				$include_file = "{$pages_path}mail.php";
				break;
			case 'members':
				set_input('group_guid', $page[1]);
		
				$include_file = "{$pages_path}groups/members.php";
				break;
			case 'group_invite_autocomplete':
				$include_file = "{$pages_path}group_invite_autocomplete.php";
				break;
			case 'add':
				if (group_tools_is_group_creation_limited()) {
					elgg_admin_gatekeeper();
				}
				break;
			case 'invitations':
				if (isset($page[1])) {
					set_input('username', $page[1]);
				}
					
				$include_file = "{$pages_path}groups/invitations.php";
				break;
			case 'related':
				if (isset($page[1])) {
					set_input('group_guid', $page[1]);
				}
					
				$include_file = "{$pages_path}groups/related.php";
				break;
			case 'activity':
				if (isset($page[1])) {
					set_input('guid', $page[1]);
				}
					
				$include_file = "{$pages_path}groups/river.php";
				break;
			default:
				// check if we have an old group profile link
				if (isset($page[0]) && is_numeric($page[0])) {
					$group = get_entity($page[0]);
					if ($group instanceof ElggGroup) {
						register_error(elgg_echo('changebookmark'));
						forward($group->getURL());
					}
				}
				break;
		}
		
		// did we want this page?
		if (!empty($include_file)) {
			include($include_file);
			return false;
		}
	}
}
