<?php 
	
	$name = $vars["internalname"]; // input name of the selected user
	$id = $vars["internalid"];
	$relationship = $vars["relationship"];
	
	$destination = $id . "_autocomplete_results";
	
	
	$multiple = $vars["multiple"]; //should be string true or false
	if(empty($multiple)){
		$multiple = "true";
	}	

	$max = $vars["max"]; 
	if(empty($max)){
		$max = 50;
	}	

	$minChars = $vars["minChars"]; 
	if(empty($minChars)){
		$minChars = 3;
	}	
	
	global $js_autocomplete;
	if(empty($js_autocomplete)){
		$js_autocomplete = true;
		
		echo "<script type='text/javascript' src='" . $vars["url"] . "vendors/jquery/jquery.autocomplete.min.js'></script>";
	}
?>
	
	<input type="text" id="<?php echo $id; ?>_autocomplete" size="25" />
	
	<div id="<?php echo $destination; ?>"></div>
	
	<input type="hidden" name="user_guids" id="<?php echo $id; ?>_autocomplete_user_guids" />
	
	<div class="clearfloat"></div>
	
	<script type="text/javascript">
	
		$(document).ready(function() {
			$.ajaxSetup( { type: "POST" } );
			$("#<?php echo $id; ?>_autocomplete").autocomplete('<?php echo $vars["url"]; ?>pg/groups/group_invite_autocomplete', {
				width: 300,
				minChars: <?php echo $minChars; ?>,
				cacheLength: 1,
				max: <?php echo $max; ?>,
				multiple: <?php echo $multiple; ?>,
				matchContains: true,
				formatItem: group_tools_autocomplete_format_item,
				extraParams: {
					'user_guids': function() {
						var result = "";
						
						$("#<?php echo $destination; ?> input[name='<?php echo $name; ?>[]']").each(function(index, elem){
							if(result == ""){
								result = $(this).val();
							} else {
								result += "," + $(this).val();
							}
						});

						return result;
					},
					'group_guid' : <?php echo $vars["group_guid"]; ?>
					<?php 
					if(!empty($relationship)){ 
						echo ", 'relationship' : '" . $relationship . "'";
					}
					?>
				}
			}).result(function(event, data, formatted) {
				if(data){
					group_tools_autocomplete_format_result(data, '<?php echo $id; ?>', '<?php echo $name; ?>');
			 		$(this).val("");
				}
			}).bind("keydown", function(event){
				  // enter should not submit 
				  if((event.keyCode || event.which || event.charCode || 0) == 13){
					  event.preventDefault(); //stop event
				  }

				  return true;
			 }).focus(function(){
				 $(this).val("");
			 }).blur(function(event){
				 $(this).val("");
			 });
			
			 $('#<?php echo $destination; ?> .group_invite_autocomplete_remove').live("click", function(){
				$(this).parent('div').remove();
			 });	
		});
	</script>