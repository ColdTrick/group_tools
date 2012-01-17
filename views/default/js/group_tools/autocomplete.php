<?php 

?>
function group_tools_autocomplete_format_item(row, pos, items, search) {
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

	result += "<span class='elgg-icon elgg-icon-delete-alt'></span>";
	result += "</div>";

	$('#' + elem_id + '_autocomplete_results').append(result);
}