define(['jquery'], function ($) {
	
	// tab filter
	$(document).on('click', '.group-tools-invite-tab', function (e) {
		e.preventDefault();
		
		var $elem = $(this);
		var target = $elem.find('a').eq(0).attr('href');
		
		$elem.siblings('.group-tools-invite-tab').addBack().removeClass('elgg-state-selected');
		$elem.addClass('elgg-state-selected');
		
		$(target).siblings('.group-tools-invite-form').addBack().removeClass('elgg-state-active').addClass('hidden');
		$(target).addClass('elgg-state-active').removeClass('hidden');
	});
});
