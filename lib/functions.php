<?php
/**
 * All helper functions for this plugin can be found here.
 *
 * @package group_tools
 */

/**
 * Check if a invitation code results in a group
 *
 * @param string $invite_code the invite code
 * @param int    $group_guid  (optional) the group to check
 *
 * @return false|ElggGroup
 */
function group_tools_check_group_email_invitation($invite_code, $group_guid = 0) {
	
	if (empty($invite_code)) {
		return false;
	}
	
	$group_guid = sanitize_int($group_guid, false);
	
	// note not using elgg_get_entities_from_annotations
	// due to performance issues with LIKE wildcard search
	// prefetch metastring ids for use in lighter joins instead
	$name_id = elgg_get_metastring_id('email_invitation');
	$code_id = elgg_get_metastring_id($invite_code);
	$sanitized_invite_code = sanitize_string($invite_code);
	
	$options = [
		'limit' => 1,
		'wheres' => [
			"n_table.name_id = {$name_id} AND (n_table.value_id = {$code_id} OR v.string LIKE '{$sanitized_invite_code}|%')",
		],
	];
	
	if (!empty($group_guid)) {
		$options['annotation_owner_guids'] = [$group_guid];
	}
	
	// find hidden groups
	$ia = elgg_set_ignore_access(true);
	
	$annotations = elgg_get_annotations($options);
	if (empty($annotations)) {
		// restore access
		elgg_set_ignore_access($ia);
		
		return false;
	}
	
	$group = $annotations[0]->getEntity();
	if ($group instanceof ElggGroup) {
		// restore access
		elgg_set_ignore_access($ia);
		
		return $group;
	}
	
	// restore access
	elgg_set_ignore_access($ia);
	
	return false;
}

/**
 * Invite a user to a group
 *
 * @param ElggGroup $group  the group to be invited for
 * @param ElggUser  $user   the user to be invited
 * @param string    $text   (optional) extra text in the invitation
 * @param bool      $resend should existing invitations be resend
 *
 * @return bool
 */
function group_tools_invite_user(ElggGroup $group, ElggUser $user, $text = "", $resend = false) {
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	$resend = (bool) $resend;
	
	if (!($user instanceof ElggUser) || !($group instanceof ElggGroup) || empty($loggedin_user)) {
		return false;
	}
	
	// Create relationship
	$relationship = add_entity_relationship($group->getGUID(), 'invited', $user->getGUID());
	
	if (empty($relationship) && empty($resend)) {
		return false;
	}
	
	// Send email
	$url = elgg_normalize_url("groups/invitations/{$user->username}");
	
	$subject = elgg_echo('groups:invite:subject', [
		$user->name,
		$group->name,
	]);
	$msg = elgg_echo('group_tools:groups:invite:body', [
		$user->name,
		$loggedin_user->name,
		$group->name,
		$text,
		$url,
	]);
	
	if (notify_user($user->getGUID(), $group->getOwnerGUID(), $subject, $msg, [], ['email'])) {
		return true;
	}
	
	return false;
}

/**
 * Add a user to a group
 *
 * @param ElggGroup $group the group to add the user to
 * @param ElggUser  $user  the user to be added
 * @param string    $text  (optional) extra text for the notification
 *
 * @return bool
 */
function group_tools_add_user(ElggGroup $group, ElggUser $user, $text = "") {
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	
	if (!($user instanceof ElggUser) || !($group instanceof ElggGroup) || empty($loggedin_user)) {
		return false;
	}
	
	// make sure all goes well
	$ia = elgg_set_ignore_access(true);
	
	if ($group->join($user)) {
		
		// notify user
		$subject = elgg_echo('group_tools:groups:invite:add:subject', [$group->name]);
		$msg = elgg_echo('group_tools:groups:invite:add:body', [
			$user->name,
			$loggedin_user->name,
			$group->name,
			$text,
			$group->getURL(),
		]);
		
		$params = [
			'group' => $group,
			'inviter' => $loggedin_user,
			'invitee' => $user,
		];
		$msg = elgg_trigger_plugin_hook('invite_notification', 'group_tools', $params, $msg);
			
		if (notify_user($user->getGUID(), $group->getOwnerGUID(), $subject, $msg, [], ['email'])) {
			// restore access
			elgg_set_ignore_access($ia);
			
			return true;
		}
	}
	
	// restore access
	elgg_set_ignore_access($ia);
	
	return false;
}

