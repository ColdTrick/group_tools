Group Tools
===========
Combines different group additions into one plugin

Contents
--------
1. Features
2. Replaces
3. ToDo
4. Group default access

1. Features
-----------
- group multiple admin
- group activity (profile,dashboard,index,group widget)
	- can show more than one group activity
	- has support for river_comments and like
- group members widget (group only)
- group invitations widget (index, dashboard)
- group mail
- group invite
	- better error messages
	- free text with invite
	- listing of outstanding invitations
	- personal invitation revoke
	- add users to group
	- all users (admin option)
	- import users from csv
- group listing
	- alfabetical
	
2. Replaces
-----------
- group_multiple_admin
- group_mail
- groupriver

3. ToDo
-------
- group listing
	- latest activity in my groups
	- my groups (and remove menu item)
	- owned groups (and remove menu item)
- group icon cron tool 
- group invite
	- preview message
	- preview csv sample
	- invitation report
		- per user what happend
	- better description of options
- group widgets
	- group listing on popular
	- index widget show member count
- group mail for group members (group optional)
- group discussions tools menu item (all discussions) (optional)
- group open/closed
	- admin toggle
	- better CSS (use class instead of id)
- when group transfer remove group admin (if needed)

4. Group default access
-----------------------
As of version 2.3 of this plugin we offer the ability to set a group default access level (like with Site and User), 
this will however not work if you haven't applied https://github.com/Elgg/Elgg/pull/253 to your Elgg installation.

Hopefully this will be integrated into Elgg core soon, so no modifications will be needed to the Elgg core files.