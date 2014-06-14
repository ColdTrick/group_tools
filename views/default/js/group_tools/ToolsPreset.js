/**
 * Support file for group tool presets
 */

define(["jquery", "elgg"], function($, elgg){
	
	function ToolsPreset(wrapper) {
		this.$wrapper = $(wrapper);
		this.$links = $("#group-tools-preset-selector a", wrapper);
		
		this.$links.on("click", function() {
			self.changePreset(this);
		});
		
	};
	
	ToolsPreset.prototype = {
		changePreset : function(elem) {
			this.resetTools();
			
			$.each(GROUP_TOOLS_PRESETS, function (index, data) {
				if (data.title == $(elem).innerHTML) {
					$("#group-tools-preset-description").html(data.description);
					
					
					$.each(data.tools)
					
					return false;
				}
			});
			
			return false;
		},
		resetTools : function() {
			$.each("#group-tools-preset-active ul.elgg-input-radios", function(index, elm) {
				var $parent = $(elm).parent();
				
				$parent.appendTo("#group-tools-preset-more div.elgg-body");
			});
			
			$radios = $("#group-tools-preset-more :input[value='no']").not(":checked");
			console.log($radios);
			
			$.each($radios, function(index, elm) {
				console.log(elm);
				//$(elm).click();
			});
		}
	};
	
	ToolsPreset.init = function(selector) {
		
		elgg.register_hook_handler("init", "system", function () {
			$(selector).each(function () {
				// we only want to wrap once
				if (!$(this).data("initialized")) {
					new ToolsPreset(this);
					$(this).data("initialized", 1);
				}
			});
		});
	};
	
	return ToolsPreset;
});