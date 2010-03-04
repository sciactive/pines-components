$(function(){
	var alert_box = $("<div />", {
		"class": "com_customer_timer_alert",
		"title": "Customer Timer Warning"
	}).dialog({
		autoOpen: false,
		dialogClass: "com_customer_timer_dialog",
		modal: true,
		draggable: false,
		resizable: false,
		buttons: {
			"Go to Status Page": function(){
				window.location = pines.com_customer_timer.status_page_url;
			},
			"Hide for 5 Minutes": function(){
				var date = new Date();
				date.setTime(date.getTime()+(5*60*1000));
				document.cookie = "com_customer_timer_stopped=yes; expires="+date.toUTCString()+"; path=/";
				alert_box.dialog("close");
			}
		}
	});
	setInterval(function(){
		var ca = document.cookie.split(';');
		for(var i=0; i < ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf("com_customer_timer_stopped=") == 0 && c.substring("com_customer_timer_stopped=".length,c.length) == "yes")
				return;
		}
		$.ajax({
			url: pines.com_customer_timer.status_url,
			type: "GET",
			dataType: "json",
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retrieve the status of the customers logged in to the timer:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (!data || !data[0]) {
					if (alert_box.dialog("isOpen"))
						alert_box.dialog("close")
					return;
				}
				var criticals = [];
				var warnings = [];
				$.each(data, function(){
					if (this.points_remain <= pines.com_customer_timer.level_critical)
						criticals = $.merge(criticals, [this]);
					else if (this.points_remain <= pines.com_customer_timer.level_warning)
						warnings = $.merge(warnings, [this]);
				});
				if (!criticals.length && !warnings.length)
					return;
				pines.loadcss(pines.rela_location+"components/com_customer_timer/includes/status_check.css", false);
				var new_dialog_contents = $("<div />");
				if (criticals.length) {
					var new_criticals = $("<div />", {
						"class": "customers ui-corner-all ui-state-error",
						"html": "<p><span class=\"icon picon_32x32_status_dialog-warning\"></span> The following logged in customers are critically low on points!</p>"
					});
					$.each(criticals, function(){
						new_criticals.append($("<div />", {
							"class": "customer",
							"html": this.guid+": "+this.name
						}).append($("<div />", {
							"css": {"float": "right"},
							"html": (this.points_remain >= 0 ? (this.points_remain * pines.com_customer_timer.ppm)+" Minutes Left" : (this.points_remain * -1 * pines.com_customer_timer.ppm)+" Minutes Over!")
						})));
					});
					new_dialog_contents.append(new_criticals);
				}
				if (warnings.length) {
					var new_warnings = $("<div />", {
						"class": "customers ui-corner-all ui-state-highlight",
						"html": "<p><span class=\"icon picon_32x32_status_dialog-information\"></span> The following logged in customers are low on points.</p>"
					});
					$.each(warnings, function(){
						new_warnings.append($("<div />", {
							"class": "customer",
							"html": this.guid+": "+this.name
						}).append($("<div />", {
							"css": {"float": "right"},
							"html": (this.points_remain >= 0 ? (this.points_remain * pines.com_customer_timer.ppm)+" Minutes Left" : (this.points_remain * -1 * pines.com_customer_timer.ppm)+" Minutes Over!")
						})));
					});
					new_dialog_contents.append(new_warnings);
				}
				if (alert_box.children().html() != new_dialog_contents.html())
					alert_box.html(new_dialog_contents);
				if (!alert_box.dialog("isOpen"))
					alert_box.dialog("open");
			}
		});
	}, 60000);
});