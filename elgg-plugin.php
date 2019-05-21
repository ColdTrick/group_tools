<?php

use ColdTrick\GroupTools\Bootstrap;
use ColdTrick\GroupTools\Upgrades\FixGroupAccess;
use Elgg\Router\Middleware\Gatekeeper;

// define for default group access
define('GROUP_TOOLS_GROUP_ACCESS_DEFAULT', -10);

require_once(dirname(__FILE__) . '/lib/functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'group_listing' => 'all',
		'multiple_admin' => 'no',
		'mail' => 'no',
		'mail_members' => 'no',
		'related_groups' => 'yes',
		'member_export' => 'no',
		'admin_approve' => 'no',
		'simple_access_tab' => 'no',
		'simple_create_form' => 'no',
		'simple_tool_presets' => 'no',
		'auto_accept_membership_requests' => 'no',
		'invite' => 'no',
		'invite_email' => 'no',
		'invite_csv' => 'no',
		'invite_members' => 'no',
		'domain_based' => 'no',
		'join_motivation' => 'no',
		'notification_toggle' => 'no',
		'search_index' => 'no',

		'auto_suggest_groups' => 'yes',
		'invite_friends' => 'yes',
		'invite_email_match' => 'yes',
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'group_tools_group_mail',
			'class' => GroupMail::class,
		],
	],
	'actions' => [
		'group_tools/toggle_admin' => [],
		'group_tools/mail' => [],
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
	'routes' => [
		'add:object:group_tools_group_mail' => [
			'path' => '/groups/mail/{guid}',
			'resource' => 'groups/mail',
		],
		'collection:group:group:related' => [
			'path' => '/groups/related/{guid}',
			'resource' => 'groups/related',
		],
		'collection:group:group:membership_requests' => [
			'path' => '/groups/membership_requests/{username}',
			'resource' => 'groups/membership_requests',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'requests:invites:group:group' => [
			'path' => '/groups/requests/{guid}/invites',
			'resource' => 'groups/requests/invites',
		],
		'requests:email_invites:group:group' => [
			'path' => '/groups/requests/{guid}/email_invites',
			'resource' => 'groups/requests/email_invites',
		],
	],
	'upgrades' => [
		FixGroupAccess::class,
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
			'context' => ['index'],
		],
		'index_groups' => [
			'context' => ['index'],
			'multiple' => true,
		],
		'group_related' => [
			'context' => ['groups'],
		],
	],
];
