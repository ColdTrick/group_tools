<?php 

	global $GROUP_TOOLS_ACL;

	gatekeeper();
	
	$group_guid = (int) get_input("group_guid", 0);
	$owner_guid = (int) get_input("owner_guid", 0);
	
	$loggedin_user = get_loggedin_user();
	$forward_url = REFERER;
	
	if(!empty($group_guid) && !empty($owner_guid)){
		if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && ($new_owner = get_user($owner_guid))){
			if(($group->getOwner() == $loggedin_user->getGUID()) || $loggedin_user->isAdmin()){
				if($group->getOwner() != $new_owner->getGUID()){
					$old_owner = $group->getOwnerEntity();
					
					// transfer ownership
					$group->owner_guid = $new_owner->getGUID();
					$group->container_guid = $new_owner->getGUID();
					
					// make sure user is added to correct acl
					register_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
					$GROUP_TOOLS_ACL = $group->group_acl;
					
					$group->join($new_owner);
					
					// cleanup hook
					unregister_plugin_hook("access:collections:write", "user", "group_tools_add_user_acl_hook");
					
					if($group->save()){
						$forward_url = $group->getURL();
						
						// check for group icon
						if(!empty($group->icontime)){
							$prefix = "groups/" . $group->getGUID();
							
							$sizes = array("", "tiny", "small", "medium", "large");
							
							$ofh = new ElggFile();
							$ofh->owner_guid = $old_owner->getGUID();
							
							$nfh = new ElggFile();
							$nfh->owner_guid = $group->getOwner();
							
							foreach($sizes as $size){
								// set correct file to handle
								$ofh->setFilename($prefix . $size . ".jpg");
								$nfh->setFilename($prefix . $size . ".jpg");
								
								// open files
								$ofh->open("read");
								$nfh->open("write");
								
								// copy file
								$nfh->write($ofh->grabFile());
								
								// close file
								$ofh->close();
								$nfh->close();
								
								// cleanup old file
								$ofh->delete();
							}
							
							$group->icontime = time();
						}
						
						// move metadata of the group to the new owner
						if($metadata = get_metadata_for_entity($group->getGUID())){
							foreach($metadata as $md){
								if($md->owner_guid == $old_owner->getGUID()){
									$md->owner_guid = $new_owner->getGUID();
									$md->save();
								}
							}
						}
						
						// notify new owner
						if($new_owner->getGUID() != $loggedin_user->getGUID()){
							$subject = sprintf(elgg_echo("group_tools:notify:transfer:subject"), $group->name);
							$message = sprintf(elgg_echo("group_tools:notify:transfer:message"), 
												$new_owner->name,
												$loggedin_user->name,
												$group->name,
												$group->getURL());
							
							notify_user($new_owner->getGUID(), $group->getGUID(), $subject, $message);
						}
						
						// success message
						system_message(sprintf(elgg_echo("group_tools:action:admin_transfer:success"), $new_owner->name));
					} else {
						register_error(elgg_echo("group_tools:action:admin_transfer:error:save"));
					}
				} else {
					register_error(elgg_echo("group_tools:action:admin_transfer:error:self"));
				}
			} else {
				register_error(elgg_echo("group_tools:action:admin_transfer:error:access"));
			}
		} else {
			register_error(elgg_echo("group_tools:action:error:entities"));
		}
	} else {
		register_error(elgg_echo("group_tools:action:error:input"));
	}

	forward($forward_url);
	
?>