define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');


	// tab filter
	$(document).on('click', '.group-tools-invite-tab', function (e) {
		e.preventDefault();

		var $elem = $(this);
		var target = $elem.find('a').eq(0).attr('href');

		$elem.siblings('.group-tools-invite-tab').andSelf().removeClass('elgg-state-selected');
		$elem.addClass('elgg-state-selected');

		$(target).siblings('.group-tools-invite-form').andSelf().removeClass('elgg-state-active').addClass('hidden');
		$(target).addClass('elgg-state-active').removeClass('hidden');
	});

	// toggle all friends
	$(document).on('change', '#group-tools-friends-toggle', function (e) {
		if ($(this).prop('checked') === true) {
			$('#group-tools-invite-friends-friendspicker').find('input[name="friends[]"]').prop('checked', true);
		} else {
			$('#group-tools-invite-friends-friendspicker').find('input[name="friends[]"]').prop('checked', false);
		}
	});

	// admin - invite all site members
	$(document).on('change', '#group-tools-invite-users-all', function (e) {
		if ($(this).prop('checked') === true) {
			$('#group-tools-invite-users-pick').addClass('hidden');
		} else {
			$('#group-tools-invite-users-pick').removeClass('hidden');
		}
	});

});