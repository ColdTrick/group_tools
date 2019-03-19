define(function (require) {

	var $ = require('jquery');
	
	var update_tools = function (preset_id) {
		
		$tool_section = $('#group-tools-group-edit-tools');
		
		$tool_section.find('.elgg-input-checkbox[value="yes"]:checked').click();
		$tool_section.find('.group-tools-preset-' + preset_id + ' .elgg-input-checkbox[value="yes"]').not(':checked').click();
	};

	$('#group-tools-group-edit-tools .group-tools-simplified-option').on('click', function() {
		$(this).siblings().removeClass('elgg-state-active');
		
		$(this).addClass('elgg-state-active');
		
		update_tools($(this).data('preset'));
	});
	
	$('#group-tools-group-edit-tools .group-tools-simplified-option:first').click();
});
