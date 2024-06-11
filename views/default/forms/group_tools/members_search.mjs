import 'jquery';
import Ajax from 'elgg/Ajax';

$(document).on('submit.memberSearch', '.elgg-form-group-tools-members-search', function (event) {
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
});
