jQuery(document).ready(function($){
	$(".notice, .error").oneTime(5000, "hide_notices", function() {
		$(this).fadeOut(400);
	});
	$(".notice .close, .error .close").css("cursor", "pointer").live("click", function() {
		$(this).parent().stopTime("hide_notices").fadeOut(400);
	});
	// $(".mainpage").css("opacity",0.95);
	$(".stylized input:button, .stylized input:submit, .stylized input:reset").css("width","auto");
});