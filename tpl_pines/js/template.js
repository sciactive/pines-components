pines(function(){
	$.pnotify.defaults.pnotify_opacity = .9;

	// Get the loaded page ready. (Styling, etc.)
	// This needs to be called after Ajax page loads.
	pines.tpl_pines_page_ready = function(){
		if (pines.tpl_pines_menu_delay) {
			// Menu close delay.
			$("li", "ul.dropdown").bind("mouseenter", function(){
				$(this).siblings().removeClass("hover");
			}).bind("mouseleave", function(){
				$(this).addClass("hover").removeClass("hover", 300);
			});
		}
		// Maximize & shade modules.
		$("div.module_title", "#content, #right").delegate("div.module_maximize", "click", function(){
			$(this).closest(".module").toggleClass("module_maximized");
		}).delegate("div.module_maximize", "hover", function(){
			$(this).toggleClass("ui-state-hover");
		}).delegate("div.module_minimize", "click", function(){
			$(this).children("span.ui-icon").toggleClass("ui-icon-triangle-1-n").toggleClass("ui-icon-triangle-1-s")
			.end().parent().nextAll(".module_content").slideToggle("normal");
		}).delegate("div.module_minimize", "hover", function(){
			$(this).toggleClass("ui-state-hover");
		});

		// Menu hover.
		$("ul.dropdown").delegate("a:not(.ui-widget-header)", "hover", function(){
			$(this).toggleClass("ui-state-hover");
		});

		// Main menu corners.
		$("#main_menu > ul.dropdown")
		.find("> li:first-child > a.ui-state-default").addClass("ui-corner-tl").end()
		.find("> li:last-child > a.ui-state-default").addClass("ui-corner-tr").end()
		.find("ul > li:first-child > a").addClass("ui-corner-tr").end()
		.find("ul > li:last-child > a").addClass("ui-corner-bottom");

		// Add disabled element styling.
		$(".module .ui-widget-content:input:disabled").addClass("ui-state-disabled");
		$(".module .ui-widget-content:input:not(:button, :submit, :reset), .module .ui-widget-content:file").addClass("ui-corner-all");

		// UI buttons.
		$(".module .ui-state-default:input:not(:not(:button, :submit, :reset))").button();
	};
	
	pines.tpl_pines_page_ready();
});