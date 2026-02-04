<?php
/**
 * Group edit form
 *
 * This view contains everything related to group access.
 * eg: how can people join this group, who can see the group, etc
 */

elgg_import_esm('groups/edit/access');

$entity = elgg_extract('entity', $vars, false);
$membership = elgg_extract('membership', $vars);
$visibility = elgg_extract('vis', $vars);
$owner_guid = elgg_extract('owner_guid', $vars);
$content_access_mode = elgg_extract('content_access_mode', $vars);
$show_content_default_access = (bool) elgg_extract('show_content_default_access', $vars, true);
$content_default_access = elgg_extract('content_default_access', $vars);
$show_group_owner_transfer = (bool) elgg_extract('show_group_owner_transfer', $vars, true);

$show_visibility = group_tools_allow_hidden_groups();
$show_visibility = ($show_visibility && (empty($entity->guid) || (!$entity->admin_approval && !$entity->is_concept)));

$show_motivation_option = group_tools_join_motivation_required();
$motivation_plugin_setting = elgg_get_plugin_setting('join_motivation', 'group_tools', 'no');
$show_motivation_option = ($show_motivation_option && str_starts_with($motivation_plugin_setting, 'yes'));

// group membership
echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('groups:membership'),
	'name' => 'membership',
	'id' => 'groups-membership',
	'value' => $membership,
	'options_values' => [
		ACCESS_PRIVATE => elgg_echo('groups:access:private'),
		ACCESS_PUBLIC => elgg_echo('groups:access:public'),
	],
]);

if ($show_motivation_option) {
	$checked = ($motivation_plugin_setting === 'yes_on');
	if ($entity instanceof \ElggGroup) {
		$group_setting = $entity->join_motivation;
		if (!empty($group_setting)) {
			$checked = ($group_setting === 'yes');
		}
	}
	
	$join_motivation = elgg_view_field([
		'#type' => 'checkbox',
		'#label' => elgg_echo('group_tools:join_motivation:edit:option:label'),
		'#help' => elgg_echo('group_tools:join_motivation:edit:option:description'),
		'name' => 'join_motivation',
		'default' => 'no',
		'value' => 'yes',
		'checked' => $checked,
	]);
	
	echo elgg_format_element('div', [
		'id' => 'group-tools-join-motivation',
		'class' => ($membership === ACCESS_PRIVATE) ? '' : 'hidden',
	], $join_motivation);
}

// group access (hidden groups)
if ($show_visibility) {
	$visibility_options = [
		ACCESS_PRIVATE => elgg_echo('groups:access:group'),
		ACCESS_LOGGED_IN => elgg_echo('access:label:logged_in'),
		ACCESS_PUBLIC => elgg_echo('access:label:public'),
	];

	if (elgg_get_config('walled_garden')) {
		unset($visibility_options[ACCESS_PUBLIC]);
		
		if ($visibility == ACCESS_PUBLIC) {
			$visibility = ACCESS_LOGGED_IN;
		}
	}

	echo elgg_view_field([
		'#type' => 'access',
		'#label' => elgg_echo('groups:visibility'),
		'name' => 'vis',
		'id' => 'groups-vis',
		'value' => $visibility,
		'options_values' => $visibility_options,
		'entity' => $entity,
		'entity_type' => 'group',
		'entity_subtype' => '',
	]);
}

// group content access mode
$access_mode_params = [
	'#type' => 'select',
	'#label' => elgg_echo('groups:content_access_mode'),
	'name' => 'content_access_mode',
	'id' => 'groups-content-access-mode',
	'value' => $content_access_mode,
	'options_values' => [
		\ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED => elgg_echo('groups:content_access_mode:unrestricted'),
		\ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY => elgg_echo('groups:content_access_mode:membersonly'),
	],
];

if ($entity instanceof \ElggGroup) {
	// Disable content_access_mode field for hidden groups because the setting
	// will be forced to members_only regardless of the entered value
	$acl = $entity->getOwnedAccessCollection('group_acl');
	if ($acl instanceof \ElggAccessCollection && ($entity->access_id === $acl->id) && !$entity->admin_approval && !$entity->is_concept) {
		$access_mode_params['disabled'] = 'disabled';
	}
	
	if ($entity->getContentAccessMode() == ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED) {
		// Warn the user that changing the content access mode to more
		// restrictive will not affect the existing group content
		$access_mode_params['#help'] = elgg_echo('groups:content_access_mode:warning');
	}
}

echo elgg_view_field($access_mode_params);

// group default access
if ($show_content_default_access) {
	$content_default_access_options = [
		'' => elgg_echo('groups:content_default_access:not_configured'),
		ACCESS_PRIVATE => elgg_echo('groups:access:group'),
		ACCESS_LOGGED_IN => elgg_echo('access:label:logged_in'),
	];
	if (!elgg_get_config('walled_garden')) {
		$content_default_access_options[ACCESS_PUBLIC] = elgg_echo('access:label:public');
	}
	
	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('groups:content_default_access'),
		'#help' => elgg_echo('groups:content_default_access:help'),
		'name' => 'content_default_access',
		'value' => $content_default_access,
		'options_values' => $content_default_access_options,
	]);
}

// next stuff only when entity exists
if (!$entity) {
	return;
}

// transfer owner
echo elgg_view('group_tools/forms/admin_transfer', $vars);
