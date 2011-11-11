<?php

	/**
	 * A simple view to provide the user with group filters and the number of group on the site
	 **/
	 
	 $num_groups = (int) $vars['count'];
	 if(!$num_groups){
	 	$num_groups = 0;
	 }
	 	
	 $filter = $vars['filter'];
	 
	 //url
	 $url = $vars['url'] . "pg/groups/all/";

?>
<div id="elgg_horizontal_tabbed_nav">
<ul>
	<li <?php if($filter == "active") echo "class='selected'"; ?>><a href="<?php echo $url; ?>?filter=active"><?php echo elgg_echo('groups:latestdiscussion'); ?></a></li>
	<li <?php if($filter == "newest") echo "class='selected'"; ?>><a href="<?php echo $url; ?>?filter=newest"><?php echo elgg_echo('groups:newest'); ?></a></li>
	<li <?php if($filter == "pop") echo "class='selected'"; ?>><a href="<?php echo $url; ?>?filter=pop"><?php echo elgg_echo('groups:popular'); ?></a></li>
	<li <?php if($filter == "open") echo "class='selected'"; ?>><a href="<?php echo $url; ?>?filter=open"><?php echo elgg_echo('group_tools:groups:sorting:open'); ?></a></li>
	<li <?php if($filter == "closed") echo "class='selected'"; ?>><a href="<?php echo $url; ?>?filter=closed"><?php echo elgg_echo('group_tools:groups:sorting:closed'); ?></a></li>
	<li <?php if($filter == "alfa") echo "class='selected'"; ?>><a href="<?php echo $url; ?>?filter=alfa"><?php echo elgg_echo('group_tools:groups:sorting:alfabetical'); ?></a></li>
</ul>
</div>
<div class="group_count">
	<?php
		echo $num_groups . " " . elgg_echo("groups:count");
	?>
</div>