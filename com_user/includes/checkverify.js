pines(function(){
	var email_notice = $('.notice-email-verify');
	var gae_chat_box = $('#main-chat-window');
	var resend_verify_url = $('.resend-verification-url').text();
	var check_verify_url = $('.check-verification-url').text();
	
	// If gae chat is implemented, shift it up when the notice is on.
	function fix_chat_height() {
		if (!email_notice.is(':visible') || !gae_chat_box.length)
			return;
		var height = email_notice.outerHeight();
		gae_chat_box.css('bottom', height+'px');
	}
	
	function close_notice() {
		email_notice.fadeOut(350);
		setTimeout(function(){
			gae_chat_box.css('bottom', '5px');
		}, 350);
	}
	
	function check_verify() {
		$.ajax({
			url: check_verify_url,
			type: "POST",
			dataType: "json",
			success: function(data){
				if (data) {
					// Show the verify notice...
					email_notice.css('visibility', 'hidden').show();
					fix_chat_height();
					email_notice.hide().css('visibility', 'visible').fadeIn();
				}
			}
		});
	}
	
	$(window).resize(function(){
		fix_chat_height();
	});
	
	email_notice.find('.resend-verify').click(function(){
		$.post(resend_verify_url, function(data){
			if (data) {
				// Turn it green and change the text.. and then fade it out
				email_notice.removeClass('alert-error alert-info')
				.addClass('alert-success').find('.message')
				.html('Email Sent!').end().find('.leave-for-errors').hide();
				setTimeout(function(){
					close_notice();
				}, 2000);
			} else {
				// Turn it red
				email_notice.removeClass('alert-success alert-info')
				.addClass('alert-error').find('.message')
				.html('Email did not send properly.');
				email_notice.find('.label').removeClass('label-info').addClass('label-important')
			}
		}, "json");
	});
	
	
	
	function do_when_fully_loaded() {
		check_verify();
	}
	if (window.addEventListener)
		window.addEventListener("load", do_when_fully_loaded, false);
	else if (window.attachEvent)
		window.attachEvent("onload", do_when_fully_loaded);
	else 
		window.onload = do_when_fully_loaded;
	
	email_notice.click(function(e){
		var posX = $(window).width(),
            posY = $(this).offset().top;
		if ( ((posX - e.pageX) < 50 ) && ((e.pageY - posY) < 50) ) {
			close_notice();
		}
	});
});