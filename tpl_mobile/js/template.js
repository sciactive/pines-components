pines(function(){
	if ($.pnotify) {
		$.pnotify.defaults.opacity = 1;
		$.pnotify.defaults.nonblock = false;
		$.pnotify.defaults.closer_hover = false;
		$.pnotify.defaults.sticker_hover = false;
		$.pnotify.defaults.history = false;
	}
	if ($.fn.pgrid)
		$.fn.pgrid.defaults.pgrid_stateful_height = false;

	// Menu link.
	var wrapper = $("#wrapper"),
		menu = $("#menu"),
		page = $("#page");
	$("#menu_link").click(function(){
		if (wrapper.hasClass("menu_open")) {
			menu.animate({
				right: "100%",
				left: "-85%"
			}, 250, function(){
				menu.css("min-height", "100%");
			});
			page.animate({
				left: "0"
			}, 250);
		} else {
			menu.css("min-height", ($("body").height() - 50)+"px").animate({
				right: "15%",
				left: "0"
			}, 250);
			page.animate({
				left: "85%"
			}, 250);
		}
		wrapper.toggleClass("menu_open");
	});
	// Close the menu if the page is clicked while the menu is open.
	$("body").on("click", ".menu_open #page", function(){
		$("#menu_link").click();
	});
	// Menus.
	$(".menu").delegate("a.expander", "click", function(){
		$(this).toggleClass("btn-success").children().toggleClass("icon-chevron-down icon-chevron-up").end().closest("li").children("ul").toggle();
	}).delegate("a:not(.expander)[href=javascript:void(0);]", "click", function(){
		$(this).siblings("a.expander").click();
	});
});