/**
 * Invite a new user by email to a group
 *
 * @param ElggGroup $group  the group to be invited for
 * @param string    $email  the email address to be invited
 * @param string    $text   (optional) extra text in the invitation
 * @param bool      $resend should existing invitations be resend
 *
 * @return bool|NULL true is invited, false on failure, null when already send
 */
function group_tools_invite_email(ElggGroup $group, $email, $text = "", $resend = false) {
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	$resend = (bool) $resend;
	
	if (!($group instanceof ElggGroup) || empty($email) || !is_email_address($email) || empty($loggedin_user)) {
		return false;
	}
	
	// generate invite code
	$invite_code = group_tools_generate_email_invite_code($group->getGUID(), $email);
	if (empty($invite_code)) {
		return false;
	}
	
	$found_group = group_tools_check_group_email_invitation($invite_code, $group->getGUID());
	if (!empty($found_group) && empty($resend)) {
		return null;
	}
	
	// make site email
	$site = elgg_get_site_entity();
	if (!empty($site->email)) {
		if (!empty($site->name)) {
			$site_from = "{$site->name} <{$site->email}>";
		} else {
			$site_from = $site->email;
		}
	} else {
		// no site email, so make one up
		if (!empty($site->name)) {
			$site_from = "{$site->name} <noreply@{$site->getDomain()}>";
		} else {
			$site_from = "noreply@{$site->getDomain()}";
		}
	}
	
	if (empty($found_group)) {
		// register invite with group
		$group->annotate('email_invitation', "{$invite_code}|{$email}", ACCESS_LOGGED_IN, $group->getGUID());
	}
	
	// make subject
	$subject = elgg_echo('group_tools:groups:invite:email:subject', [$group->name]);
	
	// make body
	$body = elgg_echo('group_tools:groups:invite:email:body', [
		$loggedin_user->name,
		$group->name,
		$site->name,
		$text,
		$site->name,
		elgg_normalize_url("register?group_invitecode={$invite_code}"),
		elgg_normalize_url("groups/invitations/?invitecode={$invite_code}"),
		$invite_code,
	]);
	
	$params = [
		'group' => $group,
		'inviter' => $loggedin_user,
		'invitee' => $email,
	];
	$body = elgg_trigger_plugin_hook('invite_notification', 'group_tools', $params, $body);
	
	return elgg_send_email($site_from, $email, $subject, $body);
}

/**
 * Verify that all supplied user_guids are a member of the group
 *
 * @param int   $group_guid the GUID of the group
 * @param array $user_guids an array of user GUIDs to check
 *
 * @return false|int[]
 */
function group_tools_verify_group_members($group_guid, $user_guids) {
	
	if (empty($group_guid) || empty($user_guids)) {
		return false;
	}
	
	if (!is_array($user_guids)) {
		$user_guids = [$user_guids];
	}
	
	$group = get_entity($group_guid);
	if (!($group instanceof ElggGroup)) {
		return false;
	}
	
	$options = [
		'type' => 'user',
		'limit' => false,
		'relationship' => 'member',
		'relationship_guid' => $group->getGUID(),
		'inverse_relationship' => true,
		'callback' => 'group_tools_guid_only_callback',
	];
	
	$member_guids = elgg_get_entities_from_relationship($options);
	if (empty($member_guids)) {
		return false;
	}
	
	// leave only GUIDS from both arrays
	$result = array_intersect($user_guids, $member_guids);
	if (empty($result)) {
		return false;
	}
	
	return array_values($result);
}

/**
 * Custom callback function to only return the GUID from a database row
 *
 * @param stdClass $row the database row
 *
 * @return int
 */
function group_tools_guid_only_callback($row) {
	return (int) $row->guid;
}

/**
 * Check if group creation is limited to site administrators
 * Also this function caches the result
 *
 * @return bool
 */
function group_tools_is_group_creation_limited() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	
	if (elgg_get_plugin_setting('admin_create', 'group_tools') === 'yes') {
		$result = true;
	}
	
	return $result;
}

