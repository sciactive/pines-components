pines(function(){
	var login_page = function(){
		var notice;
		$.ajax({
			url: pines.com_timeoutnotice.loginpage_url,
			type: "GET",
			dataType: "html",
			beforeSend: function(){
				notice = $.pnotify({
					text: "Loading login page...",
					title: "Login",
					icon: "picon picon-throbber",
					hide: false,
					history: false
				});
			},
			error: function(XMLHttpRequest, textStatus){
				notice.pnotify_remove();
				pines.error("An error occured while trying to load login page:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				notice.pnotify_remove();
				pines.pause();
				var login_dialog = $("<div />").html(data+"<br />").dialog({
					modal: true,
					resizable: false,
					title: "Login",
					width: 450,
					close: function(){
						check_time('check');
					},
					buttons: {
						"Login": function(){
							$.ajax({
								url: pines.com_timeoutnotice.login_url,
								type: "POST",
								dataType: "json",
								data: login_dialog.find(".com_timeoutnotice_login_form").serialize(),
								error: function(XMLHttpRequest, textStatus){
									pines.error("An error occured while trying to login:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
								},
								success: function(data){
									if (!data) {
										alert("Login failed.");
										return;
									}
									login_dialog.dialog("close").remove();
								}
							});
						}
					}
				});
				pines.play();
				login_dialog.find(".already_loggedin").click(function(){
					login_dialog.dialog("close").remove();
				}).end().find(".com_timeoutnotice_login_form").submit(function(){
					login_dialog.dialog("option", "buttons").Login();
					return false;
				}).find("input").keydown(function(e){
					if (e.keyCode == 13)
						login_dialog.dialog("option", "buttons").Login();
				}).eq(0).focus();
			}
		});
	};

	var logged_out = function(){
		switch (pines.com_timeoutnotice.action) {
			case "dialog":
			default:
				login_page();
				break;
			case "refresh":
				location.reload(true);
				break;
			case "redirect":
				pines.get(pines.com_timeoutnotice.redirect_url);
				break;
		}
	}

	var session_notice = false;
	var make_extend = function() {
		if (session_notice && session_notice.is(":visible"))
			return;
		session_notice = $.pnotify({
			title: "Session Timeout",
			text: "Your session is about to expire. <a href=\"javascript:void(0)\" class=\"extend_session\">Click here to stay logged in.</a>",
			icon: "picon picon-user-away",
			hide: false,
			history: false,
			mouse_reset: false
		});
		session_notice.find("a.extend_session").click(function(){
			$.ajax({
				url: pines.com_timeoutnotice.extend_url,
				type: "GET",
				dataType: "json",
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to extend your session:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					session_notice.pnotify_remove();
					if (!data) {
						logged_out();
						return;
					}
					alert("Your session has been extended.");
				}
			});
		});
	};
	
	var make_logout = function() {
		if (session_notice) {
			session_notice.pnotify_remove();
			session_notice = false;
		}
		logged_out();
	};

	var set_extend = false;
	var set_timedout = false;
	var extend_time = false;
	var timedout_time = false;
	var check_time = function(type){
		$.ajax({
			url: pines.com_timeoutnotice.check_url,
			type: "GET",
			dataType: "json",
			success: function(data){
				// If false, immediately get out.
				if (!data) {
					make_logout();
					return;
				}
				// If we need to check timed out.
				if (type == 'timedout' && data <= 0) {
					make_logout();
					return;
				}
				// If we need to check extend time
				if (type == 'extend' && data <= 60) {
					make_extend();
					return;
				}
				
				// If we are not simply checking, we came from above, and
				// should clear the time outs.
				if (type != 'check') {
					// Extend and/or timedout need to be re-calculated.
					if (session_notice) {
						session_notice.pnotify_remove();
						session_notice = false;
					}
					clearTimeout(set_extend);
					clearTimeout(set_timedout);
				}
				
				// All other situations, create check-back times.
				extend_time = ((data - 60) * 1000);
				timedout_time = (data * 1000);
				
				// Make the Notice
				set_extend = setTimeout(function(){
					check_time('extend');
				}, extend_time);
				
				set_timedout = setTimeout(function(){
					check_time('timedout');
				}, timedout_time);
			}
		});
	};
	check_time('check');
});