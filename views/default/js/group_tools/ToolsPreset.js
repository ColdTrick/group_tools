/**
 * Support file for group tool presets
 */

define(["jquery", "elgg"], function($, elgg) {
	
	function ToolsPreset(wrapper) {
		var self = this;
		
		$("#group-tools-preset-selector a", wrapper).on("click", function() {
			self.changePreset(this);
			
			return false;
		});
		
	}
	
	ToolsPreset.prototype = {
		changePreset : function(elem) {
			
			var rel = $(elem).attr("rel");
			
			$("#group-tools-preset-descriptions div").hide();
			$("#group-tools-preset-description-" + rel).show();
			
			this.resetTools();
			this.presetTools(rel);
			
			$("#group-tools-preset-more").hide();
			$("#group-tools-preset-active").show();
			
		},
		resetTools : function() {
			$("#group-tools-preset-active ul.elgg-input-radios").each(function(index, elm) {
				var $tool_parent = $(elm).parent();
				
				$tool_parent.appendTo("#group-tools-preset-more div.elgg-body");
			});
			
			$("#group-tools-preset-more .elgg-input-radio[value='no']").not(":checked").click();
		},
		presetTools : function(preset_id) {
			$("#group-tools-preset-more .group-tools-preset-" + preset_id).each(function(index, elm) {
				
				$(this).prependTo("#group-tools-preset-active div.elgg-body");
			});
			
			$("#group-tools-preset-active .elgg-input-radio[value='yes']").not(":checked").click();
		}
	};
	
	ToolsPreset.init = function(selector) {
		new ToolsPreset(selector);
	};
	
	return ToolsPreset;
});