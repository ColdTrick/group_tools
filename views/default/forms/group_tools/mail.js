define(['jquery'], function($) {
	$(document).on('click', '#group-tools-mail-clear', function() {
		var $form = $(this).closest('form');
		
		$form.find('div.elgg-user-picker[data-name="user_guids"] > ul.elgg-user-picker-list > li').remove();
	});
	
	$(document).on('change', 'form.elgg-form-group-tools-mail input[type="checkbox"][name="all_members"]', function() {
		if ($(this).is(':checked')) {
			$('#group-tools-mail-individual').closest('.elgg-field').addClass('hidden');
		} else {
			$('#group-tools-mail-individual').closest('.elgg-field').removeClass('hidden');
		}
	});
});
