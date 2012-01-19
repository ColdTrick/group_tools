<?php 

?>

function group_tools_autocomplete_format_item(row) {
	var result = "";

	if(row[0] == "user"){
		if(row[3]){
			result += "<img src='" + row[3] + "' />&nbsp;";
		}
		result += row[2];
	}
	if(row[0] == "email"){
		result += row[1];
	}

	return result;
}

function group_tools_autocomplete_format_result(row, elem_id, input_name) {
	var result = "";
	result += "<div class='" + elem_id + "_result'>";

	if(row[0] == "user"){
		result += "<input type='hidden' value='" + row[1] + "' name='" + input_name + "[]' />";

		if(row[3]){
			result += "<img src='" + row[3] + "' />&nbsp;";
		}
		
		result += row[2].replace(/(<.+?>)/gi, '');
	}

	if(row[0] == "email"){
		result += "<input type='hidden' value='" + row[1] + "' name='" + input_name + "_email[]' />";
		result += row[1].replace(/(<.+?>)/gi, '');
	}

	result += "<div class='group_invite_autocomplete_remove'></div>";
	result += "</div>";

	$('#' + elem_id + '_autocomplete_results').append(result);
}

function group_tools_cleanup_highlight(section){
	switch(section){
		case "owner_block":
			if($('#owner_block_desc').length){
				$sel = $('#owner_block_desc');
			} else {
				$sel = $('#owner_block_content');
			}
			
			$next = $sel.nextAll('div');
			index = $next.index('#owner_block_submenu');
			$next.slice(0, index).addClass('group_tools_highlight');
			
			break;
		case "actions":
			$('#owner_block_submenu .submenu_group_1groupsactions').parent().addClass('group_tools_highlight');
			break;
		case "menu":
			$('#owner_block_submenu>div:gt(0)').addClass('group_tools_highlight');
			break;
		case "members":
			$('#two_column_left_sidebar').append('<div id="group_tools_members_example" class="group_tools_highlight">' + group_tools_members_example_text + '</div>');
			break;
		case "featured":
			$('#two_column_left_sidebar').append('<div id="group_tools_featured_example" class="group_tools_highlight">' + group_tools_featured_example_text + '</div>');
			break;
	}
}

function group_tools_cleanup_unhighlight(section){
	switch(section){
		case "owner_block":
			if($('#owner_block_desc').length){
				$sel = $('#owner_block_desc');
			} else {
				$sel = $('#owner_block_content');
			}
			
			$next = $sel.nextAll('div');
			index = $next.index('#owner_block_submenu');
			$next.slice(0, index).removeClass('group_tools_highlight');
			
			break;
		case "actions":
			$('#owner_block_submenu .submenu_group_1groupsactions').parent().removeClass('group_tools_highlight');
			break;
		case "menu":
			$('#owner_block_submenu>div:gt(0)').removeClass('group_tools_highlight');
			break;
		case "members":
			$('#group_tools_members_example').remove();
			break;
		case "featured":
			$('#group_tools_featured_example').remove();
			break;
	}
}