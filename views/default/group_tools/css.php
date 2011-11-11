<?php 


?>
#group_tools_multiple_admin_group_admins {
	margin:10px;
	-webkit-border-radius: 8px; 
	-moz-border-radius: 8px;
	background: white;	
}

#group_tools_multiple_admin_group_admins h2 {
	margin:0 0 10px 0;
	padding:5px;
	color:#0054A7;
	font-size:1.25em;
	line-height:1.2em;
}

#group_tools_group_membershipreq .search_listing {
	border: 1px solid #cecece;
}

#group_tools_group_membershipreq .search_listing:hover {
	border-color: #333333;
}

.group_tools_widget_group_members .usericon {
	float: left;
	margin: 0 5px 5px 0;
}

#group_tools_group_invite_users,
#group_tools_group_invite_email,
#group_tools_group_invite_csv {
	display: none;
	margin-bottom: 10px;
}

#group_tools_group_invite_users .group_tools_group_invite_autocomplete_result,
#group_tools_group_invite_email .group_tools_group_invite_autocomplete_email_result {
	border: 1px solid transparent;
}

#group_tools_group_invite_users .group_tools_group_invite_autocomplete_result img,
#group_tools_group_invite_email .group_tools_group_invite_autocomplete_email_result img {
	vertical-align: middle;
}

#group_tools_group_invite_users .group_tools_group_invite_autocomplete_result .group_invite_autocomplete_remove,
#group_tools_group_invite_email .group_tools_group_invite_autocomplete_email_result .group_invite_autocomplete_remove {
	vertical-align: text-top;
}

#group_tools_group_invite_users .group_invite_autocomplete_remove,
#group_tools_group_invite_email .group_invite_autocomplete_remove {
	background: url("<?php echo $vars["url"]; ?>_graphics/icon_customise_remove.png") no-repeat scroll left bottom transparent;
	cursor: pointer;
	display: none;
	width: 15px;
	height: 15px;
	margin-left: 10px;
}

#group_tools_group_invite_users .group_tools_group_invite_autocomplete_result:hover,
#group_tools_group_invite_email .group_tools_group_invite_autocomplete_email_result:hover {
	border-color: #cecece;
}

#group_tools_group_invite_users .group_tools_group_invite_autocomplete_result:hover .group_invite_autocomplete_remove,
#group_tools_group_invite_email .group_tools_group_invite_autocomplete_email_result:hover .group_invite_autocomplete_remove {
	display: inline-block;
}

#group_tools_group_invite_autocomplete_autocomplete_results,
#group_tools_group_invite_autocomplete_email_autocomplete_results {
	margin-top: 10px;
}

#group_tools_mail_member_options input[type=button] {
	margin-right: 5px;
}

#group_tools_mail_member_selection {
	display: none;
}

#group_tools_group_status {
	font-weight: normal;
}