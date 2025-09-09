<?php
/**
 * All helper functions for this plugin can be found here.
 */

use ColdTrick\GroupTools\StaleInfo;
use Elgg\Database\Clauses\OrderByClause;
use Elgg\Database\MetadataTable;
use Elgg\Database\QueryBuilder;
use Elgg\Database\RelationshipsTable;
use Elgg\Security\Base64Url;

/**
 * Check if an invitation code results in a group
 *
 * @param string $invite_code the invite code
 * @param int    $group_guid  (optional) the group to check
 *
 * @return null|\ElggGroup
 */
function group_tools_check_group_email_invitation(string $invite_code, int $group_guid = 0): ?\ElggGroup {
	if (empty($invite_code) || !Base64Url::decode($invite_code)) {
		return null;
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
	
	if ($group_guid > 0) {
		$options['annotation_owner_guids'] = [$group_guid];
	}
	
	// find hidden groups
	$groups = elgg_call(ELGG_IGNORE_ACCESS, function() use ($options){
		return elgg_get_entities($options);
	});
	
	return elgg_extract(0, $groups);
}

/**
 * Invite a user to a group
 *
 * @param \ElggGroup $group  the group to be invited for
 * @param \ElggUser  $user   the user to be invited
 * @param string     $text   (optional) extra text in the invitation
 * @param bool       $resend should existing invitations be resend
 *
 * @return bool
 */
function group_tools_invite_user(\ElggGroup $group, \ElggUser $user, string $text = '', bool $resend = false): bool {
	if (!elgg_is_logged_in()) {
		return false;
	}
	
	// Create relationship
	$relationship = $group->addRelationship($user->guid, 'invited');
	if (!$relationship && !$resend) {
		return false;
	}
	
	$user->notify('invite', $group, [
		'invite_text' => $text,
	]);
	
	return true;
}

/**
 * Add a user to a group
 *
 * @param \ElggGroup $group the group to add the user to
 * @param \ElggUser  $user  the user to be added
 * @param string     $text  (optional) extra text for the notification
 *
 * @return bool
 */
function group_tools_add_user(\ElggGroup $group, \ElggUser $user, string $text = ''): bool {
	$loggedin_user = elgg_get_logged_in_user_entity();
	if (empty($loggedin_user)) {
		return false;
	}
	
	return elgg_call(ELGG_IGNORE_ACCESS, function() use ($group, $user, $loggedin_user, $text) {
		if (!$group->join($user)) {
			return false;
		}
		
		// notify user
		$user->notify('add_user', $group, [
			'text' => $text,
		]);
		
		return true;
	});
}

/**
 * Invite a new user by email to a group
 *
 * @param \ElggGroup $group  the group to be invited for
 * @param string     $email  the email address to be invited
 * @param string     $text   (optional) extra text in the invitation
 * @param bool       $resend should existing invitations be resend
 *
 * @return bool|null true is invited, false on failure, null when already send
 */
function group_tools_invite_email(\ElggGroup $group, string $email, string $text = '', bool $resend = false): ?bool {
	$loggedin_user = elgg_get_logged_in_user_entity();
	if (!elgg_is_valid_email($email) || empty($loggedin_user)) {
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
	
	$body = elgg_trigger_event_results('invite_notification', 'group_tools', $email->getParams(), $email->getBody());
	$email->setBody($body);
	
	return elgg_send_email($email);
}

/**
 * Get all the groups this email address is invited for
 *
 * @param string $email the email address
 *
 * @return \ElggGroup[]
 */
function group_tools_get_invited_groups_by_email(string $email): array {
	if (empty($email)) {
		return [];
	}

	// make sure we can see all groups
	return elgg_call(ELGG_IGNORE_ACCESS, function () use ($email) {
		return elgg_get_entities([
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
		]);
	});
}

/**
 * Returns suggested groups
 *
 * @param null|\ElggUser $user  (optional) the user to get the groups for, defaults to the current user
 * @param null|int       $limit (optional) the number of suggested groups to return, default = 10
 *
 * @return \ElggGroup[]
 * @todo revisit this
 */
function group_tools_get_suggested_groups(?\ElggUser $user = null, ?int $limit = null): array {
	if (!$user instanceof \ElggUser) {
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (is_null($limit)) {
		$limit = (int) get_input('limit', 10);
	}
	
	if (!$user instanceof \ElggUser || $limit < 1) {
		return [];
	}

	$result = [];
	$group_membership_where = function (QueryBuilder $qb, $main_alias) use ($user) {
		$select = $qb->subquery(RelationshipsTable::TABLE_NAME, 'er');
		$select->select("{$select->getTableAlias()}.guid_two")
			->where($qb->compare("{$select->getTableAlias()}.guid_one", '=', $user->guid, ELGG_VALUE_GUID))
			->andWhere($qb->compare("{$select->getTableAlias()}.relationship", 'in', ['member', 'membership_request'], ELGG_VALUE_STRING));
		
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
				$user_values_clean = [];
				foreach ($user_values as $metadata) {
					$user_values_clean[] = $metadata->value;
				}
				
				$user_values_clean = array_unique($user_values_clean);
				$user_values = array_filter($user_values_clean);
			}
			
			if (!empty($user_values)) {
				// find group with these metadata values
				$groups = elgg_get_entities([
					'type' => 'group',
					'metadata_names' => $group_tag_names,
					'metadata_values' => $user_values,
					'wheres' => $group_membership_where,
					'group_by' => [
						function (QueryBuilder $qb, $main_alias) {
							return "{$main_alias}.guid";
						},
					],
					'order_by' => new OrderByClause('count(' . MetadataTable::DEFAULT_JOIN_ALIAS . '.id)', 'DESC'),
					'limit' => $limit,
				]);
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
		$group_guids = elgg_string_to_array((string) elgg_get_plugin_setting('suggested_groups', 'group_tools'));
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
 * @param \ElggGroup     $group the group to match to
 * @param null|\ElggUser $user  the user to check (defaults to current user)
 *
 * @return bool true if the domain of the user is found in the group settings
 */
function group_tools_check_domain_based_group(\ElggGroup $group, ?\ElggUser $user = null): bool {
	if (elgg_get_plugin_setting('domain_based', 'group_tools') !== 'yes') {
		return false;
	}
	
	if (!$user instanceof \ElggUser) {
		// default to current user
		$user = elgg_get_logged_in_user_entity();
	}
	
	if (!$user instanceof \ElggUser) {
		return false;
	}
		
	$domains = $group->getPluginSetting('group_tools', 'domain_based');
	if (empty($domains)) {
		return false;
	}
	
	$domains = elgg_string_to_array(strtolower($domains));
	
	list(, $domain) = explode('@', strtolower($user->email));
	
	return in_array($domain, $domains);
}

/**
 * Get all groups based on the email domain of the user from the group settings
 *
 * @param \ElggUser $user The user used to base the search
 *
 * @return \ElggGroup[]
 */
function group_tools_get_domain_based_groups(\ElggUser $user): array {
	if (elgg_get_plugin_setting('domain_based', 'group_tools') !== 'yes') {
		return [];
	}
	
	list(, $domain) = explode('@', strtolower($user->email));

	return elgg_get_entities([
		'type' => 'group',
		'limit' => false,
		'wheres' => [
			function (QueryBuilder $qb, $main_alias) use ($domain) {
				$md = $qb->joinMetadataTable($main_alias, 'guid', 'plugin:group_setting:group_tools:domain_based');
				
				$ors = [
					$qb->compare("{$md}.value", '=', $domain, ELGG_VALUE_STRING),
					$qb->compare("{$md}.value", 'like', "{$domain},%", ELGG_VALUE_STRING),
					$qb->compare("{$md}.value", 'like', "%,{$domain}", ELGG_VALUE_STRING),
					$qb->compare("{$md}.value", 'like', "%,{$domain},%", ELGG_VALUE_STRING),
				];
				
				return $qb->merge($ors, 'OR');
			},
		],
	]);
}

/**
 * Helper function to transfer the ownership of a group to a new user
 *
 * @param \ElggGroup $group        the group to transfer
 * @param \ElggUser  $new_owner    the new owner
 * @param bool       $remain_admin the current owner remains an admin after transfer
 *
 * @return bool
 */
function group_tools_transfer_group_ownership(\ElggGroup $group, \ElggUser $new_owner, bool $remain_admin = false): bool {
	if (!$group->canEdit()) {
		return false;
	}
	
	// register event handler to make sure transfer can complete
	elgg_register_event_handler('permissions_check', 'group', '\ColdTrick\GroupTools\Permissions::allowGroupOwnerTransfer');
	
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
	$new_owner->removeRelationship($group->guid, 'group_admin');
	
	// notify new owner
	$loggedin_user = elgg_get_logged_in_user_entity();
	if ($loggedin_user && $new_owner->guid !== $loggedin_user->guid) {
		$new_owner->notify('transfer_owner', $group, [
			'old_owner' => $old_owner,
		], $loggedin_user);
	}
	
	// check if the old owner wishes to remain as an admin
	if ($remain_admin && $old_owner->guid === $loggedin_user->guid) {
		$old_owner->addRelationship($group->guid, 'group_admin');
	}
	
	if (elgg_get_plugin_setting('owner_transfer_river', 'group_tools')) {
		elgg_create_river_item([
			'action_type' => 'owner_transfer',
			'subject_guid' => $new_owner->guid,
			'object_guid' => $group->guid,
		]);
	}
	
	// unregister event handler to make sure transfer can complete
	elgg_unregister_event_handler('permissions_check', 'group', '\ColdTrick\GroupTools\Permissions::allowGroupOwnerTransfer');
	
	return true;
}

/**
 * Get the tool presets from the plugin settings
 *
 * @return null|array
 */
function group_tools_get_tool_presets(): ?array {
	$presets = elgg_get_plugin_setting('group_tool_presets', 'group_tools');
	if (empty($presets)) {
		return null;
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
 * @param \ElggGroup $group the group to check
 *
 * @return bool
 */
function group_tools_can_assign_group_admin(\ElggGroup $group): bool {
	$user_guid = elgg_get_logged_in_user_guid();
	if (empty($user_guid)) {
		return false;
	}
	
	if (!group_tools_multiple_admin_enabled()) {
		return false;
	}
	
	if ($group->owner_guid === $user_guid || elgg_is_admin_logged_in()) {
		return true;
	} elseif ($group->group_multiple_admin_allow_enable === 'yes' && $group->canEdit($user_guid)) {
		return true;
	}
	
	return false;
}

/**
 * Check the plugin/group setting if join motivation is needed
 *
 * @param null|\ElggGroup $group (optional) the group to check for
 *
 * @return bool
 */
function group_tools_join_motivation_required(?\ElggGroup $group = null): bool {
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
	if (!$group instanceof \ElggGroup || !$check_group) {
		return ($plugin_settings || $check_group);
	}
	
	if ($group->isPublicMembership()) {
		// open group, no motivation needed
		return false;
	}
	
	// get group setting
	$group_setting = $group->join_motivation;
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
 * @param null|\ElggGroup $group (optional) the group to check
 *
 * @return bool
 */
function group_tools_group_mail_enabled(?\ElggGroup $group = null): bool {
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
	
	if (!$group instanceof \ElggGroup) {
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
 * @param null|\ElggGroup $group The group to check (can be empty to check plugin setting)
 *
 * @return bool
 */
function group_tools_group_mail_members_enabled(?\ElggGroup $group = null): bool {
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
	
	if (!$group instanceof \ElggGroup) {
		return true;
	}
	
	if ($group->canEdit()) {
		// group owners and admin can mail
		return true;
	}
	
	if ($group->isMember() && $group->mail_members_enable === 'yes') {
		return true;
	}
	
	return false;
}

/**
 * Get the helper class for stale information
 *
 * @param \ElggGroup $group          the group to get the info for
 * @param null|int   $number_of_days (optional) a number of days to check stale against, defaults to plugin setting
 *
 * @return null|StaleInfo
 */
function group_tools_get_stale_info(ElggGroup $group, ?int $number_of_days = null): ?StaleInfo {
	if (!isset($number_of_days)) {
		$number_of_days = (int) elgg_get_plugin_setting('stale_timeout', 'group_tools');
	}
	
	if ($number_of_days < 1) {
		return null;
	}
	
	return new StaleInfo($group, $number_of_days);
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
 * @return array
 */
function group_tools_get_auto_join_configuration(string $id): array {
	if (empty($id)) {
		return [];
	}
	
	$existing = group_tools_get_auto_join_configurations();
	
	return (array) elgg_extract($id, $existing, []);
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
	
	// refresh cache
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
	
	// refresh cache
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
