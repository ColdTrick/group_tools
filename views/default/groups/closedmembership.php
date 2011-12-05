<?php
	/**
	 * Elgg groups plugin full profile view (for a closed group you haven't joined).
	 * 
	 * @package ElggGroups
	 */

	$group = $vars["entity"];

?>
<div id="groups_closed_membership">
	<p>
		<?php
			echo elgg_echo('groups:closedgroup');
			if (isloggedin()) {
				echo ' ' . elgg_echo('groups:closedgroup:request');
			}
		?>
	</p>
</div>

<?php 
	if(!empty($group) && ($group instanceof ElggGroup)){
		if(($group->membership != ACCESS_PUBLIC) && ($group->profile_widgets == yes)){
			echo elgg_view("groups/profileitems", $vars);
		}
	}