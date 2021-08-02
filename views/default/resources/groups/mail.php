<?php
/**
 * Mail group members
 */

use Elgg\Exceptions\Http\EntityPermissionsException;

$group_guid = (int) elgg_extract('guid', $vars);
elgg_entity_gatekeeper($group_guid, 'group');

$group = get_entity($group_guid);
if (!group_tools_group_mail_enabled($group) && !group_tools_group_mail_members_enabled($group)) {
	throw new EntityPermissionsException();
}

// set page owner
elgg_set_page_owner_guid($group->guid);
elgg_set_context('groups');

// set breadcrumb
elgg_push_breadcrumb(elgg_echo('groups'), elgg_generate_url('collection:group:group:all'));
elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());

// build page elements
$form_vars = [
	'id' => 'group_tools_mail_form',
];
$body_vars = [
	'entity' => $group,
];
$form = elgg_view_form('group_tools/mail', $form_vars, $body_vars);

// draw page
echo elgg_view_page(elgg_echo('group_tools:mail:title'), [
	'content' => $form,
	'filter_id' => 'groups/mail',
	'filter_value' => 'mail',
]);
