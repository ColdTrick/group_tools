import 'jquery';

function ToolsPreset(wrapper) {
	var self = this;
	
	$('#group-tools-preset-selector a', wrapper).on('click', function() {
		self.changePreset(this);
		
		return false;
	});
	
	self.autoDetect(wrapper);
}

ToolsPreset.prototype = {
	changePreset: function(elem) {
		var rel = $(elem).attr('rel');
		
		$('#group-tools-preset-descriptions div').hide();
		$('#group-tools-preset-description-' + rel).show();
		
		this.resetTools();
		this.presetTools(rel);
		
		$('#group-tools-preset-more').hide();
		$('#group-tools-preset-active').show();
		
	},
	resetTools: function() {
		$('#group-tools-preset-active .elgg-field').each(function(index, elm) {
			$(elm).appendTo('#group-tools-preset-more div.elgg-body');
		});
		
		$('#group-tools-preset-more .elgg-input-checkbox[value="yes"]:checked').click();
		$('#group-tools-preset-more-link').show();
	},
	presetTools: function(preset_id) {
		if (preset_id === 'blank') {
			$('#group-tools-preset-more .elgg-body > .elgg-field').each(function() {
				$(this).prependTo('#group-tools-preset-active div.elgg-body');
			});
		} else {
			$('#group-tools-preset-more .elgg-field.group-tools-preset-' + preset_id).each(function() {
				$(this).prependTo('#group-tools-preset-active div.elgg-body');
			});
			
			$('#group-tools-preset-active .elgg-input-checkbox[value="yes"]').not(':checked').click();
		}
		
		$('#group-tools-preset').val($('#group-tools-preset-description-' + preset_id + ' input[name="group_tools_preset"]').val());
		
		if ($('#group-tools-preset-more .elgg-body > .elgg-field').length === 0) {
			$('#group-tools-preset-more-link').hide();
		}
	},
	autoDetect: function(wrapper) {
		var $input = $(wrapper).find('input[name="group_tools_auto_select"]');
		if (!$input.length) {
			return;
		}
		
		var $preset = $('#group-tools-preset-selector .elgg-anchor-label').filter(function(index, elem) {
			return $input.val() === $(elem).text();
		});
		if (!$preset.length) {
			return;
		}
		
		$preset.closest('a').click();
	}
};

ToolsPreset.init = function(selector) {
	new ToolsPreset(selector);
};

ToolsPreset.init('.elgg-form-groups-edit');
