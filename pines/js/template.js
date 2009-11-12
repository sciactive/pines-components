jQuery(document).ready(function($){
	$(".notice, .error").oneTime(10000, "hide_notices", function() {
		$(this).fadeOut("slow");
	});
	$(".notice .close, .error .close").css("cursor", "pointer").live("click", function() {
		$(this).parent().stopTime("hide_notices").fadeOut("slow");
	});
});