<?php

	if(!empty($vars["entity"])){
?>
<div class="contentWrapper" id="group_tools_group_edit_tab">
	<div id="elgg_horizontal_tabbed_nav">
		<ul>
			<li class="selected"><a href="#" onclick="group_tools_edit_group('profile', this);"><?php echo elgg_echo("group_tools:group:edit:profile"); ?></a></li>
			<li><a href="#" onclick="group_tools_edit_group('other', this);"><?php echo elgg_echo("group_tools:group:edit:other"); ?></a></li>
		</ul>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		group_tools_edit_group('profile', $('#group_tools_group_edit_tab li.selected a'));
	});

	function group_tools_edit_group(section, elem){

		$('#group_tools_group_edit_tab li').removeClass("selected");
		$(elem).parent("li").addClass("selected");
		
		$('#two_column_left_sidebar_maincontent div.contentWrapper').hide();
		$('#group_tools_group_edit_tab').show();
		
		if(section == "other"){
			$('#two_column_left_sidebar_maincontent div.contentWrapper').show();
			$('#two_column_left_sidebar_maincontent form[action="<?php echo $vars["url"]; ?>action/groups/edit"]').parent('div.contentWrapper').hide();
		} else {
			$('#two_column_left_sidebar_maincontent form[action="<?php echo $vars["url"]; ?>action/groups/edit"]').parent('div.contentWrapper').show();
		}
	}
</script>
<?php } ?>