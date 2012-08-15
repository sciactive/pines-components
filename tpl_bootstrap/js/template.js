pines(function(){
if ($.pnotify) {
	$.pnotify.defaults.opacity = .9;
	$.pnotify.defaults.delay = 15000;
}

// Get the loaded page ready. (Styling, etc.)
// This needs to be called after Ajax page loads.
pines.tpl_bootstrap_page_ready = function(){
	if (pines.tpl_bootstrap_menu_delay) {
		// Menu close delay.
		$("li", "ul.dropdown").bind("mouseenter", function(){
			$(this).siblings().removeClass("hover");
		}).bind("mouseleave", function(){
			$(this).addClass("hover").removeClass("hover", 300);
		});
	}
	// Maximize & shade modules.
	$("#content, #left, #right").on("click", ".module_title .module_maximize", function(){
		$(this).children("span").toggleClass("icon-resize-full icon-resize-small")
		.end().closest(".module").toggleClass("module_maximized");
	}).on("click", ".module_title .module_minimize", function(){
		$(this).children("span").toggleClass("icon-chevron-up icon-chevron-down")
		.end().parent().nextAll(".module_content").slideToggle("normal");
	});
};

pines.tpl_bootstrap_page_ready();
});