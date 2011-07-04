<?php 

	$english = array(
	
		// general
		'group_tools:decline' => "Decline",
		'group_tools:revoke' => "Revoke",
		'group_tools:add_users' => "Add users",
		'group_tools:in' => "in",
	
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
	
		'group_tools:settings:mail' => "Allow group mail (allows group admins to send a message to all members)",
		
		'group_tools:settings:listing' => "Default group listing tab",
		
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
		
		// admin transfer - form
		'group_tools:admin_transfer:transfer' => "Transfer group ownership to",
		'group_tools:admin_transfer:myself' => "Myself",
		'group_tools:admin_transfer:submit' => "Tranfser",
		'group_tools:admin_transfer:no_users' => "No members or friends to transfer ownership to.",
		'group_tools:admin_transfer:confirm' => "Are you sure you wish to transfer ownership?",
		
		// group admins
		'group_tools:multiple_admin:group_admins' => "Group admins",
		'group_tools:multiple_admin:profile_actions:remove' => "Remove group admin",
		'group_tools:multiple_admin:profile_actions:add' => "Add group admin",
	
		'group_tools:multiple_admin:group_tool_option' => "Enable group admins to assign other group admins",

		// group kick
		'group_tools:group_kick:profile_actions:kick' => "Kick from group",
	
		// group mail
		'group_tools:mail:message:from' => "From group",
		
		'group_tools:mail:title' => "Send a mail to all group members",
		
		'group_tools:mail:form:title' => "Subject",
		'group_tools:mail:form:description' => "Body",
	
		// group invite
		'group_tools:groups:invite' => "Invite users",
		'group_tools:group:invite:users' => "Find user(s)",
		'group_tools:group:invite:users:description' => "Enter a name or username of a member and select him/her from the list",
		'group_tools:group:invite:email' => "Using e-mail address",
		'group_tools:group:invite:email:description' => "Enter a valid e-mail address and select it from the list",
		'group_tools:group:invite:text' => "Personal note (optional)",
		'group_tools:group:invite:add:confirm' => "Are you sure you wish to add these users directly?",

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
	
		// actions
		'group_tools:action:error:input' => "Invalid input to perform this action",
		'group_tools:action:error:entities' => "The given GUIDs didn't result in the correct entities",
		'group_tools:action:error:entity' => "The given GUID didn't result in a correct entity",
		'group_tools:action:error:edit' => "You don't have access to the given entity",
	
		// admin transfer - action
		'group_tools:action:admin_transfer:error:access' => "You're not allowed to transfer ownership of this group",
		'group_tools:action:admin_transfer:error:self' => "You can't transfer onwership to yourself, you're already the owner",
		'group_tools:action:admin_transfer:error:save' => "An unknown error occured while saving the group, please try again",
		'group_tools:action:admin_transfer:success' => "Group ownership was successfully transfered to %s",
		
		// group admins - action
		'group_tools:action:toggle_admin:error:group' => "The given input doesn't result in a group or you can't edit this group or the user is not a member",
		'group_tools:action:toggle_admin:error:remove' => "An unknown error occured while removing the user as a group admi",
		'group_tools:action:toggle_admin:error:add' => "An unknown error occured while adding the user as a group admin",
		'group_tools:action:toggle_admin:success:remove' => "The user was successfully removed as a group admin",
		'group_tools:action:toggle_admin:success:add' => "The user was successfully added as a group admin",
		
		// group kick - action
		'group_tools:action:kick:error' => "An unknown error occured while removing the user from the group",
		'group_tools:action:kick:success' => "The user was successfully removed from the group",
		
		// group mail - action
		'group_tools:action:mail:success' => "Message succesfully send",
	
		// group - invite - action
		'group_tools:action:group:invite:success' => "Successfully invited %s users to join this group",
		'group_tools:action:group:invite:success:other' => "Successfully invited %s users to join this group (%s were already a member, %s were already invited)",
		'group_tools:action:group:invite:error:no_invites' => "No invitations send (%s were already a member, %s were already invited)",
		
		'group_tools:groups:invite:add:success' => "Successfully added %s users",
		'group_tools:groups:invite:add:success:other' => "Successfully added %s users (%s were already a member)",
		'group_tools:groups:invite:add:error:no_add' => "No users were added (%s were already a member)",
		
		'group_tools:action:group:invite:email:success' => "Successfully invited %s users by e-mail to join this group",
		'group_tools:action:group:invite:email:success:other' => "Successfully invited %s users by e-mail to join this group (%s were already invited)",
		'group_tools:action:group:invite:email:error:no_invites' => "No e-mail invitations send (%s were already invited)",
		
		// group - invite - accept e-mail
		'group_tools:action:groups:email_invitation:error:input' => "Please enter an invitation code",
		'group_tools:action:groups:email_invitation:error:code' => "The entered invitation code is no longer valid",
		'group_tools:action:groups:email_invitation:error:join' => "An unknown error occured while joining the group %s, maybe you're already a member",
		'group_tools:action:groups:email_invitation:success' => "You've successfully joined the group",
		
		'' => "",
	
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
  	
?>