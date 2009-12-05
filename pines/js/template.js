var _alert;

function convert_standard_notices() {
	// Turn notices into Pines Notify notices.
	$(".notice.ui-state-error").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Error",
			pnotify_text: $(this).html(),
			pnotify_opacity: .9,
			pnotify_type: "error",
			pnotify_hide: false
		});
	}).end().remove();
	$(".notice.ui-state-highlight").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Notice",
			pnotify_text: $(this).html(),
			pnotify_opacity: .9
		});
	}).end().remove();
}

function consume_alert() {
	if (_alert) return;
	_alert = window.alert;
	window.alert = function(message) {
		$.pnotify({
			pnotify_title: 'Alert',
			pnotify_text: message,
			pnotify_opacity: .9
		});
	};
}

function release_alert() {
	if (!_alert) return;
	window.alert = _alert;
	_alert = null;
}

$(function($){
	convert_standard_notices();
	consume_alert();
	// Just in case Pines Notify isn't working.
	$(".notice .close, .error .close").css("cursor", "pointer").click(function() {
		$(this).parent().fadeOut("slow");
	});
	// Menu mouseover effects.
	$(".mainmenu li").hover(function(){
		$(this).addClass("ui-state-hover");
	}, function(){
		$(this).removeClass("ui-state-hover");
	});

	$(".module .module_right_minimize").hover(function(){
		$(this).addClass("ui-state-hover");
	}, function(){
		$(this).removeClass("ui-state-hover");
	}).toggle(function(){
		$(this).children("span.ui-icon").removeClass("ui-icon-triangle-1-n").addClass("ui-icon-triangle-1-s")
		.end().parent().nextAll(".module_content").slideUp("normal");
	}, function(){
		$(this).children("span.ui-icon").removeClass("ui-icon-triangle-1-s").addClass("ui-icon-triangle-1-n")
		.end().parent().nextAll(".module_content").slideDown("normal");
	});
});