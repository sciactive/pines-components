pines(function(){
	var modules = $("div.module");
	// Add disabled element styling.
	$(".ui-widget-content:input:disabled", modules).addClass("ui-state-disabled");
	// UI buttons.
	$(".ui-state-default:input:not(:not(:button, :submit, :reset))", modules).button();
});