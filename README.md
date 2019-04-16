Group Tools
===========

![Elgg 3.0](https://img.shields.io/badge/Elgg-3.0-green.svg)
[![Build Status](https://scrutinizer-ci.com/g/ColdTrick/group_tools/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ColdTrick/group_tools/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ColdTrick/group_tools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ColdTrick/group_tools/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/coldtrick/group_tools/v/stable.svg)](https://packagist.org/packages/coldtrick/group_tools)
[![License](https://poser.pugx.org/coldtrick/group_tools/license.svg)](https://packagist.org/packages/coldtrick/group_tools)

Combines different group additions into one plugin

Features
--------

- group multiple admin
- group activity (profile,dashboard,index,group widget)
	- can show more than one group activity
	- has support for river_comments and like
- group members widget (group only)
- group invitations widget (index, dashboard)
- group mail (requires a working Elgg cron setup)
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

Group default access
--------------------

As of version 2.3 of this plugin we offer the ability to set a group default access level (like with Site and User), 
this will however not work if you haven't applied https://github.com/Elgg/Elgg/pull/253 to your Elgg installation.

This fix is in place for Elgg 1.9, so no modifications will be needed to the Elgg core files.
