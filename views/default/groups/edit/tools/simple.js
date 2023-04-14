define(['jquery'], function ($) {
	function update_tools(preset_id) {
		$tool_section = $('.group-tools-edit-tools-simple').parent();
		$tool_section.find('.elgg-input-checkbox[value="yes"]:checked').click();
		
		$preset_section = $tool_section.find('.group-tools-preset-' + preset_id);
		$preset_section.find('.elgg-input-checkbox[value="yes"]').not(':checked').click();
	};

	$('.group-tools-edit-tools-simple .group-tools-simplified-option').on('click', function() {
		$(this).siblings().removeClass('elgg-state-active');
		
		$(this).addClass('elgg-state-active');
		
		$('.elgg-form-groups-edit input[name="group_tools_preset"]').prop('disabled', true);
		$(this).find('input[name="group_tools_preset"]').prop('disabled', false);
		
		update_tools($(this).data('preset'));
	});
	
	$('.group-tools-edit-tools-simple .group-tools-simplified-option:first').click();
});
