<?php 

	$invitations = groups_get_invited_groups(get_loggedin_userid());
	echo elgg_view('groups/invitationrequests', array('invitations' => $invitations));
	
?>