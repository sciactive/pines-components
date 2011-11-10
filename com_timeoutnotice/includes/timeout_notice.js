pines(function(){
	var login_page = function(){
		var notice;
		$.ajax({
			url: pines.com_timeoutnotice.loginpage_url,
			type: "GET",
			dataType: "html",
			beforeSend: function(){
				notice = $.pnotify({
					pnotify_text: "Loading login page...",
					pnotify_title: "Login",
					pnotify_notice_icon: "picon picon-throbber",
					pnotify_hide: false,
					pnotify_history: false
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
						interval = setInterval(check_timeout, 120000);
						check_timeout();
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
		if (interval)
			clearInterval(interval);
		else
			return;
		interval = false;
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

	var session_notice;
	var timeout;
	var check_timeout = function(){
		$.ajax({
			url: pines.com_timeoutnotice.check_url,
			type: "GET",
			dataType: "json",
			success: function(data){
				if (!data) {
					if (session_notice)
						session_notice.pnotify_remove();
					logged_out();
					return;
				}
				if (data > 60) {
					if (timeout)
						clearTimeout(timeout);
					if (session_notice && session_notice.is(":visible"))
						session_notice.pnotify_remove();
				}
				if (data < 260) {
					timeout = setTimeout(function(){
						setTimeout(check_timeout, 21000);
						setTimeout(check_timeout, 41000);
						setTimeout(check_timeout, 61000);
						if (session_notice) {
							if (!session_notice.is(":visible"))
								session_notice.pnotify_display();
						} else {
							session_notice = $.pnotify({
								pnotify_title: "Session Timeout",
								pnotify_text: "Your session is about to expire. <a href=\"javascript:void(0)\" class=\"extend_session\">Click here to stay logged in.</a>",
								pnotify_notice_icon: "picon picon-user-away",
								pnotify_hide: false,
								pnotify_history: false,
								pnotify_mouse_reset: false
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
						}
					}, (data - 60) * 1000);
				}
			}
		});
	};

	var interval = setInterval(check_timeout, 120000);
});