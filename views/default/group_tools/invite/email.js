define(['jquery', 'elgg/system_messages'], function ($, system_messages) {

	$(document).on('keydown', '#group-tools-invite-email .elgg-input-email', function (event) {
		if (event.keyCode === 10 || event.keyCode === 13) {
			event.preventDefault();
			event.stopPropagation();
			$('#group-tools-invite-email .elgg-button-submit').click();
		}		
	});
	
	$(document).on('click', '#group-tools-invite-email .elgg-list-email .elgg-icon-delete', function (event) {
		$(this).closest('li').remove();
	});

	$(document).on('click', '#group-tools-invite-email .elgg-button-submit', function (event) {
		event.preventDefault();
		event.stopPropagation();

		var $form = $('#group-tools-invite-email');
		var $email_input = $form.find('.elgg-input-email');
		
		if (!$email_input[0].checkValidity()) {
			system_messages.error($email_input[0].validationMessage);
			
			return;
		}
		
		var mail = $email_input.val();
		if (!mail) {
			return;
		}
		
		var $list = $form.find('.elgg-list-email');
		
		$list.append('<li><input type="hidden" name="user_guid_email[]" value="' + mail + '"></input><span class="float-alt mlm fas fa-times link elgg-icon-delete elgg-icon"></span>' + mail + '</li>');
		
		$email_input.val('');
	});
});
