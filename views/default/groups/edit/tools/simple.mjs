import 'jquery';

function update_tools(preset_id) {
	var $tool_section = $('.group-tools-edit-tools-simple').parent();
	$tool_section.find('.elgg-input-checkbox[value="yes"]:checked').click();
	
	var $preset_section = $tool_section.find('.group-tools-preset-' + preset_id);
	$preset_section.find('.elgg-input-checkbox[value="yes"]').not(':checked').click();
}

$('.group-tools-edit-tools-simple .group-tools-simplified-option').on('click', function() {
	$(this).siblings().removeClass('elgg-state-active');
	
	$(this).addClass('elgg-state-active');
	
	$('.elgg-form-groups-edit input[name="group_tools_preset"]').prop('disabled', true);
	$(this).find('input[name="group_tools_preset"]').prop('disabled', false);
	
	update_tools($(this).data('preset'));
});

var preset = $('.group-tools-simplified-options input[name="preselected_group_tools_preset"]').val();
$('.group-tools-edit-tools-simple .group-tools-simplified-option[data-preset="' + preset + '"]').click();
