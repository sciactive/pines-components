$(function(){
	var alert_box = $("<div />", {
		"class": "com_customer_timer_alert",
		"title": "Customer Timer Warning"
	}).dialog({
		autoOpen: false,
		dialogClass: "com_customer_timer_dialog",
		modal: true,
		buttons: {
			"Hide for 5 Minutes": function(){
				document.cookie
				alert_box.dialog("close");
			}
		}
	});
	setInterval(function(){
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
				var new_dialog_contents = $("<div />");
				if (criticals.length) {
					var new_criticals = $("<div />", {
						"class": "customers ui-corner-all ui-state-error",
						"html": "<p>The following logged in customers are critically low on points!</p>"
					});
					$.each(criticals, function(){
						new_criticals.append($("<div />", {
							"class": "customer",
							"html": this.guid+": "+this.name
						}).append($("<div />", {
							"css": {"float": "right"},
							"html": "Critical"
						})));
					});
					new_dialog_contents.append(new_criticals);
				}
				if (warnings.length) {
					var new_warnings = $("<div />", {
						"class": "customers ui-corner-all ui-state-highlight",
						"html": "<p>The following logged in customers are low on points.</p>"
					});
					$.each(criticals, function(){
						new_warnings.append($("<div />", {
							"class": "customer",
							"html": this.guid+": "+this.name
						}).append($("<div />", {
							"css": {"float": "right"},
							"html": "Critical"
						})));
					});
					new_dialog_contents.append(new_warnings);
				}
				alert_box.html(new_dialog_contents);
				if (!alert_box.dialog("isOpen"))
					alert_box.dialog("open");
			}
		});
	}, 5000);
});