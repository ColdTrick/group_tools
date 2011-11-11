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
			$('#group_stats').append("<p><?php echo $status;?></p>");
		</script>
		<?php 
	}
