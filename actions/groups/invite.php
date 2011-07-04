<?php
	
	/**
	 * Invite a user to join a group
	 *
	 * @package ElggGroups
	 */
	
	// Load configuration
	global $CONFIG;
	
	gatekeeper();
	
	$logged_in_user = get_loggedin_user();
	
	$user_guids = get_input("user_guid");
	if (!empty($user_guids) && !is_array($user_guids)){
		$user_guids = array($user_guids);
	}
	$group_guid = (int) get_input("group_guid");
	$text = get_input("comment");
	
	$emails = get_input("user_guid_email");
	if(!empty($emails) && !is_array($emails)){
		$emails = array($emails);
	}
	
	$submit = get_input("submit");
	
	if ((!empty($user_guids) || !empty($emails)) && ($group = get_entity($group_guid))){
		if(($group instanceof ElggGroup) && $group->canEdit()){
			// invite existing users
			if(!empty($user_guids)){
				if($submit != elgg_echo("group_tools:add_users")){
					// invite users
					$already_invited = 0;
					$new_invited = 0;
					$member = 0;
					
					foreach ($user_guids as $u_id) {
						
						if ($user = get_user($u_id)) {
							if(!$group->isMember($user)){
								if (!check_entity_relationship($group->getGUID(), "invited", $user->getGUID())) {
									// Create relationship
									add_entity_relationship($group->getGUID(), "invited", $user->getGUID());
				
									// Send email
									$url = $CONFIG->url . "pg/groups/invitations/" . $user->username;
									
									$subject = sprintf(elgg_echo("groups:invite:subject"), $user->name, $group->name);
									$msg = sprintf(elgg_echo("group_tools:groups:invite:body"), $user->name, $logged_in_user->name, $group->name, $text, $url);
									
									if (notify_user($user->getGUID(), $group->getOwner(), $subject, $msg)) {
										$new_invited++;
									}
								} else {
									$already_invited++;
								}
							} else {
								$member++;
							}
						}
					}
					
					if(!empty($new_invited)){
						if(empty($already_invited) && empty($member)){
							system_message(sprintf(elgg_echo("group_tools:action:group:invite:success"), $new_invited));
						} else {
							system_message(sprintf(elgg_echo("group_tools:action:group:invite:success:other"), $new_invited, $member, $already_invited));
						}
					} else {
						register_error(sprintf(elgg_echo("group_tools:action:group:invite:error:no_invites"), $member, $already_invited));
					}
				} else {
					$join = 0;
					$member = 0;
					
					// add users directly
					foreach($user_guid as $u_id){
						if($user = get_user($u_id)){
							if(!$group->isMember($user)){
								if($group->join($user)){
									// Remove any invite or join request flags
									remove_entity_relationship($group->getGUID(), "invited", $user->getGUID());
									remove_entity_relationship($user->getGUID(), "membership_request", $group->getGUID());
									
									// notify user
									$subject = sprintf(elgg_echo("group_tools:groups:invite:add:subject"), $group->name);
									$msg = sprintf(elgg_echo("group_tools:groups:invite:add:body"), $user->name, $logged_in_user->name, $group->name, $text, $group->getURL());
									
									if(notify_user($user->getGUID(), $group->getOwner(), $subject, $msg)){
										$join++;
									}
								}
							} else {
								$member++;
							}
						}
					}
					
					if(!empty($join)){
						if(empty($member)){
							system_message(sprintf(elgg_echo("group_tools:groups:invite:add:success"), $join));
						} else {
							system_message(sprintf(elgg_echo("group_tools:groups:invite:add:success:other"), $join, $member));
						}
					} else {
						register_error(sprintf(elgg_echo("group_tools:groups:invite:add:error:no_add"), $member));
					}
				}
			}
			
			// Invite member by e-mail address
			if(!empty($emails)){
				$sent = 0;
				$already = 0;
				
				$site_secret = get_site_secret();
				$register_url = $CONFIG->wwwroot . "pg/register";
				$invitations_url = $CONFIG->wwwroot . "pg/groups/invitations/?invitecode=";
				
				$subject = sprintf(elgg_echo("group_tools:groups:invite:email:subject"), $group->name);
				
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
				
				foreach($emails as $email){
					$invite_code = md5($site_secret . $email . $group->getGUID());
					
					if(!group_tools_check_group_email_invitation($invite_code, $group->getGUID())){
						$group->annotate("email_invitation", $invite_code, ACCESS_LOGGED_IN, $group->getGUID());
						
						$body = sprintf(elgg_echo("group_tools:groups:invite:email:body"), 
											$logged_in_user->name,
											$group->name, 
											$CONFIG->site->name, 
											$text,
											$CONFIG->site->name, 
											$register_url, 
											$invitations_url . $invite_code,
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
								$sent++;
							}
						} else {
							// use plaintext mail
							$headers = "From: " . $site_from . PHP_EOL;
							$headers .= "X-Mailer: PHP/" . phpversion() . PHP_EOL;
							$headers .= "Content-Type: text/plain; charset=\"utf-8\"" . PHP_EOL;
							
							if(mail($email, $subject, $body, $headers)){
								$sent++;
							}
						}
					} else {
						$already++;
					}
				}
				
				// system message
				if(!empty($sent)){
					if(empty($already)){
						system_message(sprintf(elgg_echo("group_tools:action:group:invite:email:success"), $sent));
					} else {
						system_message(sprintf(elgg_echo("group_tools:action:group:invite:email:success:other"), $sent, $already));
					}
				} else {
					register_error(sprintf(elgg_echo("group_tools:action:group:invite:email:error:no_invites"), count($emails), $already));
				}
			}
		} else {
			register_error(elgg_echo("group_tools:action:error:edit"));
		}
	} else {
		register_error(elgg_echo("group_tools:action:error:input"));
	}
	
	forward(REFERER);

?>