/**
 * Get all the groups this email address is invited for
 *
 * @param string $email     the email address
 * @param int    $site_guid (optional) site_guid
 *
 * @return false|ElggGroup[]
 */
function group_tools_get_invited_groups_by_email($email, $site_guid = 0) {
	
	if (empty($email)) {
		return false;
	}
	
	$dbprefix = elgg_get_config('dbprefix');
	$site_secret = get_site_secret();
	$email = sanitise_string(strtolower($email));
	
	$email_invitation_id = elgg_get_metastring_id('email_invitation');
	
	$site_guid = sanitize_int($site_guid, false);
	if ($site_guid === 0) {
		$site_guid = elgg_get_site_entity()->getGUID();
	}
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'site_guids' => $site_guid,
		'joins' => [
			"JOIN {$dbprefix}annotations a ON a.owner_guid = e.guid",
			"JOIN {$dbprefix}metastrings msv ON a.value_id = msv.id",
		],
		'wheres' => [
			"(a.name_id = {$email_invitation_id} AND
				(msv.string = md5(CONCAT('{$site_secret}{$email}', e.guid))
				OR msv.string LIKE CONCAT(md5(CONCAT('{$site_secret}{$email}', e.guid)), '|%')
				)
			)",
		],
	];
	
	// make sure we can see all groups
	$ia = elgg_set_ignore_access(true);
	
	$groups = elgg_get_entities($options);
	if (empty($groups)) {
		// restore access
		elgg_set_ignore_access($ia);
		
		return false;
	}
	
	// restore access
	elgg_set_ignore_access($ia);
	
	return $groups;
}

/**
 * Generate a unique code to be used in email invitations
 *
 * @param int    $group_guid the group GUID
 * @param string $email      the email address
 *
 * @return false|string
 */
function group_tools_generate_email_invite_code($group_guid, $email) {
	
	$group_guid = sanitize_int($group_guid, false);
	if (empty($group_guid) || empty($email)) {
		return false;
	}
	
	// get site secret
	$site_secret = get_site_secret();
	
	// generate code
	return md5($site_secret . strtolower($email) . $group_guid);
}

/**
 * Get all the users who are missing from the ACLs of their groups
 *
 * @param int $group_guid (optional) a group GUID to check, otherwise all groups will be checked
 *
 * @return stdClass[] all the found database rows
 */
function group_tools_get_missing_acl_users($group_guid = 0) {
	
	$dbprefix = elgg_get_config('dbprefix');
	$group_guid = sanitize_int($group_guid, false);
	
	$query = 'SELECT ac.id AS acl_id, ac.owner_guid AS group_guid, er.guid_one AS user_guid';
	$query .= " FROM {$dbprefix}access_collections ac";
	$query .= " JOIN {$dbprefix}entities e ON e.guid = ac.owner_guid";
	$query .= " JOIN {$dbprefix}entity_relationships er ON ac.owner_guid = er.guid_two";
	$query .= " JOIN {$dbprefix}entities e2 ON er.guid_one = e2.guid";
	$query .= ' WHERE';
	
	if ($group_guid > 0) {
		// limit to the provided group
		$query .= " e.guid = {$group_guid}";
	} else {
		// all groups
		$query .= ' e.type = "group"';
	}
	
	$query .= ' AND e2.type = "user"';
	$query .= ' AND er.relationship = "member"';
	$query .= ' AND er.guid_one NOT IN (';
	$query .= ' SELECT acm.user_guid';
	$query .= " FROM {$dbprefix}access_collections ac2";
	$query .= " JOIN {$dbprefix}access_collection_membership acm ON ac2.id = acm.access_collection_id";
	$query .= ' WHERE ac2.owner_guid = ac.owner_guid';
	$query .= ' )';
	
	return get_data($query);
}

/**
 * Get all users who are in a group ACL but no longer member of the group
 *
 * @param int $group_guid (optional) a group GUID to check, otherwise all groups will be checked
 *
 * @return stdClass[] all the found database rows
 */
