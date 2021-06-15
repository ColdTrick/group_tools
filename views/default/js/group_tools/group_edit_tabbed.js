define(function (require) {

	var $ = require('jquery');

	$(document).on('click', '.elgg-menu-filter li', function (e) {

		e.preventDefault();
		
		// remove selected class
		$(this).siblings().removeClass('elgg-state-selected');
		$(this).addClass('elgg-state-selected');

		// hide everything
		var $content = $('.elgg-layout-content');
//		$content.find('> form, > div:not(.elgg-message)').hide();
		$('.group-tools-group-edit-section').hide();
		
		var link = $(this).children('a').attr('href');
		console.log(link);
		switch (link) {
			case '#group-tools-group-edit-profile':
				$content.find('> form').show();
				$('#group-tools-group-edit-profile').show();

				break;
			case '#group-tools-group-edit-reason':
				console.log(link);
				$content.find('> form').show();
				$('#group-tools-group-edit-reason').show();
				
				break;
			case '#group-tools-group-edit-access':
				$content.find('> form').show();
				$('#group-tools-group-edit-access').show();

				break;
			case '#group-tools-group-edit-tools':
				$content.find('> form').show();
				$('#group-tools-group-edit-tools').show();

				break;
			default:
				$content.find('> div').show();
				break;
		}

	});
});
