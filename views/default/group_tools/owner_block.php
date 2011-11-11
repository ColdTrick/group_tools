<?php

	if(($group = page_owner_entity()) && ($group instanceof ElggGroup)){
		if($group->membership == ACCESS_PUBLIC){
			$status = elgg_echo("groups:open");
		} else {
			$status = elgg_echo("groups:closed");
		}
		
		$status = ucfirst($status);
		
		?>
		<script type="text/javascript">
			$('#owner_block_content').append("<div id='group_tools_group_status'><?php echo $status;?></div>");
		</script>
		<?php 
	}