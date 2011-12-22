<?php 

	$english = array(
	
		// general
		'group_tools:decline' => "Decline",
		'group_tools:revoke' => "Revoke",
		'group_tools:add_users' => "Add users",
		'group_tools:in' => "in",
		'group_tools:remove' => "Remove",
		'group_tools:clear_selection' => "Clear selection",
		'group_tools:all_members' => "All members",
		
		'group_tools:joinrequest:already' => "Revoke membership request",
		'group_tools:joinrequest:already:tooltip' => "You already requested to join this group, click here to revoke this request",
		
		// menu
		'group_tools:menu:mail' => "Mail Members",
		'group_tools:menu:membership' => "Membership approval",
		
		// plugin settings
		'group_tools:settings:admin_transfer' => "Allow group owner transfer",
		'group_tools:settings:admin_transfer:admin' => "Site admin only",
		'group_tools:settings:admin_transfer:owner' => "Group owners and site admins",
		
		'group_tools:settings:multiple_admin' => "Allow multiple group admins",
		
		'group_tools:settings:invite' => "Allow all users to be invited (not just friends)",
		'group_tools:settings:invite_email' => "Allow all users to be invited by e-mail address",
		'group_tools:settings:invite_csv' => "Allow all users to be invited by CSV-file",
	
		'group_tools:settings:mail' => "Allow group mail (allows group admins to send a message to all members)",
		
		'group_tools:settings:listing' => "Default group listing tab",
		
		'group_tools:settings:search_index' => "Allow closed groups to be indexed by search engines",
		'group_tools:settings:auto_notification' => "Automatically enable group notification on group join",
		'group_tools:settings:auto_join' => "Auto join groups",
		'group_tools:settings:auto_join:description' => "New users will automaticly join the following groups",
		
		// group invite message
		'group_tools:groups:invite:body' => "Hi %s,

%s invited you to join the '%s' group. 
%s

Click below to view your invitations:
%s",
	
		// group add message
		'group_tools:groups:invite:add:subject' => "You've been added to the group %s",
		'group_tools:groups:invite:add:body' => "Hi %s,
	
%s added you to the group %s.
%s

To view the group click on this link
%s",
		// group invite by email
		'group_tools:groups:invite:email:subject' => "You've been invited for the group %s",
		'group_tools:groups:invite:email:body' => "Hi,

%s invited you to join the group %s on %s.
%s

If you don't have an account on %s please register here
%s

If you already have an account or after you registered, please click on the following link to accept the invitation
%s

You can also go to All site groups -> Group invitations and enter the following code:
%s",
		// group transfer notification
		'group_tools:notify:transfer:subject' => "Administration of the group %s has been appointed to you",
		'group_tools:notify:transfer:message' => "Hi %s,
		
%s has appointed you as the new administrator of the group %s. 

To visit the group please click on the following link:
%s",
		
		// group edit tabbed
		'group_tools:group:edit:profile' => "Group profile / tools",
		'group_tools:group:edit:other' => "Other options",

		// admin transfer - form
		'group_tools:admin_transfer:title' => "Transfer the ownership of this group",
		'group_tools:admin_transfer:transfer' => "Transfer group ownership to",
		'group_tools:admin_transfer:myself' => "Myself",
		'group_tools:admin_transfer:submit' => "Tranfser",
		'group_tools:admin_transfer:no_users' => "No members or friends to transfer ownership to.",
		'group_tools:admin_transfer:confirm' => "Are you sure you wish to transfer ownership?",
		
		// auto join form
		'group_tools:auto_join:title' => "Auto join options",
		'group_tools:auto_join:add' => "%sAdd this group%s to the auto join groups. This will mean that new users are automaticly added to this group on registration.",
		'group_tools:auto_join:remove' => "%sRemove this group%s from the auto join groups. This will mean that new users will no longer automaticly join this group on registration.",
		'group_tools:auto_join:fix' => "To make all site members a member of this group, please %sclick here%s.",
		
		// group admins
		'group_tools:multiple_admin:group_admins' => "Group admins",
		'group_tools:multiple_admin:profile_actions:remove' => "Remove group admin",
		'group_tools:multiple_admin:profile_actions:add' => "Add group admin",
	
		'group_tools:multiple_admin:group_tool_option' => "Enable group admins to assign other group admins",

		// group profile widgets
		'group_tools:profile_widgets:title' => "Show group profile widgets to non members",
		'group_tools:profile_widgets:description' => "This is a closed group. Default no widgets are shown to non members. Here you can configure if you whish to change that.",
		'group_tools:profile_widgets:option' => "Allow non members to view widgets on the group profile page:",
		
		// group mail
		'group_tools:mail:message:from' => "From group",
		
		'group_tools:mail:title' => "Send a mail to the group members",
		'group_tools:mail:form:recipients' => "Number of recipients",
		'group_tools:mail:form:members:selection' => "Select individual members",
		
		'group_tools:mail:form:title' => "Subject",
		'group_tools:mail:form:description' => "Body",
	
		'group_tools:mail:form:js:members' => "Please select at least one member to send the message to",
		'group_tools:mail:form:js:description' => "Please enter a message",
		
		// group invite
		'group_tools:groups:invite:title' => "Invite users to this group",
		'group_tools:groups:invite' => "Invite users",
		
		'group_tools:group:invite:users' => "Find user(s)",
		'group_tools:group:invite:users:description' => "Enter a name or username of a member and select him/her from the list",
		
		'group_tools:group:invite:email' => "Using e-mail address",
		'group_tools:group:invite:email:description' => "Enter a valid e-mail address and select it from the list",
		
		'group_tools:group:invite:csv' => "Using CSV upload",
		'group_tools:group:invite:csv:description' => "You can upload a CSV file with users to invite.<br />The format must be: displayname;e-mail address. There shouldn't be a header line.",
		
		'group_tools:group:invite:text' => "Personal note (optional)",
		'group_tools:group:invite:add:confirm' => "Are you sure you wish to add these users directly?",
		
		'group_tools:group:invite:resend' => "Resend invitations to users who already have been invited",

		'group_tools:groups:invitation:code:title' => "Group invitation by e-mail",
		'group_tools:groups:invitation:code:description' => "If you have received an invitation to join a group by e-mail, you can enter the invitation code here to accept the invitation. If you click on the link in the invitation e-mail the code will be entered for you.",
	
		// group membership requests
		'group_tools:groups:membershipreq:requests' => "Membership requests",
		'group_tools:groups:membershipreq:invitations' => "Outstanding invitations",
		'group_tools:groups:membershipreq:invitations:none' => "No outstanding invitations",
		'group_tools:groups:membershipreq:invitations:revoke:confirm' => "Are you sure you wish to revoke this invitation",
	
		// group invitations
		'group_tools:group:invitations:request' => "Outstanding membership requests",
		'group_tools:group:invitations:request:revoke:confirm' => "Are you sure you wish to revoke your membership request?",
		'group_tools:group:invitations:request:non_found' => "There are no outstanding membership requests at this time",
	
		// group listing
		'group_tools:groups:sorting:alfabetical' => "Alfabetical",
		'group_tools:groups:sorting:open' => "Open",
		'group_tools:groups:sorting:closed' => "Closed",
	
		// actions
		'group_tools:action:error:input' => "Invalid input to perform this action",
		'group_tools:action:error:entities' => "The given GUIDs didn't result in the correct entities",
		'group_tools:action:error:entity' => "The given GUID didn't result in a correct entity",
		'group_tools:action:error:edit' => "You don't have access to the given entity",
		'group_tools:action:error:save' => "There was an error while saving the settings",
		'group_tools:action:success' => "The settings where saved successfully",
	
		// admin transfer - action
		'group_tools:action:admin_transfer:error:access' => "You're not allowed to transfer ownership of this group",
		'group_tools:action:admin_transfer:error:self' => "You can't transfer onwership to yourself, you're already the owner",
		'group_tools:action:admin_transfer:error:save' => "An unknown error occured while saving the group, please try again",
		'group_tools:action:admin_transfer:success' => "Group ownership was successfully transfered to %s",
		
		// group admins - action
		'group_tools:action:toggle_admin:error:group' => "The given input doesn't result in a group or you can't edit this group or the user is not a member",
		'group_tools:action:toggle_admin:error:remove' => "An unknown error occured while removing the user as a group admin",
		'group_tools:action:toggle_admin:error:add' => "An unknown error occured while adding the user as a group admin",
		'group_tools:action:toggle_admin:success:remove' => "The user was successfully removed as a group admin",
		'group_tools:action:toggle_admin:success:add' => "The user was successfully added as a group admin",
		
		// group mail - action
		'group_tools:action:mail:success' => "Message succesfully send",
	
		// group - invite - action
		'group_tools:action:group:invite:success' => "Successfully invited %s users to join this group",
		'group_tools:action:group:invite:success:other' => "Successfully invited %s users to join this group (%s were already a member, %s were already invited)",
		'group_tools:action:group:invite:error:no_invites' => "No invitations send (%s were already a member, %s were already invited)",
		
		'group_tools:action:groups:invite:add:success' => "Successfully added %s users",
		'group_tools:action:groups:invite:add:success:other' => "Successfully added %s users (%s were already a member)",
		'group_tools:action:groups:invite:add:error:no_add' => "No users were added (%s were already a member)",
		
		'group_tools:action:group:invite:email:success' => "Successfully invited %s users by e-mail to join this group",
		'group_tools:action:group:invite:email:success:other' => "Successfully invited %s users by e-mail to join this group (%s were already invited)",
		'group_tools:action:group:invite:email:error:no_invites' => "No e-mail invitations send (%s were already invited)",
		
		'group_tools:action:group:invite:csv:error:no_invites' => "No invitations send from CSV (%s were already a member, %s were already invited)",
		'group_tools:action:group:invite:success:other' => "Successfully invited %s users from CSV to join this group (%s were already a member, %s were already invited)",
		'group_tools:action:group:invite:success' => "Successfully invited %s from CSV to join this group",
		
		// group - invite - accept e-mail
		'group_tools:action:groups:email_invitation:error:input' => "Please enter an invitation code",
		'group_tools:action:groups:email_invitation:error:code' => "The entered invitation code is no longer valid",
		'group_tools:action:groups:email_invitation:error:join' => "An unknown error occured while joining the group %s, maybe you're already a member",
		'group_tools:action:groups:email_invitation:success' => "You've successfully joined the group",
		
		// group toggle auto join
		'group_tools:action:toggle_auto_join:error:save' => "An error occured while saving the new settings",
		'group_tools:action:toggle_auto_join:success' => "The new settings were saved successfully",
		
		// group fix auto_join
		'group_tools:action:fix_auto_join:success' => "Group membership fixed: %s new members, %s were already a member and %s failures",
		
	);
	
	add_translation("en", $english);

	$group_river_widget = array(
		'widgets:group_river_widget:title' => "Group activity",
	    'widgets:group_river_widget:description' => "Shows the activity of a group in a widget",
	
	    'widgets:group_river_widget:edit:num_display' => "Number of activities",
		'widgets:group_river_widget:edit:group' => "Select a group",
		'widgets:group_river_widget:edit:no_groups' => "You need to be a member of at least one group to use this widget",

		'widgets:group_river_widget:view:not_configured' => "This widget is not yet configured",
	
		'widgets:group_river_widget:view:more' => "Activity in the '%s' group",
		'widgets:group_river_widget:view:noactivity' => "We could not find any activity.",
	
 	);

  	add_translation("en", $group_river_widget);
  	
  	$group_members_widget = array(
  		'widgets:group_members:title' => "Group members",
  		'widgets:group_members:description' => "Shows the members of this group",
  		
  		'widgets:group_members:edit:num_display' => "How many members to show",
  		'widgets:group_members:view:no_members' => "No group members found",
  	);
  	
  	add_translation("en", $group_members_widget);
	
  	$group_invitations_widget = array(
  		'widgets:group_invitations:title' => "Group invitations",
  		'widgets:group_invitations:description' => "Shows the outstanding group invitations for the current user",
  	);
  	
  	add_translation("en", $group_invitations_widget);
  	