function group_tools_get_excess_acl_users($group_guid = 0) {
	
	$dbprefix = elgg_get_config('dbprefix');
	$group_guid = sanitise_int($group_guid, false);
	
	$query = 'SELECT ac.id AS acl_id, ac.owner_guid AS group_guid, acm.user_guid AS user_guid';
	$query .= " FROM {$dbprefix}access_collections ac";
	$query .= " JOIN {$dbprefix}access_collection_membership acm ON ac.id = acm.access_collection_id";
	$query .= " JOIN {$dbprefix}entities e ON ac.owner_guid = e.guid";
	$query .= ' WHERE';
	
	if ($group_guid > 0) {
		// limit to the provided group
		$query .= " e.guid = {$group_guid}";
	} else {
		// all groups
		$query .= ' e.type = "group"';
	}
	
	$query .= ' AND acm.user_guid NOT IN (';
	$query .= ' SELECT r.guid_one';
	$query .= " FROM {$dbprefix}entity_relationships r";
	$query .= ' WHERE r.relationship = "member"';
	$query .= ' AND r.guid_two = ac.owner_guid';
	$query .= ' )';
	
	return get_data($query);
}

/**
 * Get all groups that don't have an ACL
 *
 * @return ElggGroup[]
 */
function group_tools_get_groups_without_acl() {
	
	$dbprefix = elgg_get_config('dbprefix');
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'wheres' => [
			"e.guid NOT IN (
				SELECT ac.owner_guid
				FROM {$dbprefix}access_collections ac
				JOIN {$dbprefix}entities e ON ac.owner_guid = e.guid
				WHERE e.type = 'group'
			)",
		],
	];
	
	return elgg_get_entities($options);
}

/**
 * Remove a user from an access collection,
 * can't use remove_user_from_access_collection() because user might not exists any more
 *
 * @param int $user_guid     the user GUID to remove
 * @param int $collection_id the ID of the ACL to be removed from
 *
 * @return bool
 */
function group_tools_remove_user_from_access_collection($user_guid, $collection_id) {
	
	$collection_id = sanitize_int($collection_id, false);
	$user_guid = sanitize_int($user_guid, false);
	
	$collection = get_access_collection($collection_id);

	if (empty($user_guid) || empty($collection)) {
		return false;
	}

	$params = [
		'collection_id' => $collection_id,
		'user_guid' => $user_guid,
	];

	if (!elgg_trigger_plugin_hook('access:collections:remove_user', 'collection', $params, true)) {
		return false;
	}

	$dbprefix = elgg_get_config('dbprefix');
	
	$query = 'DELETE';
	$query .= " FROM {$dbprefix}access_collection_membership";
	$query .= " WHERE access_collection_id = {$collection_id}";
	$query .= " AND user_guid = {$user_guid}";

	return (bool) delete_data($query);
}

/**
 * Custom callback to save memory and queries for group admin transfer
 *
 * @param stdClass $row from elgg_get_* function
 *
 * @return array
 */
function group_tool_admin_transfer_callback($row) {
	return [
		'guid' => (int) $row->guid,
		'name' => $row->name,
	];
}

/**
 * Are group members allowed to invite new members to the group
 *
 * @param ElggGroup $group The group to check the settings
 *
 * @return bool
 */
function group_tools_allow_members_invite(ElggGroup $group) {
	
	if (!($group instanceof ElggGroup)) {
		return false;
	}
	
	$user = elgg_get_logged_in_user_entity();
	if (empty($user)) {
		return false;
	}
	
	// only for group members
	if (!$group->isMember($user)) {
		return false;
	}
	
	// check plugin setting, is this even allowed
	$setting = elgg_get_plugin_setting('invite_members', 'group_tools');
	if (!in_array($setting, ['yes_off', 'yes_on'])) {
		return false;
	}
	
	// check group setting
	$invite_members = $group->invite_members;
	if (empty($invite_members)) {
		// default not allowed
		$invite_members = 'no';
		if ($setting === 'yes_on') {
			// plugin setting says default allowed
			$invite_members = 'yes';
		}
	}
	
	if ($invite_members === 'yes') {
		return true;
	}
	
	return false;
}

/**
 * Custom annotations delete function because logged out users can't delete annotations
 *
 * @param ElggAnnotation[] $annotations annotations to delete
 *
 * @return void
 */
