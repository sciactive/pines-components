function convert_standard_notices() {
	// Turn notices into Pines Notify notices.
	$(".notice").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Notice",
			pnotify_text: $(this).html()
		});
	}).end().remove();
	$(".error").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Error",
			pnotify_text: $(this).html(),
			pnotify_type: "error"
		});
	}).end().remove();
}

jQuery(document).ready(function($){
	// Just in case Pines Notify isn't working.
	$(".notice .close, .error .close").css("cursor", "pointer").click(function() {
		$(this).parent().fadeOut("slow");
	});
	convert_standard_notices();
});