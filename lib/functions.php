<?php 

	global $GROUP_TOOLS_PAGE_HANDLER_BACKUP;
	$GROUP_TOOLS_PAGE_HANDLER_BACKUP = array();

	function group_tools_extend_page_handler($handler, $function){
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
			case "membershipreq":
				if(!empty($page[1])){
					set_input("group_guid", $page[1]);
					include(dirname(dirname(__FILE__)) . "/pages/groups/membershipreq.php");
					break;
				}
			case "group_invite_autocomplete":	
				include(dirname(dirname(__FILE__)) . "/procedures/group_invite_autocomplete.php");
				break;
			case "all":
				include(dirname(dirname(__FILE__)) . "/pages/groups/all.php");
				break;
			default:
				return group_tools_fallback_page_handler($page, $handler);
				break;
		}
	}
	
	function group_tools_replace_submenu(){
		global $CONFIG;
		
		$page_owner = page_owner_entity();
		
		$titles = array(
			elgg_echo("groups:invite") => elgg_echo("group_tools:groups:invite")
		);
		
		$urls = array();
		
		$remove = array(
			elgg_echo("groups:membershiprequests") => $CONFIG->wwwroot . "mod/groups/membershipreq.php?group_guid=" . $page_owner->getGUID()
		);
		
		if(!empty($CONFIG->submenu)){
			$submenu = $CONFIG->submenu;
			
			foreach($submenu as $group => $items){
				if(!empty($items)){
					foreach($items as $index => $item){
						// replace menu titles
						if(array_key_exists($item->name, $titles)){
							$submenu[$group][$index]->name = $titles[$item->name];
						}
						
						// replace urls
						if(array_key_exists($item->name, $urls)){
							$submenu[$group][$index]->value = $urls[$item->name];
						}
						
						// remove items
						if(array_key_exists($item->name, $remove) && ($item->value == $remove[$item->name])){
							unset($submenu[$group][$index]);
						}
					}
				} else {
					unset($submenu[$group]);
				}
			}
			
			$CONFIG->submenu = $submenu;
			
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

?>