function group_tools_delete_annotations($annotations) {

	if (empty($annotations) || !is_array($annotations)) {
		return;
	}
	
	$dbprefix = elgg_get_config('dbprefix');
		
	foreach ($annotations as $annotation) {
		if (!($annotation instanceof ElggAnnotation)) {
			continue;
		}
		
		if (!elgg_trigger_event('delete', 'annotation', $annotation)) {
			continue;
		}
		
		delete_data("DELETE from {$dbprefix}annotations where id={$annotation->id}");
	}
}

/**
 * Returns suggested groups
 *
 * @param ElggUser $user  (optional) the user to get the groups for, defaults to the current user
 * @param int      $limit (optional) the number of suggested groups to return, default = 10
 *
 * @return ElggGroup[]
 */
function group_tools_get_suggested_groups($user = null, $limit = null) {
	
	if (!($user instanceof ElggUser)) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (is_null($limit)) {
		$limit = (int) get_input('limit', 10);
	}
	$limit = sanitize_int($limit, false);
	
	if (empty($user) || ($limit < 1)) {
		return [];
	}

	$result = [];
	$dbprefix = elgg_get_config('dbprefix');
	$group_membership_where = "e.guid NOT IN (
		SELECT er.guid_two FROM {$dbprefix}entity_relationships er
		WHERE er.guid_one = {$user->getGUID()}
		AND er.relationship IN (
			'member',
			'membership_request'
		)
	)";
	
	if (elgg_get_plugin_setting('auto_suggest_groups', 'group_tools') !== 'no') {
		$tag_names = elgg_get_registered_tag_metadata_names();
		if (!empty($tag_names)) {
			$user_metadata_options = [
				'guid' => $user->getGUID(),
				'limit' => false,
				'metadata_names' => $tag_names,
			];
			
			// get metadata
			$user_values = elgg_get_metadata($user_metadata_options);
			if (!empty($user_values)) {
				// transform to values
				$user_values = metadata_array_to_values($user_values);
				
				// find group with these metadatavalues
				$group_options = [
					'type' => 'group',
					'metadata_names' => $tag_names,
					'metadata_values' => $user_values,
					'wheres' => $group_membership_where,
					'group_by' => 'e.guid',
					'order_by' => 'count(msn.id) DESC',
					'limit' => $limit,
				];
				
				$groups = elgg_get_entities_from_metadata($group_options);
				if (!empty($groups)) {
					foreach ($groups as $group) {
						$result[$group->getGUID()] = $group;
						$limit--;
					}
				}
			}
		}
	}
	
	// get admin defined suggested groups
	if ($limit > 0) {
		$group_guids = string_to_tag_array(elgg_get_plugin_setting('suggested_groups', 'group_tools'));
		if (!empty($group_guids)) {
			$group_options = [
				'guids' => $group_guids,
				'type' => 'group',
				'wheres' => [$group_membership_where],
				'limit' => $limit,
			];
			
			if (!empty($result)) {
				$suggested_guids = array_keys($result);
				$group_options['wheres'][] = 'e.guid NOT IN (' . implode(',', $suggested_guids) . ')';
			}
			
			$groups = elgg_get_entities($group_options);
			if (!empty($groups)) {
				foreach ($groups as $group) {
					$result[$group->getGUID()] = $group;
				}
			}
		}
	}
	
	return $result;
}

/**
 * Show an indicator if the group is hidden
 *
 * @param ElggGroup $group The group to check
 *
 * @return bool
 */
function group_tools_show_hidden_indicator(ElggGroup $group) {
	static $check_required;
	
	if (!($group instanceof ElggGroup)) {
		return false;
	}
	
	if (!isset($check_required)) {
		$check_required = false;
		
		$groups_setting = elgg_get_plugin_setting('hidden_groups', 'groups');
		$setting = elgg_get_plugin_setting('show_hidden_group_indicator', 'group_tools');
		
		if (($groups_setting == 'yes') && !empty($setting) && ($setting != 'no')) {
			$check_required = $setting;
		}
	}
	
	if ($check_required === false) {
		return false;
	}
	
	// when to show
	if ($check_required === "group_acl") {
		// only if group is limited to members
		if (($group->access_id != ACCESS_PUBLIC) && ($group->access_id != ACCESS_LOGGED_IN)) {
			return true;
		}
	} else {
		// for all non public groups
		if ($group->access_id != ACCESS_PUBLIC) {
			return true;
		}
	}
	
	return false;
}

