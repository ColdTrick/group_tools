<?php

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