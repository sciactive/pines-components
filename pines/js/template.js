var _alert;

function convert_standard_notices() {
	// Turn notices into Pines Notify notices.
	$(".notice.ui-state-error").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Error",
			pnotify_text: $(this).html(),
			pnotify_type: "error",
			pnotify_hide: false
		});
	}).end().remove();
	$(".notice.ui-state-highlight").find("p.entry span.text").each(function(){
		$.pnotify({
			pnotify_title: "Notice",
			pnotify_text: $(this).html()
		});
	}).end().remove();
}

function consume_alert() {
	if (_alert) return;
	_alert = window.alert;
	window.alert = function(message) {
		$.pnotify({
			pnotify_title: 'Alert',
			pnotify_text: message
		});
	};
}

function release_alert() {
	if (!_alert) return;
	window.alert = _alert;
	_alert = null;
}

$(function($){
	// Just in case Pines Notify isn't working.
	$(".notice .close, .error .close").css("cursor", "pointer").click(function() {
		$(this).parent().fadeOut("slow");
	});
	convert_standard_notices();
	consume_alert();

	// Position the footer correctly.
	var wrapper = $("#wrapper");
	var footer = wrapper.children("#footer");
	var height = footer.height();
	var colmask = wrapper.children(".colmask");
	wrapper.css("min-height", "100%");
	footer.css("position", "absolute");
	footer.css("bottom", "0");
	colmask.css("margin-bottom", height);
});