/**
 * Check the plugin setting which enables domain based groups
 *
 * @return bool
 */
function group_tools_domain_based_groups_enabled() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	
	$setting = elgg_get_plugin_setting('domain_based', 'group_tools');
	if ($setting === 'yes') {
		$result = true;
	}
	
	return $result;
}

/**
 * Check if the domain based settings for this group match the user
 *
 * @param ElggGroup $group the group to match to
 * @param ElggUser  $user  the user to check (defaults to current user)
 *
 * @return bool true if the domain of the user is found in the group settings
 */
function group_tools_check_domain_based_group(ElggGroup $group, ElggUser $user = null) {
	
	if (!group_tools_domain_based_groups_enabled()) {
		return false;
	}
	
	if (!($group instanceof ElggGroup)) {
		return false;
	}
	
	if (!($user instanceof ElggUser)) {
		// default to current user
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (!($user instanceof ElggUser)) {
		return false;
	}
		
	$domains = $group->getPrivateSetting('domain_based');
	if (empty($domains)) {
		return false;
	}
	
	$domains = explode('|', strtolower(trim($domains, '|')));
	
	list(,$domain) = explode('@', strtolower($user->email));
	
	return in_array($domain, $domains);
}

/**
 * Get all groups based on the email domain of the user from the group settings
 *
 * @param ElggUser $user      The user used to base the search
 * @param int      $site_guid (optional) the site guid to limit the search to, defaults to current site
 *
 * @return false|ElggGroup[]
 */
function group_tools_get_domain_based_groups(ElggUser $user, $site_guid = 0) {
	
	if (!group_tools_domain_based_groups_enabled()) {
		return false;
	}
	
	if (!($user instanceof ElggUser)) {
		return false;
	}
	
	$site_guid = sanitize_int($site_guid, false);
	if (empty($site_guid)) {
		$site_guid = elgg_get_site_entity()->getGUID();
	}
	
	list(, $domain) = explode('@', strtolower($user->email));
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'site_guids' => $site_guid,
		'private_setting_name_value_pairs' => [
			'name' => 'domain_based',
			'value' => "%|{$domain}|%",
			'operand' => 'LIKE',
		],
	];
	$groups = elgg_get_entities_from_private_settings($options);
	if (!empty($groups)) {
		return $groups;
	}
	
	return false;
}

/**
 * Enable registration on the site if disabled and a valid group invite code in provided
 *
 * @return void
 */
function group_tools_enable_registration() {
	
	$registration_allowed = (bool) elgg_get_config('allow_registration');
	if ($registration_allowed) {
		return;
	}
	
	// check for a group invite code
	$group_invitecode = get_input('group_invitecode');
	if (empty($group_invitecode)) {
		return;
	}
	
	// check if the code is valid
	if (group_tools_check_group_email_invitation($group_invitecode)) {
		// we have a valid code, so allow registration
		elgg_set_config('allow_registration', true);
	}
}

/**
 * Helper function to transfer the ownership of a group to a new user
 *
 * @param ElggGroup $group     the group to transfer
 * @param ElggUser  $new_owner the new owner
 *
 * @return bool
 */
