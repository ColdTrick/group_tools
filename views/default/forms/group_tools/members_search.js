define(['jquery', 'elgg/Ajax'], function($, Ajax) {
	
	var submitForm = function(event) {
		event.preventDefault();
		
		var $form = $(this);
		
		var ajax = new Ajax();
		ajax.path($form.prop('action'), {
			data: ajax.objectify($form),
			success: function(data) {
				$form.siblings().not($form).remove();
				$form.after(data);
			},
		});
	};
	
	$(document).on('submit.memberSearch', '.elgg-form-group-tools-members-search', submitForm);
});
