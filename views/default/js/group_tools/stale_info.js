define(function(require) {
	
	var $ = require('jquery');
	var Ajax = require('elgg/Ajax');
	
	var mark_not_stale = function(event) {
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
	};
	
	$(document).on('click', '#group-tools-stale-touch-link', mark_not_stale);
});
