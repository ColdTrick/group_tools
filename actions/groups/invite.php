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
	
	$csv = get_uploaded_file("csv");
	
	$submit = get_input("submit");
	
	if ((!empty($user_guids) || !empty($emails) || !empty($csv)) && ($group = get_entity($group_guid))){
		if(($group instanceof ElggGroup) && $group->canEdit()){
			// show hidden (unvalidated) users
			$hidden = access_get_show_hidden_status();
			access_show_hidden_entities(true);
			
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
									if (group_tools_invite_user($group, $user, $text)) {
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
								if(group_tools_add_user($group, $user, $text)){
									$join++;
								}
							} else {
								$member++;
							}
						}
					}
					
					if(!empty($join)){
						if(empty($member)){
							system_message(sprintf(elgg_echo("group_tools:action:groups:invite:add:success"), $join));
						} else {
							system_message(sprintf(elgg_echo("group_tools:action:groups:invite:add:success:other"), $join, $member));
						}
					} else {
						register_error(sprintf(elgg_echo("group_tools:action:groups:invite:add:error:no_add"), $member));
					}
				}
			}
			
			// Invite member by e-mail address
			if(!empty($emails)){
				$sent = 0;
				$already = 0;
				
				foreach($emails as $email){
					$invite_result = group_tools_invite_email($group, $email, $text);
					if($invite_result === true){
						$sent++;
					} elseif($invite_result === null){
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
			
			// invite from csv
			if(!empty($csv)){
				$file_location = $_FILES["csv"]["tmp_name"];
				
				$csv_added = 0;
				$csv_invited = 0;
				$csv_already_invited = 0;
				$csv_already_member = 0;
				
				if($fh = fopen($file_location, "r")){
					while(($data = fgetcsv($fh, 0, ";")) !== false){
						/*
						 * data structure
						 * data[0] => displayname
						 * data[1] => e-mail address
						 */
						$email = "";
						if(isset($data[1])){
							$email = trim($data[1]);
						}
						
						if(!empty($email) && is_email_address($email)){
							if($users = get_user_by_email($email)){
								// found a user with this email on the site, so invite (or add)
								$user = $users[0];
								
								if(!$group->isMember($user)){
									if (!check_entity_relationship($group->getGUID(), "invited", $user->getGUID())) {
										if($submit != elgg_echo("group_tools:add_users")){
											// invite user
											if(group_tools_invite_user($group, $user, $text)){
												$csv_invited++;
											}
										} else {
											if(group_tools_add_user($group, $user, $text)){
												$csv_added++;
											}
										}
									} else {
										$csv_already_invited++;
									}
								} else {
									$csv_already_member++;
								}
							} else {
								// user not found so invite based on email address
								$invite_result = group_tools_invite_email($group, $email, $text);
								
								if($invite_result === true){
									$csv_invited++;
								} elseif($invite_result === null){
									$csv_already_invited++;
								}
							}
						}
					}
				}
				
				if(!empty($csv_added) || !empty($csv_invited)){
					if(empty($csv_already_invited) && empty($csv_already_member)){
						system_message(sprintf(elgg_echo("group_tools:action:group:invite:success"), ($csv_added + $csv_invited)));
					} else {
						system_message(sprintf(elgg_echo("group_tools:action:group:invite:success:other"), ($csv_added + $csv_invited), $csv_already_member, $csv_already_invited));
					}
				} else {
					register_error(sprintf(elgg_echo("group_tools:action:group:invite:csv:error:no_invites"), $csv_already_member, $csv_already_invited));
				}
			}
			
			// restore hidden users
			access_show_hidden_entities($hidden);
		} else {
			register_error(elgg_echo("group_tools:action:error:edit"));
		}
	} else {
		register_error(elgg_echo("group_tools:action:error:input"));
	}
	
	forward(REFERER);

?>
