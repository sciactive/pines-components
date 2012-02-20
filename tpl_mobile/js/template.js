pines(function(){
	if ($.pnotify) {
		$.pnotify.defaults.pnotify_opacity = 1;
		$.pnotify.defaults.pnotify_nonblock = false;
	}
	if ($.fn.pgrid)
		$.fn.pgrid.defaults.pgrid_stateful_height = false;

	// Menu link.
	$("#menu_link, #menu_back").click(function(){
		$("#menu, #page").toggle();
	});
	// Menus.
	$(".menu").delegate("a.expander", "click", function(){
		$(this).toggleClass("btn-info btn-success").children().toggleClass("icon-chevron-down icon-chevron-up").end().siblings("ul").toggle();
	}).delegate("a:not(.expander)[href=javascript:void(0);]", "click", function(){
		$(this).siblings("a.expander").click();
	});
});