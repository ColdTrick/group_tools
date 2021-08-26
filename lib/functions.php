<?php
/**
 * All helper functions for this plugin can be found here.
 */

use Elgg\Database\Clauses\OrderByClause;
use Elgg\Database\Select;
use Elgg\Database\QueryBuilder;
use Elgg\Security\Base64Url;

/**
 * Check if a invitation code results in a group
 *
 * @param string $invite_code the invite code
 * @param int    $group_guid  (optional) the group to check
 *
 * @return false|ElggGroup
 */
function group_tools_check_group_email_invitation(string $invite_code, int $group_guid = 0) {
	
	if (empty($invite_code) || !Base64Url::decode($invite_code)) {
		return false;
	}
	
	$options = [
		'limit' => 1,
		'annotation_name_value_pairs' => [
			[
				'name' => 'email_invitation',
				'value' => $invite_code,
				'type' => ELGG_VALUE_STRING,
			],
			[
				'name' => 'email_invitation',
				'value' => "{$invite_code}|%",
				'operand' => 'LIKE',
				'type' => ELGG_VALUE_STRING,
			]
		],
		'annotation_name_value_pairs_operator' => 'OR',
	];
	
	$group_guid = (int) $group_guid;
	if ($group_guid > 0) {
		$options['annotation_owner_guids'] = [$group_guid];
	}
	
	// find hidden groups
	$groups = elgg_call(ELGG_IGNORE_ACCESS, function() use ($options){
		return elgg_get_entities($options);
	});
	
	return elgg_extract(0, $groups, false);
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
function group_tools_invite_user(ElggGroup $group, ElggUser $user, string $text = '', bool $resend = false) {
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	if (empty($loggedin_user)) {
		return false;
	}
	
	// Create relationship
	$relationship = add_entity_relationship($group->guid, 'invited', $user->guid);
	
	if (empty($relationship) && empty($resend)) {
		return false;
	}
	
	// Send email
	$url = elgg_generate_url('collection:group:group:invitations', [
		'username' => $user->username,
	]);
	
	$subject = elgg_echo('groups:invite:subject', [
		$user->getDisplayName(),
		$group->getDisplayName(),
	]);
	$msg = elgg_echo('group_tools:groups:invite:body', [
		$user->getDisplayName(),
		$loggedin_user->getDisplayName(),
		$group->getDisplayName(),
		$text,
		$url,
	]);
	
	$params = [
		'object' => $group,
		'action' => 'invite',
	];
	
	if (notify_user($user->guid, $group->owner_guid, $subject, $msg, $params, ['email', 'site'])) {
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
function group_tools_add_user(ElggGroup $group, ElggUser $user, string $text = '') {
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	if (empty($loggedin_user)) {
		return false;
	}
	
	return elgg_call(ELGG_IGNORE_ACCESS, function() use ($group, $user, $loggedin_user, $text) {
		
		if (!$group->join($user)) {
			return false;
		}
		
		// notify user
		$subject = elgg_echo('group_tools:groups:invite:add:subject', [$group->getDisplayName()]);
		$msg = elgg_echo('group_tools:groups:invite:add:body', [
			$user->getDisplayName(),
			$loggedin_user->getDisplayName(),
			$group->getDisplayName(),
			$text,
			$group->getURL(),
		]);
		
		$params = [
			'group' => $group,
			'inviter' => $loggedin_user,
			'invitee' => $user,
		];
		$msg = elgg_trigger_plugin_hook('invite_notification', 'group_tools', $params, $msg);
		
		if (!notify_user($user->guid, $group->owner_guid, $subject, $msg, [], ['email'])) {
			return false;
		}
		
		return true;
	});
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
function group_tools_invite_email(ElggGroup $group, string $email, string $text = '', bool $resend = false) {
	
	$loggedin_user = elgg_get_logged_in_user_entity();
	if (!is_email_address($email) || empty($loggedin_user)) {
		return false;
	}
	
	// generate invite code
	$invite_code = elgg_build_hmac([
		strtolower($email),
		$group->guid,
	])->getToken();
	
	$found_group = group_tools_check_group_email_invitation($invite_code, $group->guid);
	if (!empty($found_group) && empty($resend)) {
		return null;
	}
	
	if (empty($found_group)) {
		// register invite with group
		$group->annotate('email_invitation', "{$invite_code}|{$email}", ACCESS_LOGGED_IN, $group->guid);
	}
	
	// make site email
	$site = elgg_get_site_entity();
	$email = \Elgg\Email::factory([
		'from' => $site,
		'to' => $email,
		'subject' => elgg_echo('group_tools:groups:invite:email:subject', [$group->getDisplayName()]),
		'body' => elgg_echo('group_tools:groups:invite:email:body', [
			$loggedin_user->getDisplayName(),
			$group->getDisplayName(),
			$site->getDisplayName(),
			$text,
			$site->getDisplayName(),
			elgg_generate_url('account:register', [
				'group_invitecode' => $invite_code,
			]),
			$invite_code,
		]),
		'params' => [
			'group' => $group,
			'inviter' => $loggedin_user,
			'invitee' => $email,
		],
	]);
	
	$body = elgg_trigger_plugin_hook('invite_notification', 'group_tools', $email->getParams(), $email->getBody());
	$email->setBody($body);
	
	return elgg_send_email($email);
}

/**
 * Get all the groups this email address is invited for
 *
 * @param string $email the email address
 *
 * @return ElggGroup[]
 */
function group_tools_get_invited_groups_by_email(string $email): array {
	if (empty($email)) {
		return [];
	}
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'annotation_name_value_pairs' => [
			[
				'name' => 'email_invitation',
				'value' => "%|{$email}",
				'operand' => 'LIKE',
				'type' => ELGG_VALUE_STRING,
			],
		],
	];
	
	// make sure we can see all groups
	return elgg_call(ELGG_IGNORE_ACCESS, function () use ($options) {
		return elgg_get_entities($options);
	});
}

/**
 * Get all the users who are missing from the ACLs of their groups
 *
 * @param int $group_guid (optional) a group GUID to check, otherwise all groups will be checked
 *
 * @return stdClass[] all the found database rows
 */
function group_tools_get_missing_acl_users(int $group_guid = 0) {
	
	$select = Select::fromTable('access_collections', 'ac');
	$select->select('ac.id AS acl_id')
		->addSelect('ac.owner_guid AS group_guid')
		->addSelect('er.guid_one AS user_guid');
	$select->joinEntitiesTable('ac', 'owner_guid', 'inner', 'e');
	$select->joinRelationshipTable('ac', 'owner_guid', 'member', false, 'inner', 'er');
	$select->joinEntitiesTable('er', 'guid_one', 'inner', 'e2');
	
	$group_guid = (int) $group_guid;
	if ($group_guid > 0) {
		$select->where($select->compare('e.guid', '=', $group_guid, ELGG_VALUE_GUID));
	} else {
		$select->where($select->compare('e.type', '=', 'group', ELGG_VALUE_STRING));
	}
	
	$select->andWhere($select->compare('e2.type', '=', 'user', ELGG_VALUE_STRING));
	
	$sub = $select->subquery('access_collections', 'ac2');
	$sub->select('acm.user_guid');
	$sub->join('ac2', 'access_collection_membership', 'acm', $select->compare('ac2.id', '=', 'acm.access_collection_id'));
	$sub->where($select->compare('ac2.owner_guid', '=', 'ac.owner_guid'));
	
	$select->andWhere($select->compare('er.guid_one', 'NOT IN', $sub->getSQL()));
	
	return elgg()->db->getData($select);
}

/**
 * Get all users who are in a group ACL but no longer member of the group
 *
 * @param int $group_guid (optional) a group GUID to check, otherwise all groups will be checked
 *
 * @return stdClass[] all the found database rows
 */
function group_tools_get_excess_acl_users(int $group_guid = 0) {
	
	$select = Select::fromTable('access_collections', 'ac');
	$select->select('ac.id AS acl_id')
		->addSelect('ac.owner_guid AS group_guid')
		->addSelect('acm.user_guid AS user_guid');
	$select->joinEntitiesTable('ac', 'owner_guid', 'inner', 'e');
	$select->join('ac', 'access_collection_membership', 'acm', $select->compare('ac.id', '=', 'acm.access_collection_id'));
	
	$group_guid = (int) $group_guid;
	if ($group_guid > 0) {
		$select->where($select->compare('e.guid', '=', $group_guid, ELGG_VALUE_GUID));
	} else {
		$select->where($select->compare('e.type', '=', 'group', ELGG_VALUE_STRING));
	}
	
	$sub = $select->subquery('entity_relationships', 'r');
	$sub->select('r.guid_one');
	$sub->where($select->compare('r.relationship', '=', 'member', ELGG_VALUE_STRING));
	$sub->andWhere($select->compare('r.guid_two', '=', 'ac.owner_guid'));
	
	$select->andWhere($select->compare('acm.user_guid', 'NOT IN', $sub->getSQL()));
	
	return elgg()->db->getData($select);
}

/**
 * Get all groups that don't have an ACL
 *
 * @param bool $count return a count
 *
 * @return int|ElggGroup[]
 */
function group_tools_get_groups_without_acl(bool $count = false) {
	return elgg_get_entities([
		'type' => 'group',
		'limit' => false,
		'count' => $count,
		'wheres' => [
			function (QueryBuilder $qb, $main_alias) {
				$select = $qb->subquery('access_collections', 'ac');
				$select->select('ac.owner_guid')
					->where($qb->compare('e2.type', '=', 'group', ELGG_VALUE_STRING));
				$select->joinEntitiesTable('ac', 'owner_guid', 'inner', 'e2');
				
				return $qb->compare("{$main_alias}.guid", 'NOT IN', $select->getSQL());
			},
		],
	]);
}

/**
 * Are group members allowed to invite new members to the group
 *
 * @param ElggGroup $group The group to check the settings
 *
 * @return bool
 */
function group_tools_allow_members_invite(ElggGroup $group): bool {
	
	$user = elgg_get_logged_in_user_entity();
	if (!$user instanceof ElggUser) {
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
	$invite_members = $setting === 'yes_off' ? 'no' : 'yes';
	$invite_members = $group->getPluginSetting('group_tools', 'invite_members', $invite_members);
	
	return $invite_members === 'yes';
}

/**
 * Returns suggested groups
 *
 * @param ElggUser $user  (optional) the user to get the groups for, defaults to the current user
 * @param int      $limit (optional) the number of suggested groups to return, default = 10
 *
 * @return ElggGroup[]
 * @todo revisit this
 */
function group_tools_get_suggested_groups(ElggUser $user = null, int $limit = null) {
	
	if (!$user instanceof ElggUser) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (is_null($limit)) {
		$limit = (int) get_input('limit', 10);
	}
	
	if (!$user instanceof ElggUser || ($limit < 1)) {
		return [];
	}

	$result = [];
	$group_membership_where = function (QueryBuilder $qb, $main_alias) use ($user) {
		$select = $qb->subquery('entity_relationships', 'er');
		$select->select('er.guid_two')
			->where($qb->compare('er.guid_one', '=', $user->guid, ELGG_VALUE_GUID))
			->andWhere($qb->compare('er.relationship', 'in', ['member', 'membership_request'], ELGG_VALUE_STRING));
		
		return $qb->compare("{$main_alias}.guid", 'NOT IN', $select->getSQL());
	};
	
	if (elgg_get_plugin_setting('auto_suggest_groups', 'group_tools') !== 'no') {
		$user_tag_names = [];
		$group_tag_names = [];
		
		$user_fields = elgg()->fields->get($user->getType(), $user->getSubtype());
		foreach ($user_fields as $field) {
			if (!in_array(elgg_extract('#type', $field), ['tags', 'location'])) {
				continue;
			}
			
			$user_tag_names[] = elgg_extract('name', $field);
		}
		
		$group_fields = elgg()->fields->get('group', 'group');
		foreach ($group_fields as $field) {
			if (!in_array(elgg_extract('#type', $field), ['tags', 'location'])) {
				continue;
			}
			
			$group_tag_names[] = elgg_extract('name', $field);
		}
		
		$user_tag_names = array_filter($user_tag_names);
		$group_tag_names = array_filter($group_tag_names);
		if (!empty($user_tag_names) && !empty($group_tag_names)) {
			$user_metadata_options = [
				'guid' => $user->guid,
				'limit' => false,
				'metadata_names' => $user_tag_names,
			];
			
			// get metadata
			$user_values = elgg_get_metadata($user_metadata_options);
			if (!empty($user_values)) {
				// transform to values
				$user_values = [];
				foreach ($user_values as $metadata) {
					$user_values[] = $metadata->value;
				}
				$user_values = array_unique($user_values);
				$user_values = array_filter($user_values);
			}
			
			if (!empty($user_values)) {
				// find group with these metadatavalues
				$group_options = [
					'type' => 'group',
					'metadata_names' => $group_tag_names,
					'metadata_values' => $user_values,
					'wheres' => $group_membership_where,
					'group_by' => [
						function (QueryBuilder $qb, $main_alias) {
							return "{$main_alias}.guid";
						},
					],
					'order_by' => new OrderByClause('count(msn.id)', 'DESC'),
					'limit' => $limit,
				];
				
				$groups = elgg_get_entities($group_options);
				if (!empty($groups)) {
					foreach ($groups as $group) {
						$result[$group->guid] = $group;
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
				$group_options['wheres'][] = function (QueryBuilder $qb, $main_alias) use ($suggested_guids) {
					return $qb->compare("{$main_alias}.guid", 'not in', $suggested_guids, ELGG_VALUE_GUID);
				};
			}
			
			$groups = elgg_get_entities($group_options);
			if (!empty($groups)) {
				foreach ($groups as $group) {
					$result[$group->guid] = $group;
				}
			}
		}
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
function group_tools_check_domain_based_group(ElggGroup $group, ElggUser $user = null): bool {
	
	if (elgg_get_plugin_setting('domain_based', 'group_tools') !== 'yes') {
		return false;
	}
	
	if (!$user instanceof ElggUser) {
		// default to current user
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (!$user instanceof ElggUser) {
		return false;
	}
		
	$domains = $group->getPluginSetting('group_tools', 'domain_based');
	if (empty($domains)) {
		return false;
	}
	
	$domains = string_to_tag_array(strtolower($domains));
	
	list(, $domain) = explode('@', strtolower($user->email));
	
	return in_array($domain, $domains);
}

/**
 * Get all groups based on the email domain of the user from the group settings
 *
 * @param ElggUser $user      The user used to base the search
 *
 * @return ElggGroup[]
 */
function group_tools_get_domain_based_groups(ElggUser $user): array {
	
	if (elgg_get_plugin_setting('domain_based', 'group_tools') !== 'yes') {
		return [];
	}
	
	list(, $domain) = explode('@', strtolower($user->email));
	
	$options = [
		'type' => 'group',
		'limit' => false,
		'wheres' => [
			function (QueryBuilder $qb, $main_alias) use ($domain) {
				$ps = $qb->joinPrivateSettingsTable($main_alias);
				
				$ors = [
					$qb->compare("{$ps}.value", '=', $domain, ELGG_VALUE_STRING),
					$qb->compare("{$ps}.value", 'like', "{$domain},%", ELGG_VALUE_STRING),
					$qb->compare("{$ps}.value", 'like', "%,{$domain}", ELGG_VALUE_STRING),
					$qb->compare("{$ps}.value", 'like', "%,{$domain},%", ELGG_VALUE_STRING),
				];
				
				$ands = [
					$qb->compare("{$ps}.name", '=', 'plugin:group_setting:group_tools:domain_based', ELGG_VALUE_STRING),
					$qb->merge($ors, 'OR'),
				];
				
				return $qb->merge($ands);
			},
		],
	];
	
	return elgg_get_entities($options);
}

/**
 * Helper function to transfer the ownership of a group to a new user
 *
 * @param ElggGroup $group        the group to transfer
 * @param ElggUser  $new_owner    the new owner
 * @param bool      $remain_admin the current owner remains an admin after transfer
 *
 * @return bool
 */
function group_tools_transfer_group_ownership(ElggGroup $group, ElggUser $new_owner, bool $remain_admin = false): bool {
	
	if (!$group->canEdit()) {
		return false;
	}
	
	// register plugin hook to make sure transfer can complete
	elgg_register_plugin_hook_handler('permissions_check', 'group', '\ColdTrick\GroupTools\Permissions::allowGroupOwnerTransfer');
	
	$old_owner = $group->getOwnerEntity();
	
	// transfer ownership
	$group->owner_guid = $new_owner->guid;
	$group->container_guid = $new_owner->guid;
	
	if (!$group->save()) {
		return false;
	}
	
	// make sure user is added to the group
	$group->join($new_owner);
	
	// remove existing group administrator role for new owner
	remove_entity_relationship($new_owner->guid, 'group_admin', $group->guid);
	
	// notify new owner
	$loggedin_user = elgg_get_logged_in_user_entity();
	if ($loggedin_user && ($new_owner->guid !== $loggedin_user->guid)) {
		$subject = elgg_echo('group_tools:notify:transfer:subject', [$group->getDisplayName()]);
		$message = elgg_echo('group_tools:notify:transfer:message', [
			$new_owner->getDisplayName(),
			$loggedin_user->getDisplayName(),
			$group->getDisplayName(),
			$group->getURL(),
		]);
		
		$params = [
			'object' => $group,
			'action' => 'transfer_owner',
			'old_owner' => $old_owner,
			'new_owner' => $new_owner,
		];
		
		notify_user($new_owner->guid, $group->guid, $subject, $message, $params);
	}
	
	// check if the old owner wishes to remain as an admin
	if ($remain_admin && ($old_owner->guid === $loggedin_user->guid)) {
		add_entity_relationship($old_owner->guid, 'group_admin', $group->guid);
	}
	
	// unregister plugin hook to make sure transfer can complete
	elgg_unregister_plugin_hook_handler('permissions_check', 'group', '\ColdTrick\GroupTools\Permissions::allowGroupOwnerTransfer');
	
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
 * Check the plugin setting to allow multiple group admins
 *
 * @return bool
 */
function group_tools_multiple_admin_enabled(): bool {
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
function group_tools_can_assign_group_admin(ElggGroup $group): bool {
	
	$user_guid = elgg_get_logged_in_user_guid();
	if (empty($user_guid)) {
		return false;
	}
	
	if (!group_tools_multiple_admin_enabled()) {
		return false;
	}
	
	if (($group->owner_guid === $user_guid) || elgg_is_admin_logged_in()) {
		return true;
	} elseif (($group->group_multiple_admin_allow_enable === 'yes') && $group->canEdit($user_guid)) {
		return true;
	}
	
	return false;
}

/**
 * Check the plugin/group setting if join motivation is needed
 *
 * @param ElggGroup $group (optional) the group to check for
 *
 * @return bool
 */
function group_tools_join_motivation_required(ElggGroup $group = null): bool {
	static $plugin_settings;
	static $check_group = false;
	
	// load plugin settings
	if (!isset($plugin_settings)) {
		$plugin_settings = false;
		
		$setting = elgg_get_plugin_setting('join_motivation', 'group_tools', 'no');
		switch ($setting) {
			case 'yes_off':
				$check_group = true;
				break;
			case 'yes_on':
				$check_group = true;
				$plugin_settings = true;
				break;
			case 'required':
				$plugin_settings = true;
				break;
		}
	}
	
	// do we need to check the group settings?
	if (!$group instanceof ElggGroup || !$check_group) {
		return ($plugin_settings || $check_group);
	}
	
	if ($group->isPublicMembership()) {
		// open group, no motivation needed
		return false;
	}
	
	// get group setting
	$group_setting = $group->getPrivateSetting('join_motivation');
	switch ($group_setting) {
		case 'no':
			return false;
			
		case 'yes':
			return true;
			
	}
	
	return $plugin_settings;
}

/**
 * Check if group mail is allowed
 *
 * @param ElggGroup $group the group to check
 *
 * @return bool
 */
function group_tools_group_mail_enabled(ElggGroup $group = null): bool {
	static $mail_enabled;
	
	if (!isset($mail_enabled)) {
		$mail_enabled = false;
		
		$setting = elgg_get_plugin_setting('mail', 'group_tools');
		if ($setting === 'yes') {
			$mail_enabled = true;
		}
	}
	
	// quick return if plugin setting says no
	if (!$mail_enabled) {
		return false;
	}
	
	if (!$group instanceof ElggGroup) {
		return true;
	}
	
	if ($group->canEdit()) {
		// group owners and admin can mail
		return true;
	}
	
	return false;
}

/**
 * Check if group mail is enabled for members
 *
 * @param ElggGroup $group The group to check (can be empty to check plugin setting)
 *
 * @return bool
 */
function group_tools_group_mail_members_enabled(ElggGroup $group = null): bool {
	static $mail_members_enabled;
	
	if (!isset($mail_members_enabled)) {
		$mail_members_enabled = false;
	
		$setting = elgg_get_plugin_setting('mail_members', 'group_tools');
		if ($setting === 'yes') {
			$mail_members_enabled = true;
		}
	}
	
	// quick return if mail members is not allowed
	if (!group_tools_group_mail_enabled()) {
		return false;
	}
	
	if (!$mail_members_enabled) {
		return false;
	}
	
	if (!$group instanceof ElggGroup) {
		return true;
	}
	
	if ($group->canEdit()) {
		// group owners and admin can mail
		return true;
	}
	
	if ($group->isMember() && ($group->mail_members_enable === 'yes')) {
		return true;
	}
	
	return false;
}

/**
 * Get the helper class for stale information
 *
 * @param ElggGroup $group          the group to get the info for
 * @param int       $number_of_days (optional) a number of days to check stale against, defaults to plugin setting
 *
 * @return false|\ColdTrick\GroupTools\StaleInfo
 */
function group_tools_get_stale_info(ElggGroup $group, int $number_of_days = null) {
	
	if (!isset($number_of_days)) {
		$number_of_days = (int) elgg_get_plugin_setting('stale_timeout', 'group_tools');
	}
	
	if ($number_of_days < 1) {
		return false;
	}
	
	return new ColdTrick\GroupTools\StaleInfo($group, $number_of_days);
}

/**
 * Allow hidden groups to be created (applies group tools setting)
 *
 * @return bool
 */
function group_tools_allow_hidden_groups(): bool {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	
	$plugin_setting = elgg_get_plugin_setting('allow_hidden_groups', 'group_tools');
	if (!isset($plugin_setting)) {
		$plugin_setting = elgg_get_plugin_setting('hidden_groups', 'groups', 'no');
	}
	
	switch ($plugin_setting) {
		case 'yes':
			$result = true;
			break;
		case 'admin':
			$result = elgg_is_admin_logged_in();
			break;
	}
	
	return $result;
}

/**
 * Get the auto join group configurations
 *
 * @param bool $refresh refresh data from database (default: false)
 *
 * @return array
 */
function group_tools_get_auto_join_configurations(bool $refresh = false): array {
	static $result;
	
	$refresh = (bool) $refresh;
	
	if (isset($result) && !$refresh) {
		return $result;
	}
	
	$result = [];
	$setting = elgg_get_plugin_setting('auto_join_config', 'group_tools');
	if (!empty($setting)) {
		$result = json_decode($setting, true);
	}
	
	return $result;
}

/**
 * Get an auto join group configuration
 *
 * @param string $id the id of the Configuration
 *
 * @return false|array
 */
function group_tools_get_auto_join_configuration(string $id) {
	
	if (empty($id)) {
		return false;
	}
	
	$existing = group_tools_get_auto_join_configurations();
	
	return elgg_extract($id, $existing, []);
}

/**
 * Create/edit an auto join group configuration
 *
 * @param array $config the auto join configuration (must contain at least the key 'id')
 *
 * @return bool
 */
function group_tools_save_auto_join_configuration(array $config): bool {
	
	if (empty($config)) {
		return false;
	}
	
	$id = elgg_extract('id', $config);
	if (empty($id)) {
		return false;
	}
	
	$existing_config = group_tools_get_auto_join_configurations();
	$existing_config[$id] = $config;
	
	// store new config
	$plugin = elgg_get_plugin_from_id('group_tools');
	$result = $plugin->setSetting('auto_join_config', json_encode($existing_config), 'group_tools');
	
	// refesh cache
	group_tools_get_auto_join_configurations(true);
	
	return $result;
}

/**
 * Remove an auto join group configuration
 *
 * @param string $id the id of the configuration to remove
 *
 * @return bool
 */
function group_tools_delete_auto_join_configuration(string $id): bool {
	
	if (empty($id)) {
		return false;
	}
	
	$existing_config = group_tools_get_auto_join_configurations();
	if (!isset($existing_config[$id])) {
		// id didn't exist so remove was a success
		return true;
	}
	
	unset($existing_config[$id]);
	
	// store new config
	$plugin = elgg_get_plugin_from_id('group_tools');
	$result = $plugin->setSetting('auto_join_config', json_encode($existing_config));
	
	// refesh cache
	group_tools_get_auto_join_configurations(true);
	
	return $result;
}

/**
 * Get a list of profile field replacements to auto join group matching
 *
 * @return string[]
 */
function group_tools_get_auto_join_pattern_user_options(): array {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = [
		'name' => elgg_echo('name'),
		'email' => elgg_echo('email'),
		'username' => elgg_echo('username'),
	];
	
	$profile_fields = elgg()->fields->get('user', 'user');
	if (empty($profile_fields)) {
		return $result;
	}
	
	foreach ($profile_fields as $field) {
		$metadata_name = elgg_extract('name', $field);
		
		$lan = $metadata_name;
		if (elgg_language_key_exists("profile:{$metadata_name}")) {
			$lan = elgg_echo("profile:{$metadata_name}");
		}
		
		$result[$metadata_name] = $lan;
	}
	
	return $result;
}

/**
 * Show the tools section on the group edit form
 *
 * @return bool
 */
function group_tools_show_tools_on_edit(): bool {
	
	if (elgg_get_page_owner_entity() instanceof ElggGroup) {
		// edit of a group
		return true;
	}
	
	$group_tools_preset = get_input('group_tools_preset');
	if (empty($group_tools_preset) || elgg_get_plugin_setting('create_based_on_preset', 'group_tools') !== 'yes') {
		// no preset or plugin setting doesn't hide it
		return true;
	}
	
	$presets = group_tools_get_tool_presets();
	if (empty($presets)) {
		// no presets
		return true;
	}
	
	foreach ($presets as $preset) {
		if ($group_tools_preset !== elgg_extract('title', $preset)) {
			continue;
		}
		
		return false;
	}
	
	return true;
}
