<?php 

	global $GROUP_TOOLS_PAGE_HANDLER_BACKUP;
	$GROUP_TOOLS_PAGE_HANDLER_BACKUP = array();

	function group_tools_extend_page_handler($handler, $function){
		elgg_deprecated_notice("don't use: register", 1.7);
		
		global $GROUP_TOOLS_PAGE_HANDLER_BACKUP;
		global $CONFIG;
		
		$result = false;
		
		if(!empty($handler) && !empty($function) && is_callable($function)){
			if(isset($CONFIG->pagehandler) && array_key_exists($handler, $CONFIG->pagehandler)){
				// backup original page handler
				$GROUP_TOOLS_PAGE_HANDLER_BACKUP[$handler] = $CONFIG->pagehandler[$handler];
				// register new handler
				register_page_handler($handler, $function);
				$result = true;
			} else {
				register_page_handler($handler, $function);
				$result = true;
			}
		}
		
		return $result;
	}
	
	function group_tools_fallback_page_handler($page, $handler){
		elgg_deprecated_notice("don't use: fallback", 1.7);
		global $GROUP_TOOLS_PAGE_HANDLER_BACKUP;
		
		$result = false;
		
		if(!empty($handler)){
			if(array_key_exists($handler, $GROUP_TOOLS_PAGE_HANDLER_BACKUP)){
				if(is_callable($GROUP_TOOLS_PAGE_HANDLER_BACKUP[$handler])){
					$function = $GROUP_TOOLS_PAGE_HANDLER_BACKUP[$handler];
					
					$result = $function($page, $handler);
					
					if($result !== false){
						$result = true;
					}
				}
			}
		}
		
		return $result;
	}

	function group_tools_groups_page_handler($page, $handler){
		elgg_deprecated_notice("don't use: handler", 1.7);
		switch($page[0]){
			case "mail":
				if(!empty($page[1])){
					set_input("group_guid", $page[1]);
					include(dirname(dirname(__FILE__)) . "/pages/mail.php");
					break;
				}
			case "invite":
				if(!empty($page[1])){
					set_input("group_guid", $page[1]);
					include(dirname(dirname(__FILE__)) . "/pages/groups/invite.php");
					break;
				}
			case "group_invite_autocomplete":	
				include(dirname(dirname(__FILE__)) . "/procedures/group_invite_autocomplete.php");
				break;
			default:
				return group_tools_fallback_page_handler($page, $handler);
				break;
		}
	}
	
	function group_tools_check_group_email_invitation($invite_code, $group_guid = 0){
		$result = false;
		
		if(!empty($invite_code)){
			$options = array(
				"type" => "group",
				"limit" => 1,
				"site_guids" => false,
				"annotation_name_value_pairs" => array("email_invitation" => $invite_code)
			);
			
			if(!empty($group_guid)){
				$options["annotation_owner_guids"] = array($group_guid);
			}
			
			if($groups = elgg_get_entities_from_annotations($options)){
				$result = $groups[0];
			}
		}
		
		return $result;
	}
	
	function group_tools_invite_user(ElggGroup $group, ElggUser $user, $text = "", $resend = false){
		global $CONFIG;
		
		$result = false;
		
		if(!empty($user) && ($user instanceof ElggUser) && !empty($group) && ($group instanceof ElggGroup) && ($loggedin_user = get_loggedin_user())){
			if(!$resend){
				// Create relationship
				add_entity_relationship($group->getGUID(), "invited", $user->getGUID());
			}
			
			// Send email
			$url = $CONFIG->url . "pg/groups/invitations/" . $user->username;
				
			$subject = sprintf(elgg_echo("groups:invite:subject"), $user->name, $group->name);
			$msg = sprintf(elgg_echo("group_tools:groups:invite:body"), $user->name, $loggedin_user->name, $group->name, $text, $url);
			
			if(notify_user($user->getGUID(), $group->getOwner(), $subject, $msg)){
				$result = true;
			}
		}
		
		return $result;
	}
	
	function group_tools_add_user(ElggGroup $group, ElggUser $user, $text = ""){
		$result = false;
		
		if(!empty($user) && ($user instanceof ElggUser) && !empty($group) && ($group instanceof ElggGroup) && ($loggedin_user = get_loggedin_user())){
			if($group->join($user)){
				// Remove any invite or join request flags
				remove_entity_relationship($group->getGUID(), "invited", $user->getGUID());
				remove_entity_relationship($user->getGUID(), "membership_request", $group->getGUID());
					
				// notify user
				$subject = sprintf(elgg_echo("group_tools:groups:invite:add:subject"), $group->name);
				$msg = sprintf(elgg_echo("group_tools:groups:invite:add:body"), $user->name, $loggedin_user->name, $group->name, $text, $group->getURL());
					
				if(notify_user($user->getGUID(), $group->getOwner(), $subject, $msg)){
					$result = true;
				}
			}
		}
		
		return $result;
	}
	
	function group_tools_invite_email(ElggGroup $group, $email, $text = "", $resend = false){
		global $CONFIG;
		
		$result = false;

		if(!empty($group) && ($group instanceof ElggGroup) && !empty($email) && is_email_address($email) && ($loggedin_user = get_loggedin_user())){
			// get site secret
			$site_secret = get_site_secret();
			
			// generate invite code
			$invite_code = md5($site_secret . $email . $group->getGUID());
			
			if(!group_tools_check_group_email_invitation($invite_code, $group->getGUID()) || $resend){
				// make site email
				if(!empty($CONFIG->site->email)){
					if(!empty($CONFIG->site->name)){
						$site_from = $CONFIG->site->name . " <" . $CONFIG->site->email . ">";
					} else {
						$site_from = $CONFIG->site->email;
					}
				} else {
					// no site email, so make one up
					if(!empty($CONFIG->site->name)){
						$site_from = $CONFIG->site->name . " <noreply@" . get_site_domain($CONFIG->site_guid) . ">";
					} else {
						$site_from = "noreply@" . get_site_domain($CONFIG->site_guid);
					}
				}
				
				if(!$resend){
					// register invite with group
					$group->annotate("email_invitation", $invite_code, ACCESS_LOGGED_IN, $group->getGUID());
				}
				
				// make subject
				$subject = sprintf(elgg_echo("group_tools:groups:invite:email:subject"), $group->name);
				
				// make body
				$body = sprintf(elgg_echo("group_tools:groups:invite:email:body"),
					$loggedin_user->name,
					$group->name,
					$CONFIG->site->name,
					$text,
					$CONFIG->site->name,
					$CONFIG->wwwroot . "pg/register",
					$CONFIG->wwwroot . "pg/groups/invitations/?invitecode=" . $invite_code,
					$invite_code);
				
				if(is_plugin_enabled("html_email_handler") && (get_plugin_setting("notifications", "html_email_handler") == "yes")){
					// generate HTML mail body
					$html_message = elgg_view("html_email_handler/notification/body", array("title" => $subject, "message" => parse_urls($body)));
					if(defined("XML_DOCUMENT_NODE")){
						if($transform = html_email_handler_css_inliner($html_message)){
							$html_message = $transform;
						}
					}
				
					// set options for sending
					$options = array(
						"to" => $email,
						"from" => $site_from,
						"subject" => $subject,
						"html_message" => $html_message,
						"plaintext_message" => $body
					);
					
					if(html_email_handler_send_email($options)){
						$result = true;
					}
				} else {
					// use plaintext mail
					$headers = "From: " . $site_from . PHP_EOL;
					$headers .= "X-Mailer: PHP/" . phpversion() . PHP_EOL;
					$headers .= "Content-Type: text/plain; charset=\"utf-8\"" . PHP_EOL;
						
					if(mail($email, $subject, $body, $headers)){
						$result = true;
					}
				}
			} else {
				$result = null;
			}
		}
		
		return $result;
	}

	function group_tools_verify_group_members($group_guid, $user_guids){
		$result = false;
		
		if(!empty($group_guid) && !empty($user_guids)){
			if(!is_array($user_guids)){
				$user_guids = array($user_guids);
			}
			
			if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup)){
				$options = array(
					"type" => "user",
					"limit" => false,
					"relationship" => "member",
					"relationship_guid" => $group->getGUID(),
					"inverse_relationship" => true,
					"callback" => "group_tools_guid_only_callback"
				);
				
				if($member_guids = elgg_get_entities_from_relationship($options)){
					$result = array();
					
					foreach($user_guids as $user_guid){
						if(in_array($user_guid, $member_guids)){
							$result[] = $user_guid;
						}
					}
				}
			}
		}
		
		return $result;
	}
	
	function group_tools_guid_only_callback($row){
		return $row->guid;
	}