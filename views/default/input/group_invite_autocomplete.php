<?php 
	
	$name = elgg_extract("name", $vars); // input name of the selected user
	$id = elgg_extract("id", $vars);
	$relationship = elgg_extract("relationship", $vars);
	
	$destination = $id . "_autocomplete_results";
	
	$multiple = elgg_extract("multiple", $vars, "true"); //should be string true or false
	$max = elgg_extract("max", $vars, 50); 
	$minChars = elgg_extract("minChars", $vars, 3);
	
	elgg_load_js('jquery.autocomplete.min');
	elgg_load_js("group_tools.autocomplete");
	elgg_load_css("group_tools.autocomplete");
	
?>
	
	<input type="text" id="<?php echo $id; ?>_autocomplete" class="elgg-input elgg-input-autocomplete" />
	
	<div id="<?php echo $destination; ?>"></div>
	
	<input type="hidden" name="user_guids" id="<?php echo $id; ?>_autocomplete_user_guids" />
	
	<div class="clearfloat"></div>
	
	<script type="text/javascript">
	
		$(document).ready(function() {
			$.ajaxSetup( { type: "POST" } );
			
			$("#<?php echo $id; ?>_autocomplete").autocomplete('<?php echo $vars["url"]; ?>groups/group_invite_autocomplete', {
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
			
			 $('#<?php echo $destination; ?> .elgg-icon-delete-alt').live("click", function(){
				$(this).parent('div').remove();
			 });	
		});
	</script>