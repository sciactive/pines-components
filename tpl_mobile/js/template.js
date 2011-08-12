pines(function(){
	$.pnotify.defaults.pnotify_opacity = 1;
	$.pnotify.defaults.pnotify_nonblock = false;
	$.fn.pgrid.defaults.pgrid_stateful_height = false;

	// Menu link.
	$("#menu_link, #menu_back").button().click(function(){
		$("#menu, #page").toggle();
	});
	// Menus.
	$(".menu li a").button().filter(".expander").toggleClass("ui-corner-all ui-corner-right").next().toggleClass("ui-corner-all ui-corner-left");
	$(".menu").delegate("a.expander", "click", function(){
		$(this).toggleClass("ui-state-highlight").siblings("ul").toggle();
	}).delegate("a:not(.expander)[href=javascript:void(0);]", "click", function(){
		$(this).siblings("a.expander").click();
	});

	var modules = $("div.module");
	// Add disabled element styling.
	$(".ui-widget-content:input:disabled", modules).addClass("ui-state-disabled");
	// UI buttons.
	$(".ui-state-default:input:not(:not(:button, :submit, :reset))", modules).button();
});