pines(function(){
	// Give hover effects to elements.
	var hover = function(elements){
		(typeof elements == "string" ? $(elements) : elements).live("mouseenter", function(){
			$(this).addClass("ui-state-hover");
		}).live("mouseleave", function(){
			$(this).removeClass("ui-state-hover");
		});
	};
	$.pnotify.defaults.pnotify_opacity = .9;
	// Maximize modules.
	hover($(".module .module_maximize").live("click", function(){
		$(this).closest(".module").toggleClass("module_maximized");
	}));
	// Shade modules.
	hover($(".module .module_minimize").live("click", function(){
		$(this).children("span.ui-icon").toggleClass("ui-icon-triangle-1-n").toggleClass("ui-icon-triangle-1-s")
		.end().parent().nextAll(".module_content").slideToggle("normal");
	}));
	// Menu hover.
	hover("ul.dropdown li a:not(.ui-widget-header)");

	// Main menu close delay.
	var cur_kept_open = [];
	var cur_timer = null;
	// TODO: Option to enable slide down.
	$("#main_menu").delegate("li", "mouseenter", function(){
		//var cur_item = $(this);
		//var cur_submenu = cur_item.children("ul");
		if (cur_timer) {
			window.clearTimeout(cur_timer);
			cur_timer = null;
			if (cur_kept_open.length)
				cur_kept_open.removeClass("hover");
			cur_kept_open = [];
		}
		/*if (cur_submenu.length)
			cur_submenu.hide().slideDown(200);*/
	}).delegate("li", "mouseleave", function(){
		var cur_item = $(this);
		//var cur_submenu = cur_item.children("ul");
		if (cur_kept_open.length)
			cur_kept_open.removeClass("hover");
		cur_kept_open = cur_item.parentsUntil("ul.dropdown").andSelf().filter("li").addClass("hover");
		if (cur_timer)
			window.clearTimeout(cur_timer);
		cur_timer = window.setTimeout(function(){
			if (cur_kept_open.length)
				cur_kept_open.removeClass("hover");
			cur_kept_open = [];
			cur_timer = null;
		}, 300);
		/*cur_submenu.slideUp(100, function(){
			cur_item.removeClass("hover");
		});*/
	});

	// Get the loaded page ready. (Styling, etc.)
	// This needs to be called after Ajax page loads.
	pines.tpl_pines_page_ready = function(){
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