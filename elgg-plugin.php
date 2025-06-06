<?php

use ColdTrick\GroupTools\Bootstrap;
use ColdTrick\GroupTools\Notifications\GroupAdminApprovalNotificationHandler;
use ColdTrick\GroupTools\Notifications\GroupMailEnqueueNotificationEventHandler;
use ColdTrick\GroupTools\Upgrades\MigrateNotificationSettings;
use Elgg\Router\Middleware\Gatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerCanEditGatekeeper;
use Elgg\Router\Middleware\GroupPageOwnerGatekeeper;
use Elgg\Router\Middleware\UserPageOwnerCanEditGatekeeper;

require_once(dirname(__FILE__) . '/lib/functions.php');

return [
	'plugin' => [
		'version' => '21.0',
		'dependencies' => [
			'groups' => [
				'position' => 'after',
			],
			'profile_manager' => [
				'must_be_active' => false,
				'position' => 'after',
			],
		],
	],
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'group_listing' => 'all',
		'multiple_admin' => 'no',
		'mail' => 'no',
		'mail_members' => 'no',
		'related_groups' => 'yes',
		'admin_approve' => 'no',
		'creation_reason' => 0,
		'concept_groups' => 0,
		'admin_transfer' => 'no',
		'owner_transfer_river' => 0,
		'simple_access_tab' => 'no',
		'simple_tool_presets' => 'no',
		'auto_accept_membership_requests' => 'no',
		'invite_email' => 'no',
		'invite_csv' => 'no',
		'domain_based' => 'no',
		'join_motivation' => 'no',
		'notification_toggle' => 'no',
		'search_index' => 'no',
		'auto_suggest_groups' => 'yes',
	],
	'entities' => [
		[
			'type' => 'object',
			'subtype' => 'group_tools_group_mail',
			'class' => GroupMail::class,
			'capabilities' => [
				'commentable' => false,
			],
		],
	],
	'actions' => [
		'group_tools/toggle_admin' => [],
		'group_tools/mail' => [],
		'group_tools/related_groups' => [],
		'group_tools/remove_related_groups' => [],
		'group_tools/toggle_notifications' => [],
		'group_tools/mark_not_stale' => [],
		'group_tools/join_motivation' => [],
		'group_tools/remove_concept_status' => [],
		'group_tools/revoke_email_invitation' => [],

		'group_tools/order_groups' => ['access' => 'admin'],
		'group_tools/admin/toggle_special_state' => ['access' => 'admin'],
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
	'events' => [
		'action:validate' => [
			'groups/edit' => [
				'\ColdTrick\GroupTools\Plugins\Groups::editActionListener' => [],
			],
			'groups/join' => [
				'\ColdTrick\GroupTools\Membership::groupJoinAction' => [],
			],
		],
		'create' => [
			'user' => [
				'\ColdTrick\GroupTools\Membership::createUserGroupInviteCode' => [],
			],
		],
		'cron' => [
			'daily' => [
				'\ColdTrick\GroupTools\Cron::notifyStaleGroupOwners' => [],
				'\ColdTrick\GroupTools\Cron::removeExpiredConceptGroups' => [],
			],
			'fiveminute' => [
				'\ColdTrick\GroupTools\Membership::autoJoinGroupsCron' => [],
			],
			'weekly' => [
				'\ColdTrick\GroupTools\Cron::notifyConceptGroupOwners' => [],
			],
		],
		'delete' => [
			'relationship' => [
				'\ColdTrick\GroupTools\Membership::deleteRequest' => [],
			],
		],
		'entity:url' => [
			'object' => [
				'\ColdTrick\GroupTools\Widgets::widgetURL' => [],
			],
		],
		'export_value' => [
			'csv_exporter' => [
				'\ColdTrick\GroupTools\Plugins\CSVExporter::exportApprovalReasons' => [],
				'\ColdTrick\GroupTools\Plugins\CSVExporter::exportGroupAdminsForGroups' => [],
				'\ColdTrick\GroupTools\Plugins\CSVExporter::exportGroupAdminsForUsers' => [],
				'\ColdTrick\GroupTools\Plugins\CSVExporter::exportStaleInfo' => [],
			],
		],
		'get' => [
			'subscriptions' => [
				'\ColdTrick\GroupTools\GroupAdmins::addGroupAdminsToMembershipRequest' => [],
			],
		],
		'get_exportable_values' => [
			'csv_exporter' => [
				'\ColdTrick\GroupTools\Plugins\CSVExporter::addApprovalReasons' => [],
				'\ColdTrick\GroupTools\Plugins\CSVExporter::addGroupAdminsToGroups' => [],
				'\ColdTrick\GroupTools\Plugins\CSVExporter::addGroupAdminsToUsers' => [],
				'\ColdTrick\GroupTools\Plugins\CSVExporter::addStaleInfo' => [],
			],
		],
		'get_exportable_values:group' => [
			'csv_exporter' => [
				'\ColdTrick\GroupTools\Plugins\CSVExporter::allowUserGroupValues' => [],
			],
		],
		'group_tool_widgets' => [
			'widget_manager' => [
				'\ColdTrick\GroupTools\Plugins\WidgetManager::groupToolWidgets' => [],
			],
		],
		'handlers' => [
			'widgets' => [
				'\ColdTrick\GroupTools\Widgets::unregisterGroupActivityWidget' => [],
				'\ColdTrick\GroupTools\Widgets::unregisterRelatedGroupsWidget' => [],
			],
		],
		'head' => [
			'page' => [
				'\ColdTrick\GroupTools\PageLayout::noIndexClosedGroups' => [],
			],
		],
		'join' => [
			'group' => [
				'\ColdTrick\GroupTools\Membership::groupJoin' => [],
			],
		],
		'leave' => [
			'group' => [
				'\ColdTrick\GroupTools\Plugins\Groups::removeGroupAdminOnLeave' => [],
			],
		],
		'login:after' => [
			'user' => [
				'\ColdTrick\GroupTools\Membership::autoJoinGroupsLogin' => [],
			],
		],
		'permissions_check' => [
			'group' => [
				'\ColdTrick\GroupTools\Permissions::allowGroupAdminsToEdit' => [],
			],
		],
		'plugin_setting' => [
			'group' => [
				'\ColdTrick\GroupTools\PluginSettings::saveGroupSettings' => [],
			],
		],
		'register' => [
			'menu:admin_header' => [
				'\ColdTrick\GroupTools\Menus\AdminHeader::registerAdminItems' => ['priority' => 501],
			],
			'menu:annotation' => [
				'\ColdTrick\GroupTools\Menus\Annotation::registerEmailInvitation' => [],
				'\ColdTrick\GroupTools\Menus\Annotation::registerEmailInvitationUser' => [],
			],
			'menu:entity' => [
				'\ColdTrick\GroupTools\Menus\Entity::addRemoveFromGroup' => [],
				'\ColdTrick\GroupTools\Menus\Entity::registerApprovalReasons' => [],
				'\ColdTrick\GroupTools\Menus\Entity::relatedGroup' => [],
				'\ColdTrick\GroupTools\Menus\Entity::suggestedGroup' => [],
				'\ColdTrick\GroupTools\Menus\Entity::assignGroupAdmin' => [],
			],
			'menu:filter:groups/all' => [
				'\ColdTrick\GroupTools\Menus\Filter\GroupsAll::addTabs' => ['priority' => 550],
				'\ColdTrick\GroupTools\Menus\Filter\GroupsAll::cleanupTabs' => ['priority' => 900],
			],
			'menu:filter:groups/invitations' => [
				'\ColdTrick\GroupTools\Menus\Filter::registerUserEmailInvitations' => [],
			],
			'menu:filter:groups/members' => [
				'\ColdTrick\GroupTools\Menus\Filter\GroupsMembers::registerGroupAdmins' => [],
				'\ColdTrick\GroupTools\Menus\Filter\GroupsMembers::registerEmailInvitations' => [],
			],
			'menu:owner_block' => [
				'\ColdTrick\GroupTools\Menus\OwnerBlock::relatedGroups' => [],
			],
			'menu:page' => [
				'\ColdTrick\GroupTools\Menus\Page::registerGroupMail' => [],
			],
			'menu:relationship' => [
				'\ColdTrick\GroupTools\Menus\Relationship::groupDeclineMembershipReason' => ['priority' => 501],
			],
			'menu:title' => [
				'\ColdTrick\GroupTools\Menus\Title::addGroupToolPresets' => [],
				'\ColdTrick\GroupTools\Menus\Title::conceptGroup' => ['priority' => 9999],
				'\ColdTrick\GroupTools\Menus\Title::groupAdminStatus' => ['priority' => 501],
				'\ColdTrick\GroupTools\Menus\Title::groupMembership' => ['priority' => 501],
				'\ColdTrick\GroupTools\Menus\Title::pendingApproval' => ['priority' => 9998],
			],
			'menu:user_hover' => [
				'\ColdTrick\GroupTools\Menus\Entity::assignGroupAdmin' => [],
			],
		],
		'prepare' => [
			'notification:membership_request:group:group' => [
				'\ColdTrick\GroupTools\GroupAdmins::prepareMembershipRequestMessage' => [],
			],
		],
		'send:after' => [
			'notifications' => [
				'\ColdTrick\GroupTools\Notifications::sendConfirmationOfGroupAdminApprovalToOwner' => [],
				'\ColdTrick\GroupTools\GroupMail::cleanup' => [],
			],
		],
		'tool_options' => [
			'group' => [
				'\ColdTrick\GroupTools\Plugins\Groups::registerGroupTools' => [],
			],
		],
		'types:custom_group_field' => [
			'profile_manager' => [
				'\ColdTrick\GroupTools\Plugins\ProfileManager::registerGroupFields' => [],
			],
		],
		'validate:after' => [
			'user' => [
				'\ColdTrick\GroupTools\Membership::createUserEmailInvitedGroups' => [],
				'\ColdTrick\GroupTools\Membership::createUserDomainBasedGroups' => [],
				'\ColdTrick\GroupTools\Membership::autoJoinGroups' => [],
			],
		],
		'view_vars' => [
			'forms/groups/edit' => [
				'\ColdTrick\GroupTools\Views::prepareGroupApprovalReasons' => [],
			],
			'groups/edit/access' => [
				'\ColdTrick\GroupTools\Views::allowGroupOwnerTransfer' => [],
				'\ColdTrick\GroupTools\Views::showSimplefiedAccess' => [],
			],
			'input/form' => [
				'\ColdTrick\GroupTools\Views::allowDoubleSubmitWhenConceptGroupsEnabled' => [],
			],
			'relationship/membership_request' => [
				'\ColdTrick\GroupTools\Views::addJoinMotivationToGroupMembershipRequest' => [],
			],
			'resources/groups/all' => [
				'\ColdTrick\GroupTools\Views::prepareGroupAll' => [],
			],
		],
	],
	'notifications' => [
		'group' => [
			'group' => [
				'admin_approval' => GroupAdminApprovalNotificationHandler::class,
			],
		],
		'object' => [
			'group_tools_group_mail' => [
				'enqueue-mail' => GroupMailEnqueueNotificationEventHandler::class,
			],
		]
	],
	'routes' => [
		'add:object:group_tools_group_mail' => [
			'path' => '/groups/mail/{guid}',
			'resource' => 'groups/mail',
			'middleware' => [
				Gatekeeper::class,
			],
		],
		'collection:group:group:related' => [
			'path' => '/groups/related/{guid}',
			'resource' => 'groups/related',
			'middleware' => [
				GroupPageOwnerGatekeeper::class,
			],
		],
		'collection:annotation:email_invitation:group' => [
			'path' => '/groups/invites/{guid}/email_invitations',
			'resource' => 'groups/email_invitations',
			'middleware' => [
				Gatekeeper::class,
				GroupPageOwnerCanEditGatekeeper::class,
			],
		],
		'collection:annotation:email_invitation:user' => [
			'path' => '/groups/invitations/{username}/email',
			'resource' => 'groups/user/email_invitations',
			'middleware' => [
				Gatekeeper::class,
				UserPageOwnerCanEditGatekeeper::class,
			],
		],
	],
	'upgrades' => [
		MigrateNotificationSettings::class,
	],
	'view_extensions' => [
		'admin.css' => [
			'group_tools/admin.css' => [],
		],
		'elgg.css' => [
			'group_tools/site.css' => [],
		],
		'groups/edit' => [
			'group_tools/extends/groups/edit/admin_approve' => ['priority' => 1],
		],
		'groups/edit/settings' => [
			'group_tools/extends/groups/edit/settings/domain_based' => [],
			'group_tools/extends/groups/edit/settings/notifications' => [],
			'group_tools/extends/groups/edit/settings/welcome_message' => [],
		],
		'groups/profile/summary' => [
			'group_tools/extends/groups/edit/admin_approve' => [],
			'group_tools/extends/groups/profile/concept' => [],
			'group_tools/extends/groups/profile/stale_message' => [],
		],
		'groups/sidebar/members' => [
			'group_tools/group_admins' => ['priority' => 400],
		],
		'notifications/settings/records' => [
			'group_tools/notifications/settings' => [],
		],
		'register/extend' => [
			'group_tools/register_extend' => [],
		],
	],
	'view_options' => [
		'forms/group_tools/admin/auto_join/additional' => ['ajax' => true],
		'forms/group_tools/admin/auto_join/default' => ['ajax' => true],
		'forms/groups/killrequest' => ['ajax' => true],
		'group_tools/elements/auto_join_match_pattern' => ['ajax' => true],
		'group_tools/forms/motivation' => ['ajax' => true],
		'group_tools/group/reasons' => ['ajax' => true],
		'group_tools/group/suggested' => ['ajax' => true],
	],
	'widgets' => [
		'group_river_widget' => [
			/* 2012-05-03: restored limited functionality of group activity widget
			 * will be fully restored if Elgg fixes widget settings
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
