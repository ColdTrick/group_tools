<?php
/**
 * Elgg groups plugin edit action.
 *
 * If editing an existing group, only the "group_guid" must be submitted. All other form
 * elements may be omitted and the corresponding data will be left as is.
 */

// Get group fields
$input = [];

$fields = elgg()->fields->get('group', 'group');
foreach ($fields as $field) {
	$shortname = $field['name'];
	
	$value = get_input($shortname);

	if ($value === null) {
		// only submitted fields should be updated
		continue;
	}

	$input[$shortname] = $value;

	// @todo treat profile fields as unescaped: don't filter, encode on output
	if (is_array($input[$shortname])) {
		array_walk_recursive($input[$shortname], function (&$v) {
			$v = elgg_html_decode($v);
		});
	} else {
		$input[$shortname] = elgg_html_decode($input[$shortname]);
	}

	if ($field['#type'] == 'tags') {
		$input[$shortname] = elgg_string_to_array((string) $input[$shortname]);
	}
}

// only set if submitted
$name = elgg_get_title_input('name');
if (!elgg_is_empty($name)) {
	$input['name'] = $name;
}

$user = elgg_get_logged_in_user_entity();

$group_guid = (int) get_input('group_guid');

if ($group_guid) {
	$is_new_group = false;
	$group = get_entity($group_guid);
	if (!$group instanceof ElggGroup || !$group->canEdit()) {
		$error = elgg_echo('groups:cantedit');
		return elgg_error_response($error);
	}
} else {
	if (elgg_get_plugin_setting('limited_groups', 'groups') == 'yes' && !$user->isAdmin()) {
		$error = elgg_echo('groups:cantcreate');
		return elgg_error_response($error);
	}
	
	$container_guid = (int) get_input('container_guid', $user->guid);
	$container = get_entity($container_guid);
	
	if (!$container || !$container->canWriteToContainer($user->guid, 'group', 'group')) {
		$error = elgg_echo('groups:cantcreate');
		return elgg_error_response($error);
	}
	
	$is_new_group = true;
	$group = new ElggGroup();
	$group->container_guid = $container->guid;
}

// Assume we can edit or this is a new group
foreach ($input as $shortname => $value) {
	if ($value === '' && !in_array($shortname, ['name', 'description'])) {
		// The group profile displays all profile fields that have a value.
		// We don't want to display fields with empty string value, so we
		// remove the metadata completely.
		$group->deleteMetadata($shortname);
		continue;
	}

	$group->$shortname = $value;
}

// Validate create
if (!$group->name) {
	return elgg_error_response(elgg_echo('groups:notitle'));
}

// Set group tool options (only pass along saved entities)
// @todo: move this to an event handler to make sure groups created outside of the action
// get their tools configured
if ($is_new_group) {
	$tools = elgg()->group_tools->all();
	
	// store selected group tool preset
	$preset = get_input('group_tools_preset');
	if (!empty($preset)) {
		$group->group_tools_preset = $preset;
	}
} else {
	$tools = elgg()->group_tools->group($group);
}

foreach ($tools as $tool) {
	$prop_name = $tool->mapMetadataName();
	$value = get_input($prop_name);

	if (!isset($value)) {
		continue;
	}

	if ($value === 'yes') {
		$group->enableTool($tool->name);
	} else {
		$group->disableTool($tool->name);
	}
}

// Group membership - should these be treated with same constants as access permissions?
$value = get_input('membership');
if ($group->membership === null || $value !== null) {
	$is_public_membership = ($value == ACCESS_PUBLIC);
	$group->membership = $is_public_membership ? ACCESS_PUBLIC : ACCESS_PRIVATE;
}

$group->setContentAccessMode((string) get_input('content_access_mode'));

if ($is_new_group) {
	$group->access_id = ACCESS_PUBLIC;

	// if new group, we need to save so group acl gets set in event handler
	if (!$group->save()) {
		return elgg_error_response(elgg_echo('groups:save_error'));
	}
}

// Invisible group support + admin approve check
// @todo this requires save to be called to create the acl for the group. This
// is an odd requirement and should be removed. Either the acl creation happens
// in the action or the visibility moves to an event handler
$admin_approve = (bool) (elgg_get_plugin_setting('admin_approve', 'group_tools') == 'yes');
$admin_approve = ($admin_approve && !elgg_is_admin_logged_in()); // admins don't need to wait

$concept_group = (bool) elgg_get_plugin_setting('concept_groups', 'group_tools');
$concept_group = $concept_group && (bool) get_input('concept_group');

