define(['jquery', 'elgg/Ajax'], function($, Ajax) {

	$(document).on('click', '#group-tools-stale-touch-link', function(event) {
		event.preventDefault();
		
		if ((typeof event.result !== 'undefined') && (event.result === false)) {
			return false;
		}
		
		var $elem = $(this);
		var ajax = new Ajax();
		ajax.action($elem.attr('href'), {
			success: function() {
				$elem.closest('.elgg-message').remove();
			}
		});
		
		return false;
	});
});