function group_tools_transfer_group_ownership(ElggGroup $group, ElggUser $new_owner) {
	
	if (!($group instanceof ElggGroup) || !$group->canEdit()) {
		return false;
	}
	
	if (!($new_owner instanceof ElggUser)) {
		return false;
	}
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	
	// register plugin hook to make sure transfer can complete
	elgg_register_plugin_hook_handler('permissions_check', 'group', '\ColdTrick\GroupTools\Access::allowGroupOwnerTransfer');
	
	$old_owner = $group->getOwnerEntity();
	
	// transfer ownership
	$group->owner_guid = $new_owner->getGUID();
	$group->container_guid = $new_owner->getGUID();
	
	if (!$group->save()) {
		return false;
	}
	
	// make sure user is added to the group
	$group->join($new_owner);
	
	// remove existing group administrator role for new owner
	remove_entity_relationship($new_owner->getGUID(), 'group_admin', $group->getGUID());
	
	// check for group icon
	if (!empty($group->icontime)) {
		$prefix = "groups/{$group->getGUID()}";
		
		$sizes = elgg_get_config('icon_sizes');
		
		$ofh = new ElggFile();
		$ofh->owner_guid = $old_owner->getGUID();
		
		$nfh = new ElggFile();
		$nfh->owner_guid = $group->getOwnerGUID();
		
		foreach ($sizes as $size => $info) {
			// set correct file to handle
			$ofh->setFilename("{$prefix}{$size}.jpg");
			if (!$ofh->exists()) {
				// file doesn't exist
				continue;
			}
			
			$nfh->setFilename("{$prefix}{$size}.jpg");
			
			// open files
			$ofh->open('read');
			$nfh->open('write');
			
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
	$options = [
		'guid' => $group->getGUID(),
		'limit' => false,
	];
	$metadata = elgg_get_metadata($options);
	if (!empty($metadata)) {
		foreach ($metadata as $md) {
			if ($md->owner_guid == $old_owner->getGUID()) {
				$md->owner_guid = $new_owner->getGUID();
				$md->save();
			}
		}
	}
	
	// notify new owner
	if ($new_owner->getGUID() !== $loggedin_user->getGUID()) {
		$subject = elgg_echo('group_tools:notify:transfer:subject', [$group->name]);
		$message = elgg_echo('group_tools:notify:transfer:message', [
			$new_owner->name,
			$loggedin_user->name,
			$group->name,
			$group->getURL(),
		]);
		
		notify_user($new_owner->getGUID(), $group->getGUID(), $subject, $message);
	}
	
	// unregister plugin hook to make sure transfer can complete
	elgg_unregister_plugin_hook_handler('permissions_check', 'group', '\ColdTrick\GroupTools\Access::allowGroupOwnerTransfer');
	
	return true;
}

/**
 * Get the tool presets from the plugin settings
 *
 * @return false|array
 */
function group_tools_get_tool_presets() {
	
	$presets = elgg_get_plugin_setting('group_tool_presets', 'group_tools');
	if (empty($presets)) {
		return false;
	}
	
	return json_decode($presets, true);
}

/**
 * Get the time_created from the group membership relation
 *
 * @param ElggUser  $user  the user to check
 * @param ElggGroup $group the group to check
 *
 * @return int
 */
function group_tools_get_membership_information(ElggUser $user, ElggGroup $group) {
	
	if (!($user instanceof ElggUser) || !($group instanceof ElggGroup)) {
		return 0;
	}
	
	$query = 'SELECT *';
	$query .= ' FROM ' . elgg_get_config('dbprefix') . 'entity_relationships';
	$query .= " WHERE guid_one = {$user->getGUID()}";
	$query .= " AND guid_two = {$group->getGUID()}";
	$query .= ' AND relationship = "member"';
	
	$row = get_data_row($query);
	if (!empty($row)) {
		return (int) $row->time_created;
	}
	
	return 0;
}

/**
 * Check the plugin setting to allow multiple group admins
 *
 * @return bool
 */
function group_tools_multiple_admin_enabled() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	
	if (elgg_get_plugin_setting('multiple_admin', 'group_tools') === 'yes') {
		$result = true;
	}
	
	return $result;
}

/**
 * Check if the group allows admins (not owner) to assign other admins
 *
 * @param ElggGroup $group the group to check
 *
 * @return bool
 */
function group_tools_can_assign_group_admin(ElggGroup $group) {
	
	if (!($group instanceof ElggGroup)) {
		return false;
	}
	
	$user_guid = elgg_get_logged_in_user_guid();
	if (empty($user_guid)) {
		return false;
	}
	
	if (!group_tools_multiple_admin_enabled()) {
		return false;
	}
	
	if (($group->getOwnerGUID() === $user_guid) || elgg_is_admin_logged_in()) {
		return true;
	} elseif (($group->group_multiple_admin_allow_enable === 'yes') && $group->canEdit($user_guid)) {
		return true;
	}
	
	return false;
}
