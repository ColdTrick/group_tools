<?php

// define for default group access
define('GROUP_TOOLS_GROUP_ACCESS_DEFAULT', -10);

require_once(dirname(__FILE__) . '/lib/functions.php');

return [
	'bootstrap' => '\ColdTrick\GroupTools\Bootstrap',
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'group_tools_group_mail',
			'class' => 'GroupMail',
		],
	],
	'settings' => [
// 		'enable_group' => 'no',
// 		'extend_widgets' => 'yes',
// 		'extend_activity' => 'no',
// 		'mention_display' => 'username',
		
	],
	'routes' => [
// 		'collection:object:thewire:group' => [
// 			'path' => '/thewire/group/{guid}',
// 			'resource' => 'thewire/group',
// 		],
// 		'collection:object:thewire:autocomplete' => [
// 			'path' => '/thewire/autocomplete',
// 			'resource' => 'thewire/autocomplete',
// 		],
// 		'collection:object:thewire:search' => [
// 			'path' => '/thewire/search/{q?}',
// 			'resource' => 'thewire/search',
// 		],
	],
	'widgets' => [
		'group_river_widget' => [
			/* 2012-05-03: restored limited functionality of group activity widget
			 * will be fully restored if Elgg fixes widget settings
			 * @todo is this still needed
			 */
			'context' => ['dashboard', 'profile', 'index', 'groups'],
			'multiple' => true,
		],
		'group_members' => [
			'context' => ['groups'],
		],
		'group_invitations' => [
			'context' => ['index', 'dashboard'],
		],
		'featured_groups' => [
			'name' => elgg_echo('groups:featured'),
			'context' => ['index'],
		],
		'index_groups' => [
			'name' => elgg_echo('groups'),
			'context' => ['index'],
			'multiple' => true,
		],
		'group_related' => [
			'context' => ['groups'],
		],
	],
	'actions' => [
		'group_tools/toggle_admin' => [],
		'group_tools/mail' => [],
		'group_tools/cleanup' => [],
		'group_tools/invite_members' => [],
		'group_tools/welcome_message' => [],
		'group_tools/domain_based' => [],
		'group_tools/related_groups' => [],
		'group_tools/remove_related_groups' => [],
		'group_tools/member_export' => [],
		'group_tools/toggle_notifications' => [],
		'group_tools/mark_not_stale' => [],
		'group_tools/join_motivation' => [],
		'group_tools/revoke_email_invitation' => [],

		'group_tools/order_groups' => ['access' => 'admin'],
		'group_tools/admin/toggle_special_state' => ['access' => 'admin'],
		'group_tools/admin/fix_acl' => ['access' => 'admin'],
		'group_tools/admin/group_tool_presets' => ['access' => 'admin'],
		'group_tools/admin/bulk_delete' => ['access' => 'admin'],
		'group_tools/admin/approve' => ['access' => 'admin'],
		'group_tools/admin/decline' => ['access' => 'admin'],
		'group_tools/admin/disable_notifications' => ['access' => 'admin'],
		'group_tools/admin/notifications' => ['access' => 'admin'],
		'group_tools/admin/auto_join/default' => ['access' => 'admin'],
		'group_tools/admin/auto_join/additional' => ['access' => 'admin'],
		'group_tools/admin/auto_join/delete' => ['access' => 'admin'],

		'groups/email_invitation' => [],
		'groups/decline_email_invitation' => [],
		'groups/edit' => [],
		'groups/invite' => [],
	],
];
