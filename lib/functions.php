<?php 

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
		$result = false;
		
		if(!empty($user) && ($user instanceof ElggUser) && !empty($group) && ($group instanceof ElggGroup) && ($loggedin_user = elgg_get_logged_in_user_entity())){
			if(!$resend){
				// Create relationship
				add_entity_relationship($group->getGUID(), "invited", $user->getGUID());
			}
			
			// Send email
			$url = elgg_get_site_url() . "/groups/invitations/" . $user->username;
			
			$subject = elgg_echo("groups:invite:subject", array($user->name, $group->name));
			$msg = elgg_echo("group_tools:groups:invite:body", array($user->name, $loggedin_user->name, $group->name, $text, $url));
			
			if(notify_user($user->getGUID(), $group->getOwnerGUID(), $subject, $msg)){
				$result = true;
			}
		}
		
		return $result;
	}
	
	function group_tools_add_user(ElggGroup $group, ElggUser $user, $text = ""){
		$result = false;
		
		if(!empty($user) && ($user instanceof ElggUser) && !empty($group) && ($group instanceof ElggGroup) && ($loggedin_user = elgg_get_logged_in_user_entity())){
			if($group->join($user)){
				// Remove any invite or join request flags
				remove_entity_relationship($group->getGUID(), "invited", $user->getGUID());
				remove_entity_relationship($user->getGUID(), "membership_request", $group->getGUID());
					
				// notify user
				$subject = elgg_echo("group_tools:groups:invite:add:subject", array($group->name));
				$msg = elgg_echo("group_tools:groups:invite:add:body", array($user->name, $loggedin_user->name, $group->name, $text, $group->getURL()));
					
				if(notify_user($user->getGUID(), $group->getOwnerGUID(), $subject, $msg)){
					$result = true;
				}
			}
		}
		
		return $result;
	}
	
	function group_tools_invite_email(ElggGroup $group, $email, $text = "", $resend = false){
		$result = false;

		if(!empty($group) && ($group instanceof ElggGroup) && !empty($email) && is_email_address($email) && ($loggedin_user = elgg_get_logged_in_user_entity())){
			// get site secret
			$site_secret = get_site_secret();
			
			// generate invite code
			$invite_code = md5($site_secret . $email . $group->getGUID());
			
			if(!group_tools_check_group_email_invitation($invite_code, $group->getGUID()) || $resend){
				// make site email
				$site = elgg_get_site_entity();
				if(!empty($site->email)){
					if(!empty($site->name)){
						$site_from = $site->name . " <" . $site->email . ">";
					} else {
						$site_from = $site->email;
					}
				} else {
					// no site email, so make one up
					if(!empty($site->name)){
						$site_from = $site->name . " <noreply@" . get_site_domain($site->getGUID()) . ">";
					} else {
						$site_from = "noreply@" . get_site_domain($site->getGUID());
					}
				}
				
				if(!$resend){
					// register invite with group
					$group->annotate("email_invitation", $invite_code, ACCESS_LOGGED_IN, $group->getGUID());
				}
				
				// make subject
				$subject = elgg_echo("group_tools:groups:invite:email:subject", array($group->name));
				
				// make body
				$body = elgg_echo("group_tools:groups:invite:email:body", array(
					$loggedin_user->name,
					$group->name,
					$site->name,
					$text,
					$site->name,
					elgg_get_site_url() . "register",
					elgg_get_site_url() . "groups/invitations/?invitecode=" . $invite_code,
					$invite_code)
				);
				
				if(elgg_is_active_plugin("html_email_handler") && (elgg_get_plugin_setting("notifications", "html_email_handler") == "yes")){
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
	
	/**
	 * Check if group creation is limited to site administrators
	 * Also this function caches the result
	 * 
	 * @return boolean
	 */
	function group_tools_is_group_creation_limited(){
		static $result;
		
		if(!isset($result)){
			$result = false;
			
			if(elgg_get_plugin_setting("admin_create", "group_tools") == "yes"){
				$result = true;
			}
		}
		
		return $result;
	}