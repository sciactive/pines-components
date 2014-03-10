pines.pnotify_alert_defaults = {nonblock: true};
pines.pnotify_notice_defaults = {nonblock: true};
pines.pnotify_error_defaults = {type: "error", hide: false, nonblock: false};
pines.load(function(){
	if (!window._alert) {
		window._alert = window.alert;
		window.alert = function(message){
			var options = $.extend({title: "Alert", text: pines.safe(message)}, pines.pnotify_alert_defaults);
			return $.pnotify(options);
		};
		pines.notice = function(message, title){
			var options = $.extend({title: title ? title : "Notice", text: String(message)}, pines.pnotify_notice_defaults);
			return $.pnotify(options);
		};
		pines.error = function(message, title){
			var options = $.extend({title: title ? title : "Error", text: String(message)}, pines.pnotify_error_defaults);
			return $.pnotify(options);
		};
	}
});