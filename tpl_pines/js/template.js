pines(function(){
if ($.pnotify) {
	$.pnotify.defaults.pnotify_opacity = .9;
	$.pnotify.defaults.pnotify_delay = 15000;
}

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
	$("#content, #left, #right").on("click", ".module_title .module_maximize", function(){
		$(this).closest(".module").toggleClass("module_maximized");
	}).on("hover", ".module_title .module_maximize", function(){
		$(this).toggleClass("ui-state-hover");
	}).on("click", ".module_title .module_minimize", function(){
		$(this).children("span.ui-icon").toggleClass("ui-icon-triangle-1-n").toggleClass("ui-icon-triangle-1-s")
		.end().parent().nextAll(".module_content").slideToggle("normal");
	}).on("hover", ".module_title .module_minimize", function(){
		$(this).toggleClass("ui-state-hover");
	});

	// Menu hover.
	$("ul.dropdown").on("hover", "a:not(.ui-widget-header)", function(){
		$(this).toggleClass("ui-state-hover");
	});
};

pines.tpl_pines_page_ready();
});