// new groups get access private, so an admin can validate it
$access_id = (int) $group->access_id;
if ($is_new_group && ($admin_approve || $concept_group)) {
	$access_id = ACCESS_PRIVATE;
	
	if ((bool) elgg_get_plugin_setting('creation_reason', 'group_tools')) {
		$reasons = (array) get_input('reasons', []);
		foreach ($reasons as $question => $answer) {
			$group->annotate("approval_reason:{$question}", serialize($answer), ACCESS_PRIVATE);
		}
	}
	
	if (!$concept_group) {
		elgg_trigger_event('admin_approval', 'group', $group);
	} else {
		$group->is_concept = true;
	}
}

if (group_tools_allow_hidden_groups()) {
	$value = get_input('vis');
	if ($is_new_group || $value !== null) {
		$visibility = (int) $value;
		
		if ($visibility == ACCESS_PRIVATE) {
			// Make this group visible only to group members. We need to use
			// ACCESS_PRIVATE on the form and convert it to group_acl here
			// because new groups do not have acl until they have been saved once.
			$acl = $group->getOwnedAccessCollection('group_acl');
			if ($acl instanceof ElggAccessCollection) {
				$visibility = $acl->id;
			}
			
			// Force all new group content to be available only to members
			$group->setContentAccessMode(ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY);
		}
		
		if (($access_id === ACCESS_PRIVATE) && ($admin_approve || $concept_group)) {
			// admins has not yet approved the group, store wanted access
			$group->intended_access_id = $visibility;
		} else {
			// already approved group
			$access_id = $visibility;
		}
	}
}

// set access
$group->access_id = $access_id;

// group default content access
$content_default_access = get_input('content_default_access');
if (isset($content_default_access)) {
	if (elgg_is_empty($content_default_access)) {
		unset($group->content_default_access);
	} else {
		$group->content_default_access = (int) $content_default_access;
	}
}

// save plugin settings
$settings = (array) get_input('settings', []);
foreach ($settings as $plugin_id => $plugin_settings) {
	if (empty($plugin_settings) || !is_array($plugin_settings)) {
		continue;
	}
	
	foreach ($plugin_settings as $name => $value) {
		if (elgg_is_empty($value)) {
			$group->removePluginSetting($plugin_id, $name);
		} else {
			$group->setPluginSetting($plugin_id, $name, $value);
		}
	}
}

if (!$group->save()) {
	return elgg_error_response(elgg_echo('groups:save_error'));
}

// join motivation
if (!$group->isPublicMembership() && group_tools_join_motivation_required()) {
	$join_motivation = get_input('join_motivation');
	$group->join_motivation = $join_motivation;
} else {
	unset($group->join_motivation);
}

// group creator needs to be member of new group and river entry created
if ($is_new_group) {
	$group->join($user);
	elgg_create_river_item([
		'view' => 'river/group/create',
		'action_type' => 'create',
		'object_guid' => $group->guid,
	]);
}

if (get_input('icon_remove')) {
	$group->deleteIcon();
} else {
	// try to save new icon, will fail silently if no icon provided
	$group->saveIconFromUploadedFile('icon');
}

if (get_input('header_remove')) {
	$group->deleteIcon('header');
} else {
	// try to save new icon, will fail silently if no icon provided
	$group->saveIconFromUploadedFile('header', 'header');
}

// owner transfer
$old_owner_guid = $is_new_group ? 0 : $group->owner_guid;
$new_owner_guid = (int) elgg_extract(0, (array) get_input('owner_guid', []));
$remain_admin = false;
if (group_tools_multiple_admin_enabled()) {
	$remain_admin = (bool) get_input('admin_transfer_remain', false);
}

if (!$is_new_group && $new_owner_guid && ($new_owner_guid != $old_owner_guid)) {
	// who can transfer
	$admin_transfer = elgg_get_plugin_setting('admin_transfer', 'group_tools');
	
	$transfer_allowed = false;
	if (($admin_transfer == 'admin') && elgg_is_admin_logged_in()) {
		$transfer_allowed = true;
	} elseif (($admin_transfer == 'owner') && (($group->owner_guid == $user->guid) || elgg_is_admin_logged_in())) {
		$transfer_allowed = true;
	}
	
	if ($transfer_allowed) {
		// get the new owner
		$new_owner = get_user($new_owner_guid);
		
		// transfer the group to the new owner
		group_tools_transfer_group_ownership($group, $new_owner, $remain_admin);
	}
}

$data = [
	'entity' => $group,
];
return elgg_ok_response($data, elgg_echo('groups:saved'), $group->getURL());
