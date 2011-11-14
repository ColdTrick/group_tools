<?php

	if(($group = page_owner_entity()) && ($group instanceof ElggGroup)){
		if($group->membership == ACCESS_PUBLIC){
			$status = elgg_echo("groups:open");
			$class = "group_status_open";
		} else {
			$status = elgg_echo("groups:closed");
			$class = "group_status_closed";
		}
		
		$status = ucfirst($status);
		
		?>
		<script type="text/javascript">
			$('#owner_block_content').append("<div id='group_tools_group_status' class='<?php echo $class; ?>'><?php echo $status;?></div>");
		</script>
		<?php 
	}