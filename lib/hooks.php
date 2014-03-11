<?php
/**
 * All plugin hook callback functions are defined in this file
 *
 * @package group_tools
 */

/**
 * Allow group admins (not owners) to also edit group content
 *
 * @param string $hook         the 'permissions_check' hook
 * @param string $type         for the 'group' type
 * @param bool   $return_value the current value
 * @param array  $params       supplied params to help change the outcome
 *
 * @return bool true if can edit, false otherwise
 */
function group_tools_multiple_admin_can_edit_hook($hook, $type, $return_value, $params) {
	$result = $return_value;

	if (!empty($params) && is_array($params) && !$result) {
		if (array_key_exists("entity", $params) && array_key_exists("user", $params)) {
			$entity = $params["entity"];
			$user = $params["user"];

			if (($entity instanceof ElggGroup) && ($user instanceof ElggUser)) {
				if ($entity->isMember($user) && check_entity_relationship($user->getGUID(), "group_admin", $entity->getGUID())) {
					$result = true;
				}
			}
		}
	}

	return $result;
}

/**
 * Take over the groups page handler in some cases
 *
 * @param string $hook         the 'route' hook
 * @param string $type         for the 'groups' page handler
 * @param bool   $return_value tells which page is handled, contains:
 *    $return_value['handler'] => requested handler
 *    $return_value['segments'] => url parts ($page)
 * @param null   $params       no params provided
 *
 * @return bool false if we take over the page handler
 */
function group_tools_route_groups_handler($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	if (!empty($return_value) && is_array($return_value)) {
		$page = $return_value['segments'];
		
		switch ($page[0]) {
			case "all":
				$filter = get_input("filter");
				$default_filter = elgg_get_plugin_setting("group_listing", "group_tools");
				
				if (empty($filter) && !empty($default_filter)) {
					$filter = $default_filter;
					set_input("filter", $default_filter);
				} elseif (empty($filter)) {
					$filter = "newest";
					set_input("filter", $filter);
				}
				
				if (in_array($filter, array("yours", "open", "closed", "alpha", "ordered", "suggested"))) {
					// we will handle the output
					$result = false;
					
					include(dirname(dirname(__FILE__)) . "/pages/groups/all.php");
				}
				
				break;
			case "suggested":
				$result = false;
				
				include(dirname(dirname(__FILE__)) . "/pages/groups/suggested.php");
				break;
			case "search":
				$result = false;
					
				include(dirname(dirname(__FILE__)) . "/pages/groups/search.php");
				break;
			case "requests":
				$result = false;
				
				set_input("group_guid", $page[1]);
				
				include(dirname(dirname(__FILE__)) . "/pages/groups/membershipreq.php");
				break;
			case "invite":
				$result = false;
				
				set_input("group_guid", $page[1]);
				
				include(dirname(dirname(__FILE__)) . "/pages/groups/invite.php");
				break;
			case "mail":
				$result = false;
				
				set_input("group_guid", $page[1]);
					
				include(dirname(dirname(__FILE__)) . "/pages/mail.php");
				break;
			case "group_invite_autocomplete":
				$result = false;
				
				include(dirname(dirname(__FILE__)) . "/procedures/group_invite_autocomplete.php");
				break;
			case "add":
				if (group_tools_is_group_creation_limited()) {
					admin_gatekeeper();
				}
				break;
			case "invitations":
				$result = false;
				if (isset($page[1])) {
					set_input("username", $page[1]);
				}
				
				include(dirname(dirname(__FILE__)) . "/pages/groups/invitations.php");
				break;
			case "related":
				$result = false;
				
				if (isset($page[1])) {
					set_input("group_guid", $page[1]);
				}
				
				include(dirname(dirname(__FILE__)) . "/pages/groups/related.php");
				break;
			default:
				// check if we have an old group profile link
				if (isset($page[0]) && is_numeric($page[0])) {
					$group = get_entity($page[0]);
					if (!empty($group) && elgg_instanceof($group, "group", null, "ElggGroup")) {
						register_error(elgg_echo("changebookmark"));
						forward($group->getURL());
					}
				}
				break;
		}
	}
	
	return $result;
}

