<?php 

	require_once(dirname(__FILE__) . "/lib/functions.php");
	require_once(dirname(__FILE__) . "/lib/events.php");
	require_once(dirname(__FILE__) . "/lib/hooks.php");

	function group_tools_init(){
		
		if(is_plugin_enabled("groups")){
			// extend css & js
			elgg_extend_view("css", "group_tools/css");
			elgg_extend_view("js/initialise_elgg", "group_tools/js");
			
			// extend groups page handler
			group_tools_extend_page_handler("groups", "group_tools_groups_page_handler");
			
			if(get_plugin_setting("multiple_admin", "group_tools") == "yes"){
				// add group tool option
				add_group_tool_option("group_multiple_admin_allow", elgg_echo("group_tools:multiple_admin:group_tool_option"), false);
				
				// register permissions check hook
				register_plugin_hook("permissions_check", "group", "group_tools_multiple_admin_can_edit_hook");
				
				// register on group leave
				register_elgg_event_handler("leave", "group", "group_tools_multiple_admin_group_leave");
			}
			
			// register group activity widget
			add_widget_type("group_river_widget", elgg_echo("widgets:group_river_widget:title"), elgg_echo("widgets:group_river_widget:description"), "dashboard,profile,index,groups", true);
			
			// register group members widget
			add_widget_type("group_members", elgg_echo("widgets:group_members:title"), elgg_echo("widgets:group_members:description"), "groups", false);
			if(is_callable("add_widget_title_link")){
				add_widget_title_link("group_members", "[BASEURL]pg/groups/memberlist/[GUID]");
			}
			
			// register groups invitations widget
			add_widget_type("group_invitations", elgg_echo("widgets:group_invitations:title"), elgg_echo("widgets:group_invitations:description"), "index,dashboard", false);
			if(is_callable("add_widget_title_link")){
				add_widget_title_link("group_invitations", "[BASEURL]pg/groups/invitations/[USERNAME]");
			}
			
			// group invitation
			register_action("groups/invite", false, dirname(__FILE__) . "/actions/groups/invite.php");
			register_action("groups/joinrequest", false, dirname(__FILE__) . "/actions/groups/joinrequest.php");
			
			// auto enable group notifications on group join
			if(get_plugin_setting("auto_notification", "group_tools") == "yes"){
				register_elgg_event_handler("join", "group", "group_tools_join_group_event");
			}
			
			// manage auto join for groups
			elgg_extend_view("forms/groups/edit", "group_tools/forms/auto_join", 400);
			register_elgg_event_handler("create", "member_of_site", "group_tools_join_site_handler");
		}
		
		if(isadminloggedin()){
			run_function_once("group_tools_version_1_3");
		}
	}
	
	function group_tools_pagesetup(){
		global $CONFIG;
		
		$user = get_loggedin_user();
		$page_owner = page_owner_entity();
		$context = get_context();
		
		if(($context == "groups") && ($page_owner instanceof ElggGroup)){
			// replace submenu
			group_tools_replace_submenu();
			
			if(!empty($user)){
				// extend profile actions
				elgg_extend_view("profile/menu/actions", "group_tools/profile_actions");
				
				// check for admin transfer
				$admin_transfer = get_plugin_setting("admin_transfer", "group_tools");
				
				if(($admin_transfer == "admin") && $user->isAdmin()){
					elgg_extend_view("forms/groups/edit", "group_tools/forms/admin_transfer", 400);
				} elseif(($admin_transfer == "owner") && (($page_owner->getOwner() == $user->getGUID()) || $user->isAdmin())){
					elgg_extend_view("forms/groups/edit", "group_tools/forms/admin_transfer", 400);
				}
				
				// check multiple admin
				if(get_plugin_setting("multiple_admin", "group_tools") == "yes"){
					// extend group members sidebar list
					elgg_extend_view("groups/members", "group_tools/group_admins", 400);
					
					// remove group tool options for group admins
					if(($page_owner->getOwner() != $user->getGUID()) && !$user->isAdmin()){
						remove_group_tool_option("group_multiple_admin_allow");
					}
				}
				
				// invitation management
				if($page_owner->canEdit()){
					$request_options = array(
						"type" => "user",
						"relationship" => "membership_request", 
						"relationship_guid" => $page_owner->getGUID(), 
						"inverse_relationship" => true, 
						"count" => true
					);
					if($requests = elgg_get_entities_from_relationship($request_options)){
						$postfix = " [" . $requests . "]";
					}
					add_submenu_item(elgg_echo("group_tools:menu:membership") . $postfix, $CONFIG->wwwroot . "pg/groups/membershipreq/" . $page_owner->getGUID(), '1groupsactions');
				}
				
				// group mail options
				if ($page_owner->canEdit() && (get_plugin_setting("mail", "group_tools") == "yes")) {
					add_submenu_item(elgg_echo("group_tools:menu:mail"), $CONFIG->wwwroot . "pg/groups/mail/" . $page_owner->getGUID(), "1groupsactions");
				}
			}	
		}
		
		if($page_owner instanceof ElggGroup){
			if($page_owner->membership != ACCESS_PUBLIC){
				if(get_plugin_setting("search_index", "group_tools") != "yes"){
					// closed groups should be indexed by search engines
					elgg_extend_view("metatags", "metatags/noindex");
				}
			}
		}
		
	}
	
	function group_tools_version_1_3(){
		global $CONFIG, $GROUP_TOOLS_ACL;
		
		$query = "SELECT ac.id AS acl_id, ac.owner_guid AS group_guid, er.guid_one AS user_guid
			FROM {$CONFIG->dbprefix}access_collections ac
			JOIN {$CONFIG->dbprefix}entities e ON e.guid = ac.owner_guid
			JOIN {$CONFIG->dbprefix}entity_relationships er ON ac.owner_guid = er.guid_two
			WHERE e.type = 'group'
			AND er.relationship = 'member'
			AND er.guid_one NOT IN 
			(
			SELECT acm.user_guid
			FROM {$CONFIG->dbprefix}access_collections ac2
			JOIN {$CONFIG->dbprefix}access_collection_membership acm ON ac2.id = acm.access_collection_id
			WHERE ac2.owner_guid = ac.owner_guid
			)";
		
		if($data = get_data($query)){
			register_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
			
			foreach($data as $row){
				$GROUP_TOOLS_ACL = $row->acl_id;
				
				add_user_to_access_collection($row->user_guid, $row->acl_id);
			}
			
			unregister_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
		}
		
	}
	
	// default elgg event handlers
	register_elgg_event_handler("init", "system", "group_tools_init");
	register_elgg_event_handler("pagesetup", "system", "group_tools_pagesetup");

	// actions
	register_action("group_tools/admin_transfer", false, dirname(__FILE__) . "/actions/admin_transfer.php");
	register_action("group_tools/toggle_admin", false, dirname(__FILE__) . "/actions/toggle_admin.php");
	register_action("group_tools/kick", false, dirname(__FILE__) . "/actions/kick.php");
	register_action("group_tools/mail", false, dirname(__FILE__) . "/actions/mail.php");
	register_action("group_tools/toggle_auto_join", false, dirname(__FILE__) . "/actions/toggle_auto_join.php", true);
	register_action("group_tools/fix_auto_join", false, dirname(__FILE__) . "/actions/fix_auto_join.php", true);
	register_action("groups/email_invitation", false, dirname(__FILE__) . "/actions/groups/email_invitation.php");
	
	