/**
 * Modify the title menu in the groups context.
 *
 * @param string $hook         the 'register' hook
 * @param string $type         for the 'menu:title' menu
 * @param array  $return_value the menu items to show
 * @param arary  $params       params to help extend the menu items
 *
 * @return ElggMenuItem[] a list of menu items
 */
function group_tools_menu_title_handler($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	$page_owner = elgg_get_page_owner_entity();
	$user = elgg_get_logged_in_user_entity();
	
	if (!empty($result) && is_array($result)) {
		if (elgg_in_context("groups")) {
			// modify some group menu items
			if (!empty($page_owner) && !empty($user) && ($page_owner instanceof ElggGroup)) {
				$invite_found = false;
				
				foreach ($result as $menu_item) {
					
					switch ($menu_item->getName()) {
						case "groups:joinrequest":
							if (check_entity_relationship($user->getGUID(), "membership_request", $page_owner->getGUID())) {
								// user already requested to join this group
								$menu_item->setText(elgg_echo("group_tools:joinrequest:already"));
								$menu_item->setTooltip(elgg_echo("group_tools:joinrequest:already:tooltip"));
								$menu_item->setHref(elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/groups/killrequest?user_guid=" . $user->getGUID() . "&group_guid=" . $page_owner->getGUID()));
							} elseif (check_entity_relationship($page_owner->getGUID(), "invited", $user->getGUID())) {
								// the user was invited, so let him/her join
								$menu_item->setName("groups:join");
								$menu_item->setText(elgg_echo("groups:join"));
								$menu_item->setTooltip(elgg_echo("group_tools:join:already:tooltip"));
								$menu_item->setHref(elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/groups/join?user_guid=" . $user->getGUID() . "&group_guid=" . $page_owner->getGUID()));
							} elseif (group_tools_check_domain_based_group($page_owner, $user)) {
								// user has a matching email domain
								$menu_item->setName("groups:join");
								$menu_item->setText(elgg_echo("groups:join"));
								$menu_item->setTooltip(elgg_echo("group_tools:join:domain_based:tooltip"));
								$menu_item->setHref(elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/groups/join?user_guid=" . $user->getGUID() . "&group_guid=" . $page_owner->getGUID()));
							}
							
							break;
						case "groups:invite":
							$invite_found = true;
							
							$invite = elgg_get_plugin_setting("invite", "group_tools");
							$invite_email = elgg_get_plugin_setting("invite_email", "group_tools");
							$invite_csv = elgg_get_plugin_setting("invite_csv", "group_tools");
							
							if (in_array("yes", array($invite, $invite_csv, $invite_email))) {
								$menu_item->setText(elgg_echo("group_tools:groups:invite"));
							}
							
							break;
					}
				}
				
				// maybe allow normal users to invite new members
				if (elgg_in_context("group_profile") && !$invite_found) {
					// this is only allowed for group members
					if ($page_owner->isMember($user)) {
						// we're on a group profile page, but haven't found the invite button yet
						// so check if it should be here
						$setting = elgg_get_plugin_setting("invite_members", "group_tools");
						if (in_array($setting, array("yes_off", "yes_on"))) {
							$invite_members = $page_owner->invite_members;
							if (empty($invite_members)) {
								$invite_members = "no";
								if ($setting == "yes_on") {
									$invite_members = "yes";
								}
							}
							
							if ($invite_members == "yes") {
								// normal users are allowed to invite users
								$invite = elgg_get_plugin_setting("invite", "group_tools");
								$invite_email = elgg_get_plugin_setting("invite_email", "group_tools");
								$invite_csv = elgg_get_plugin_setting("invite_csv", "group_tools");
								
								if (in_array("yes", array($invite, $invite_csv, $invite_email))) {
									$text = elgg_echo("group_tools:groups:invite");
								} else {
									$text = elgg_echo("groups:invite");
								}
								
								$result[] = ElggMenuItem::factory(array(
									"name" => "groups:invite",
									"href" => "groups/invite/" . $page_owner->getGUID(),
									"text" => $text,
									"link_class" => "elgg-button elgg-button-action",
								));
							}
						}
					}
				}
			}
			
			// check if we need to remove the group add button
			if (!empty($user) && !$user->isAdmin() && group_tools_is_group_creation_limited()) {
				foreach ($result as $index => $menu_item) {
					if ($menu_item->getName() == "add") {
						unset($result[$index]);
					}
				}
			}
		}
	}
	
	return $result;
}

/**
 * Modify the user hover menu.
 *
 * @param string $hook         the 'register' hook
 * @param string $type         for the 'menu:user_hover' menu
 * @param array  $return_value the menu items to show
 * @param arary  $params       params to help extend the menu items
 *
 * @return ElggMenuItem[] a list of menu items
 */
function group_tools_menu_user_hover_handler($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	$page_owner = elgg_get_page_owner_entity();
	$loggedin_user = elgg_get_logged_in_user_entity();
	
	if (!empty($page_owner) && ($page_owner instanceof ElggGroup) && !empty($loggedin_user)) {
		// are multiple admins allowed
		if (elgg_get_plugin_setting("multiple_admin", "group_tools") == "yes") {
			if (!empty($params) && is_array($params)) {
				$user = $params["entity"];
				
				// do we have a user
				if (!empty($user) && ($user instanceof ElggUser)) {
					// is the user not the owner of the group and noet the current user
					if (($page_owner->getOwnerGUID() != $user->getGUID()) && ($user->getGUID() != $loggedin_user->getGUID())) {
						// is the user a member od this group
						if ($page_owner->isMember($user)) {
							// can we add/remove an admin
							if (($page_owner->getOwnerGUID() == $loggedin_user->getGUID()) || ($page_owner->group_multiple_admin_allow_enable == "yes" && $page_owner->canEdit()) || $loggedin_user->isAdmin()) {
								if (check_entity_relationship($user->getGUID(), "group_admin", $page_owner->getGUID())) {
									$text = elgg_echo("group_tools:multiple_admin:profile_actions:remove");
								} else {
									$text = elgg_echo("group_tools:multiple_admin:profile_actions:add");
								}
								
								$result[] = ElggMenuItem::factory(array(
									"text" => $text,
									"name" => "group_admin",
									"href" => elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/group_tools/toggle_admin?group_guid=" . $page_owner->getGUID() . "&user_guid=" . $user->getGUID())
								));
							}
						}
					}
				}
			}
		}
	}
	
	return $result;
}

/**
 * Modify the entity menu.
 *
 * @param string $hook         the 'register' hook
 * @param string $type         for the 'menu:entity' menu
 * @param array  $return_value the menu items to show
 * @param array  $params       params to help extend the menu items
 *
 * @return ElggMenuItem[] a list of menu items
 */
function group_tools_menu_entity_handler($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	if (!empty($params) && is_array($params)) {
		
		$entity = elgg_extract("entity", $params);
		$page_owner = elgg_get_page_owner_entity();
		
		if (elgg_in_context("group_tools_related_groups") && !empty($page_owner) && elgg_instanceof($page_owner, "group") && $page_owner->canEdit() && elgg_instanceof($entity, "group")) {
			// remove relatede group
			$result[] = ElggMenuItem::factory(array(
				"name" => "related_group",
				"text" => elgg_echo("group_tools:related_groups:entity:remove"),
				"href" => "action/group_tools/remove_related_groups?group_guid=" . $page_owner->getGUID() . "&guid=" . $entity->getGUID(),
				"confirm" => elgg_echo("question:areyousure")
			));
		} elseif (elgg_in_context("widgets_groups_show_members") && elgg_instanceof($entity, "group")) {
			// number of members
			$num_members = $entity->getMembers(10, 0, true);
			
			$result[] = ElggMenuItem::factory(array(
				"name" => "members",
				"text" => $num_members . " " . elgg_echo("groups:member"),
				"href" => false,
				"priority" => 200,
			));
		} elseif (elgg_instanceof($entity, "object", "groupforumtopic") && $entity->canEdit()) {
			$text = elgg_echo("close");
			$confirm = elgg_echo("group_tools:discussion:confirm:close");
			if ($entity->status == "closed") {
				$text = elgg_echo("open");
				$confirm = elgg_echo("group_tools:discussion:confirm:open");
			}
			
			$result[] = ElggMenuItem::factory(array(
				"name" => "status_change",
				"text" => $text,
				"confirm" => $confirm,
				"href" => "action/discussion/toggle_status?guid=" . $entity->getGUID(),
				"is_trusted" => true,
				"priority" => 200
			));
		} elseif (elgg_instanceof($entity, "group") && group_tools_show_hidden_indicator($entity)) {
			$access_id_string = get_readable_access_level($entity->access_id);
			$access_id_string = htmlspecialchars($access_id_string, ENT_QUOTES, "UTF-8", false);
			
			$text = "<span title='" . $access_id_string . "'>" . elgg_view_icon("eye") . "</span>";
			
			$result[] = ElggMenuItem::factory(array(
				"name" => "hidden_indicator",
				"text" => $text,
				"href" => false,
				"priority" => 1
			));
		}
	}
	
	return $result;
}

/**
 * return an url to be used by Widget Manager
 *
 * @param string $hook         the 'widget_url' hook
 * @param string $type         for 'widget_manager'
 * @param string $return_value the default return value
 * @param array  $params       params to help set a correct url
 *
 * @return string the widger url
 */
function group_tools_widget_url_handler($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	if (!$result && !empty($params) && is_array($params)) {
		$widget = elgg_extract("entity", $params);
		
		if (!empty($widget) && elgg_instanceof($widget, "object", "widget")) {
			switch ($widget->handler) {
				case "group_members":
					$result = "groups/members/" . $widget->getOwnerGUID();
					break;
				case "group_invitations":
					$user = elgg_get_logged_in_user_entity();
					if (!empty($user)) {
						$result = "groups/invitations/" . $user->username;
					}
					break;
				case "discussion":
					$result = "discussion/all";
					break;
				case "group_forum_topics":
					$page_owner = elgg_get_page_owner_entity();
					if (!empty($page_owner) && ($page_owner instanceof ElggGroup)) {
						$result = "discussion/owner/" . $page_owner->getGUID();
						break;
					}
				case "group_river_widget":
					if ($widget->context != "groups") {
						$group_guid = (int) $widget->group_guid;
					} else {
						$group_guid = $widget->getOwnerGUID();
					}
					
					if (!empty($group_guid)) {
						$group = get_entity($group_guid);
						if (!empty($group) && elgg_instanceof($group, "group", null, "ElggGroup")) {
							$result = "groups/activity/" . $group_guid;
						}
					}
					break;
				case "index_groups":
				case "featured_groups":
					$result = "groups/all";
					break;
				case "a_user_groups":
					$owner = $widget->getOwnerEntity();
					if (!empty($owner) && elgg_instanceof($owner, "user")) {
						$result = "groups/member/" . $owner->username;
					}
					break;
				case "start_discussion":
					$owner = $widget->getOwnerEntity();
					if (!empty($owner) && elgg_instanceof($owner, "group")) {
						$result = "discussion/add/" . $owner->getGUID();
					}
					break;
				case "group_related":
					$result = "groups/related/" . $widget->getOwnerGUID();
					break;
			}
		}
	}
	
	return $result;
}

/**
 * Allows the edit of default access
 *
 * See:
 * @link http://trac.elgg.org/ticket/4415
 * @link https://github.com/Elgg/Elgg/pull/253
 *
 * @param string $hook         the 'access:default' hook
 * @param string $type         for the 'user' type
 * @param int    $return_value the default access for this user
 * @param array  $params       params to help change the value
 *
 * @return int the access_id to use as default
 */
function group_tools_access_default_handler($hook, $type, $return_value, $params) {
	global $GROUP_TOOLS_GROUP_DEFAULT_ACCESS_ENABLED;
	$GROUP_TOOLS_GROUP_DEFAULT_ACCESS_ENABLED = true;
	
	$result = $return_value;
	
	// check if the page owner is a group
	$page_owner = elgg_get_page_owner_entity();
	if (!empty($page_owner) && elgg_instanceof($page_owner, "group", null, "ElggGroup")) {
		// check if the group as a default access set
		$group_access = $page_owner->getPrivateSetting("elgg_default_access");
		if ($group_access !== false) {
			$result = (int) $group_access;
		}
		
		// if the group hasn't set anything check if there is a site setting for groups
		if ($group_access === false) {
			$site_group_access = elgg_get_plugin_setting("group_default_access", "group_tools");
			if ($site_group_access !== null) {
				switch ($site_group_access) {
					case GROUP_TOOLS_GROUP_ACCESS_DEFAULT:
						$result = $page_owner->group_acl;
						break;
					default:
						$result = $site_group_access;
						break;
				}
			}
		}
	}
	
	return $result;
}

/**
 * Changed the content of an input/access
 *
 * @param string $hook         the 'access:collections:write' hook
 * @param string $type         for the 'user' type
 * @param array  $return_value the default values
 * @param array  $params       params to help change the values
 *
 * @return array the new access options
 */
function group_tools_access_write_handler($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	if (elgg_in_context("group_tools_default_access") && !empty($result) && is_array($result)) {
		// unset ACCESS_PRIVATE & ACCESS_FRIENDS;
		if (isset($result[ACCESS_PRIVATE])) {
			unset($result[ACCESS_PRIVATE]);
		}
		
		if (isset($result[ACCESS_FRIENDS])) {
			unset($result[ACCESS_FRIENDS]);
		}
		
		// reverse the array
		$result = array_reverse($result, true);
		
		// add group option
		$result[GROUP_TOOLS_GROUP_ACCESS_DEFAULT] = elgg_echo("group_tools:default:access:group");
	}
	
	return $result;
}

/**
 * Allow a group to be transfered by the correct user
 *
 * @param string $hook         the 'permissions_check' hook
 * @param string $type         for the 'group' type
 * @param bool   $return_value is the current user allowed to perform the action
 * @param array  $params       params to help chnage the return value
 *
 * @return bool true if we allow admin transfer
 */
function group_tools_admin_transfer_permissions_hook($hook, $type, $return_value, $params) {
	$result = $return_value;

	if (!$result && !empty($params) && is_array($params)) {
		$group = elgg_extract("entity", $params);
		if (!empty($group) && elgg_instanceof($group, "group")) {
			$result = true;
		}
	}

	return $result;
}

/**
 * A prepend hook to the groups/join action
 *
 * @param string $hook         'action'
 * @param string $type         'groups/join'
 * @param bool   $return_value true, return false to stop the action
 * @param null   $params       passed on params
 *
 * @return bool
 */
function group_tools_join_group_action_handler($hook, $type, $return_value, $params) {
	// hacky way around a short comming of Elgg core to allow users to join a group
	if (group_tools_domain_based_groups_enabled()) {
		elgg_register_plugin_hook_handler("permissions_check", "group", "group_tools_permissions_check_groups_join_hook");
	}
}

/**
 * A hook on the ->canEdit() of a group. This is done to allow e-mail domain users to join a group
 *
 * Note: this is a very hacky way arround a short comming of Elgg core
 *
 * @param string $hook         'permissions_check'
 * @param string $type         'group'
 * @param bool   $return_value is the current user allowed to edit the group
 * @param mixed  $params       passed on params
 *
 * @return bool
 */
function group_tools_permissions_check_groups_join_hook($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	if (!$result && group_tools_domain_based_groups_enabled()) {
		// domain based groups are enabled, lets check if this user is allowed to join based on that
		if (!empty($params) && is_array($params)) {
			$group = elgg_extract("entity", $params);
			$user = elgg_extract("user", $params);
			
			if (!empty($group) && elgg_instanceof($group, "group") && !empty($user) && elgg_instanceof($user ,"user")) {
				if (group_tools_check_domain_based_group($group, $user)) {
					$result = true;
				}
			}
		}
	}
	
	return $result;
}

/**
 * A hook to extend the owner block of groups
 *
 * @param string         $hook         'register'
 * @param string         $type         'menu:owner_block'
 * @param ElggMenuItem[] $return_value the current menu items
 * @param mixed          $params       passed on params
 *
 * @return ElggMenuItem[]
 */
function group_tools_register_owner_block_menu_handler($hook, $type, $return_value, $params) {
	$result = $return_value;
	
	if (!empty($params) && is_array($params)) {
		$entity = elgg_extract("entity", $params);
		
		if (!empty($entity) && elgg_instanceof($entity, "group")) {
			if ($entity->related_groups_enable == "yes") {
				$result[] = ElggMenuItem::factory(array(
					"name" => "related_groups",
					"text" => elgg_echo("group_tools:related_groups:title"),
					"href" => "groups/related/" . $entity->getGUID(),
					"is_trusted" => true
				));
			}
		}
	}
	
	return $result;
}
