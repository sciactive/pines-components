// Using pines function so that this will load after the page
pines(function() {

var gae_variables = $("#gae-chat-variables");
if (!gae_variables.length) {
    // Don't have the div, get out
    return;
}
    

connectedToChannel = false;
chatHistory = {};
channel_errors = false;
channel_retries = 0;

sendMessageURL = $("#send_message_url").attr('data-url');
getTokenURL = $("#get_token_url").attr('data-url');
onlineTestURL = $("#online_test_url").attr('data-url');
onlineCheckURL = $("#send_online_check_url").attr('data-url');
getMessagesURL = $("#get_messages_url").attr('data-url');

var chat_window = $("#main-chat-window");
var customer_pic_url = $("#chat_customer_pic").attr('data-url');
var employee_pic_url = $("#chat_employee_pic").attr('data-url');
var chat_body = $("#main-chat-body");
var chat_messages = $("#main-chat-messages");
var chat_header = $("#main-chat-header");
var chat_footer = $("#main-chat-footer");
var chat_notice_bar = $("#main-chat-notice");
var chat_send_message_btn = $('#chat-send-message-btn');
var chat_message_input = $("#chat-btn-input");
var chat_toggle_icon_container = $('.main-chat-notice-toggle');
var chat_toggle_icon = chat_toggle_icon_container.find('i');
var chat_container = chat_window.find('.chat-container');
var chat_status = chat_notice_bar.find('.chat-status');
var page_title = $('title');

// Messages Notices
var chat_notice_messages_container = chat_notice_bar.find('.main-chat-notice-messages');
var chat_notice_messages_num = chat_notice_bar.find('.main-chat-notice-num-messages');


var start_width = 0;
var start_height = 0;
/*
 * Sets the Channel Token and ID
 * Proceeds to connect to the channel
 *
 */
function setChannelInfo(chan_id, chan_token) {
    channel_id = chan_id;
    channel_token = chan_token;
    setUpChannel();
}


/**
 * PolyFill Function provided by Mozilla
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/toISOString
 * 
 * Used so that if the person is on an older browser, it defines a prototype function to Date so we can call .toISOString()
 * Need the ISOString for compatibility with the timeago jQuery plugin.
 */
if ( !Date.prototype.toISOString) {
    ( function() {
        function pad(number) {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        }
        
        Date.prototype.toISOString = function () {
            return this.getUTCFullYear() +
                '-' + pad(this.getUTCMonth() + 1) +
                '-' + pad(this.getUTCDate()) + 
                'T' + pad(this.getUTCHours()) +
                ':' + pad(this.getUTCMinutes()) +
                ':' + pad(this.getUTCSeconds()) +
                '.' + (this.getUTCMilliseconds() / 1000).toFixed(3).slice(2,5) +
                'Z';
        };
    }() );
}


/*
 * Get's the message's real timestamp with the correct offset
 * This is used to make all messages appear in local time
 *
 */
function getTimeagoString(time) {
    return new Date(Number(time)).toISOString();
}

/*
 * Adds the message to the chatHistory object
 * Used to keep track of our messages so we don't show duplicate messages
 * 
 */
function addMessageToHistory(message_id, chan_id, message, username) {
    chatHistory[message_id] = {from_channel: chan_id, message: message, username: username};
}

/*
 * The OnOpen function for the Google App Engine Channel Socket
 * 
 * Set's the var connectToChannel to true and changes the online indicator
 */
function onChannelOpen() {
    connectedToChannel = true;
    chat_status.removeClass('offline checking');
	if (chat_visibility == 'open' && $(window).width() > 768) {
		chat_notice_bar.click();
	}
	chat_toggle_icon.removeClass('icon-spin icon-spinner icon-ban-circle');
	adjust_toggle_icon();
    getMessages();
}


/*
 * The onMessage function for the Google App Engine Channel Socket
 *
 * Checks to see what type of message it is and handles it appropriately
 */
function onChannelMessage(msg) {
    var data = jQuery.parseJSON(msg.data);
        if (data !== undefined) {
            if (data.type == "message") {
                appendChannelMessage(data.message.message, data.message.from_username, getTimeagoString(data.message.timestamp), true, true);
				update_messages();
            } else if (data.type == "customer_message") {
                addMessageToHistory(data.message_id, data.from_channel, data.message, data.username);
                appendChannelMessage(data.message, data.username, getTimeagoString(data.timestamp), false, true);
				update_messages();
            } else if (data.type == "employee_message") {
                addMessageToHistory(data.message_id, data.from_channel, data.message, data.username);
                appendChannelMessage(data.message, data.username, getTimeagoString(data.timestamp), true, true);
				update_messages();
            } else if (data.type == "online_check") {
                handleOnlineCheck();
            } else {
                chat_messages.append("<li><p>" + data.message + "</p></li>");
            }
        }
}


/*
 * The onError function for the Google App Engine Channel Socket
 *
 * Checks the error code to see if the person has timed out (Code: 401)
 */
function onChannelError(err) {
    if (err.code == "401") {
        channel_errors = true;
    }
}

/*
 * The onClose function for the Google App Engine Channel Socket
 *
 * Called when the socket is closed. We will check if an expired token caused it, and if it did, then reconnect to the channel
 */
function onChannelClose() {
    connectedToChannel = false;
    chat_status.addClass('offline');
	chat_window.addClass('chat-minimized');
	$('body').removeClass('chat-mobile-open');
	adjust_toggle_icon();
	chat_toggle_icon.removeClass('icon-chevron-down icon-chevron-down icon-spin icon-spinner')
	.addClass('icon-ban-circle');
    if (channel_errors && channel_retries < 5) {
        channel_retries++;
        connectToChannel("true");

    }
}

/*
 * Connects to the App Engine Channel
 * 
 * Opens the connection and sets the onopen, onmessage, onerror, and onclose methods
 */
function setUpChannel() {
    channel = new goog.appengine.Channel(channel_token);
    socket = channel.open();
    socket.onopen = onChannelOpen;
    socket.onmessage = onChannelMessage;
    socket.onerror = onChannelError;
    socket.onclose = onChannelClose;
}


/*
 * Handles an Online Check
 * 
 * This is used to respond to a ping and to let the employees know that the customer is still online
 */
function handleOnlineCheck() {
    // Need to make sure we have http and https enabled on app engine
    var params = {"channel_id": channel_id, "channel_token": channel_token};
    
    if ($.browser.msie && window.XDomainRequest) {
        var xdr = new XDomainRequest();
        // Need to have all xdr methods defined here. IE 8 doesn't care, but IE 9 does
        xdr.onload = function() {
            // We don't really care about letting the employee know about their status, they should know
        };
        xdr.onerror = function() {
            // Again, nothing we can really do, we just want them to POST back to the App Engine
            // If they can't do that, then the employee will know
        };
        xdr.ontimeout = function() {
            // Don't care about the timeout
        };
        xdr.onprogress = function() {
            // Don't care about onprogress being called
        };
        xdr.timeout = 8000;
        xdr.open("POST", onlineTestURL);
        xdr.send($.param(params));
        
    } else {
        $.ajax({
            type: "POST",
            url: onlineTestURL,
            data: params,
            crossDomain: true,
            dataType: 'json',
            success: function() {
                // Don't really care about the success
            },
            error: function() {
                // Don't care about the error
            }
        });
    }
}

// Need to check for IE 8 if we are doing CORs Request
/*
 * Send the message to the employees
 * 
 * 
 */
function sendMessage(msg) {
    if (connectedToChannel) {
        var timestamp = new Date().getTime();
        
        var params = {"channel_id": channel_id, "msg": msg, "timestamp": timestamp, "channel_token": channel_token};
        
        if ($.browser.msie && window.XDomainRequest) {
            // Use MS XDR
            var xdr = new XDomainRequest();
            xdr.onload = function() {
                var data = JSON.parse(xdr.responseText);
                if (data.status == "success") {
                    appendChannelMessage(msg, data.username, getTimeagoString(timestamp), false, false);
                    addMessageToHistory(data.message_id, channel_id, msg, data.username);
					update_messages();
                }
            };
            xdr.onerror = function() {
                chat_messages.append('<li><p>There was an error sending the message. Please try again</p></li>');
                chat_body.scrollTop(chat_messages.height());
            };
            xdr.ontimeout = function() {
                chat_messages.append('<li><p>There was a timeout error. It took too long to send the message. Please try again</p></li>');
                chat_body.scrollTop(chat_messages.height());
            };
            xdr.onprogress = function() {
                // Don't need to update them on progress
            };
            xdr.timeout = 8000;
            xdr.open("POST", sendMessageURL);
            xdr.send($.param(params));
        } else {
            $.ajax({
                type: 'POST',
                url: sendMessageURL,
                data: params,
                crossDomain: true,
                dataType: 'json',
                success: function(data) {
                    if (data.status == "success") {
                        appendChannelMessage(msg, data.username, getTimeagoString(timestamp), false, false);
						update_messages();
                    }
                },
                error: function () {
                    chat_messages.append('<li><p>There was an error sending the message. Please try again</p></li>');
                    chat_body.scrollTop(chat_messages.height());
                }
            });
        }
    } else {
        chat_messages.append('<li><p>Need to be connected to Chat to send messages</p></li>');
        chat_body.scrollTop(chat_messages.height());
    }

}

/*
 * Removes the Chat Div (For when we want to deny chat access)
 */
function removeGAEChat() {
    // We need to remove chat because this person doesn't have permission
    chat_window.remove();
    gae_variables.remove();
}

/*
 * Posts to Pines to get a token for this person
 * 
 */
function connectToChannel(force) {
    chat_status.removeClass('offline').addClass('checking');
	chat_toggle_icon.removeClass('icon-chevron-down icon-chevron-down icon-ban-circle')
	.addClass('icon-spin icon-spinner');
	// Connecting!
    $.ajax({
        type: "POST",
        url: getTokenURL,
        data: {"force_new_token": force},
        dataType: 'json',
        success: function(data) {
            if (data.status != 'success' || data.action == 'terminate') {
                removeGAEChat();
            } else {
                chat_window.removeClass('hide');
                setChannelInfo(data.channel_id, data.channel_token);
            }
        },
        error: function() {
            // Let the user know we could not connect to get a token
            chat_messages.append('<li><p>There was an error connecting to chat. Please refresh your page.</p></li>');
            chat_body.scrollTop(chat_messages.height());
        }
    });
}

/*
 * Handles the chat history of customers
 * Appends the messages to the chat window if they haven't already been appended
 */
function handleChatHistory(messages) {
    var count = messages.length;

    for (var i=0; i<count; i++) {
        var message = messages[i];
        if (!chatHistory[message.message_id]) {
            appendChannelMessage(message.message, message.from_username, getTimeagoString(message.timestamp), channel_id != message.from_channel, false);
            addMessageToHistory(message.message_id, message.from_channel, message.message, message.from_username);
        }
    }
	update_messages();
}


/*
 * Appends the Message to the Chat window
 * 
 * Floats it right or left based on if the message is coming from an employee
 * 
 */
function appendChannelMessage(message, username, timestamp, is_employee, count_unread) {
    if (is_employee) {
        var chat_message = '<li class="left clearfix chat-message main-trans"><span class="chat-img pull-left"><img src="' + employee_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
                            '<div class="chat-message-left"><div class="header"><strong class="primary-font chat-username">' + username + '</strong> ' +
                            '<small class="pull-right text-muted">' +
                            '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small></div>' +
                                '<p>' + message + '</p></div></li>';
    } else {
        var chat_message = '<li class="right clearfix chat-message main-trans"><span class="chat-img pull-right"><img src="' + customer_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
            '<div class="chat-message-right"><div class="header">' +
              '<small class="text-muted">' +
              '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small>'+
              '<strong class="pull-right primary-font">You</strong>' +
            '</div><p>' + message + '</p></div></li>';
    }

    chat_messages.append(chat_message);
    chat_body.scrollTop(chat_messages.height());
    $("abbr.timeago").timeago();
    
//	if (count_unread && chat_window.hasClass('chat-minimized')) {
//		message_num++;
//		make_cookie('main_chat_messages_num', message_num, 2);
//		chat_notice_messages_num.text(message_num).addClass('blink-me');
//		title_notice('on');
//		chat_notice_messages_container.fadeIn();
//	}
	if (count_unread && chat_window.hasClass('chat-minimized')) {
		chat_messages.find('.chat-message').last().addClass('flash');
	}
}

function getMessages() {
    
    var params = {"channel_token": channel_token, "channel_id": channel_id};
    
    if ($.browser.msie && window.XDomainRequest) {
            // Use MS XDR
            var xdr = new XDomainRequest();
            xdr.onload = function(data) {
                // Don't care about results
                if (data.status == 'success') {
                    handleChatHistory(data.messages);
                }
            };
            xdr.onerror = function() {
                // Don't care
            };
            xdr.ontimeout = function() {
                // Don't care
            };
            xdr.onprogress = function() {
                // Don't need to update them on progress
            };
            xdr.timeout = 8000;
            xdr.open("GET", getMessagesURL);
            xdr.send($.param(params));
        } else {
            $.ajax({
                type: 'GET',
                url: getMessagesURL,
                data: params,
                crossDomain: true,
                dataType: 'json',
                success: function(data) {
                    if (data.status == 'success') {
                        handleChatHistory(data.messages);
                    }
                },
                error: function () {
                    // No care
                }
            });
        }
}

/*
 * Determines the correct style/sizing for mobile and non mobile chat.
 */
function size_chat(window_resize) {
	var window_width = $(window).width();
	var window_height = $(window).height();

	var header_height = chat_header.outerHeight();
	var nav_height = $('#nav').outerHeight();
	var footer_height = chat_footer.outerHeight();
	var notice_height = chat_notice_bar.outerHeight();
	var set_body_height = (window_height - (nav_height + header_height + footer_height + notice_height)) - (chat_body.outerHeight() - chat_body.height()) + 10;
	var set_container_height = window_height - (notice_height + nav_height) + 10;
	if (window_width < 768 && !chat_window.hasClass('chat-minimized')) {
		// Do all maximized mobile chat things here
		$('html,body').addClass('chat-mobile-open');
		$('body').css('height', window_height+'px');
		chat_container.css('height', set_container_height+'px');
		chat_body.css('height', set_body_height+'px');
		
		// On mobile, dont use this cookie.
		erase_cookie('main_chat_visibility');
	} else {
		// Undo maximized mobile chat
		$('html,body').removeClass('chat-mobile-open');
		$('body').removeAttr('style');
		chat_container.add(chat_body).removeAttr('style');
		
		// Erase the cookie even if minimized on MOBILE.
		if (window_width < 768) {
			erase_cookie('main_chat_visibility');
		}
	}

	start_width = window_width;
	start_height = window_height;
}

/*
 * Figures out the correct up or down arrow to apply to the chat toggle icon.
 */
function adjust_toggle_icon() {
	chat_toggle_icon.removeClass('icon-chevron-up icon-chevron-down');
	if (chat_window.hasClass('chat-minimized')) {
		chat_toggle_icon.addClass('icon-chevron-up');
	} else {
		chat_toggle_icon.addClass('icon-chevron-down');
	}
}

/*
 * Make a Cookie.
 */
function make_cookie(name,value,days){
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

/*
 * Read a Cookie.
 */
function read_cookie(name){
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
};

/*
 * Delete a Cookie.
 */
function erase_cookie(name) {
    make_cookie(name,"",-1);
}

/*
 * Keep track of messages
 */
var messages_num = 0;
var first_message_load = true;
function update_messages(){
	var chat_messages = chat_window.find('.chat-message');
	// Read Cookie, Set Unread messages
	if (first_message_load) {
		var cookie = read_cookie('main_chat_messages_num');
		if (cookie !== null) {
			messages_num = parseInt(cookie);
			var flash_num = -1 * messages_num;
			chat_messages.slice(flash_num).addClass('flash');
			toggle_indicator(messages_num);
			title_notice('on');
		}
		first_message_load = false;
	} else {
		// Set the cookie based on flashes.
		messages_num = chat_messages.filter('.flash').length;
		make_cookie('main_chat_messages_num', messages_num, 1);
		
		// If we have messages..
		if (messages_num > 0 && chat_window.hasClass('chat-minimized')) {
			toggle_indicator(messages_num);
			title_notice('on');
		} else {
			erase_cookie('main_chat_messages_num');
			toggle_indicator(0);
			title_notice('off');
		}
	}
}

function check_message_cookies() {
	// We never set the message cookies to 0, we erase it.
	// So if there's no cookie after we've loaded, the user viewed it on another
	// chat tab.
	var chat_messages = chat_window.find('.chat-message');
	var cookie = read_cookie('main_chat_messages_num');
	if (cookie == null && chat_messages.filter('.flash').length) {
		chat_messages.removeClass('flash');
		update_messages();
	}
}


/*
 * Toggle the indicator on the chat bar to alert the user of messages.
 */
function toggle_indicator(num) {
	if (num > 0) {
		chat_notice_messages_num.text(num);
		chat_notice_messages_container.fadeIn();
	} else {
		chat_notice_messages_container.fadeOut();
	}
}



var title_timeout = null;
/*
 * The function to call in the title timeout.
 * - Checks the cookie to ensure accurracy.
 */
function title_notice_blink() {
	var new_words = (message_num == 1) ? messages_num+' Unread Message!' : messages_num+' Unread Messages!';
	var original_words = page_title.attr('data-title');
	if (page_title.text() == original_words)
		page_title.text(new_words);
	else
		page_title.text(original_words);
	check_message_cookies(); // Dangerous possible recursion.
	title_notice('on');
	
}

/*
 * Make the page title blink when there's new messages.
 * 
 */
function title_notice(on_off){
	clearTimeout(title_timeout);
	if (on_off == 'off' || messages_num < 1) {
		page_title.text(page_title.attr('data-title'));
		return;
	}
	
	title_timeout = setTimeout(title_notice_blink, 1000);
}



/*
 * jQuery Listener for when the user presses the send button
 */
chat_send_message_btn.click(function() {
    var msg = chat_message_input.val();
    if (msg) {
		size_chat(false);
        sendMessage(msg);
        chat_message_input.val("");
    }
});

/*
 * jQuery Listener for when the user presses the enter key to send a message
 */
chat_message_input.on('keyup', function(e) {
    // jQuery normalizes the keys, using which
    // 13 == Enter Key
    var msg = chat_message_input.val();
    if (e.which == 13 && msg) {
        sendMessage(msg);
        chat_message_input.val("");
    }
});

/*
 * jQuery listener to switch between a max and min state for the chat window
 */
chat_notice_bar.on('click', function(e) {
	if (chat_toggle_icon_container.hasClass('offline') || chat_toggle_icon_container.hasClass('checking'))
		return;
    if (chat_window.hasClass("chat-minimized")) {
		chat_window.attr('data-offset', window.pageYOffset);
		$('html,body').animate({
			scrollTop: 0
		}, 100);
		chat_window.removeClass("chat-minimized");
		chat_toggle_icon_container.html("<i class='icon-chevron-down'></i>");
		update_messages();

		// Flash New Messages When Opened.
		var chat_messages = $('.chat-message').filter('.flash');
		setTimeout(function(){
			chat_messages.removeClass('flash');
		}, 4000);

		make_cookie('main_chat_visibility', 'open', 2);
    } else {
		$('html,body').animate({
			scrollTop: parseInt(chat_window.attr('data-offset'))
		}, 400);
        chat_window.addClass("chat-minimized");
        chat_toggle_icon_container.html("<i class='icon-chevron-up'></i>");
		make_cookie('main_chat_visibility', 'closed', 2);
    }
	size_chat(false);
});

/*
 * jQuery Listener for when the browser is resized.
 */
$(window).resize(function(){
	size_chat(true);
	if (!chat_window.hasClass('chat-minimized')) {
		var js_chat_body = document.getElementById('main-chat-body');
		chat_body.animate({
			scrollTop: js_chat_body.scrollHeight
		}, 300);
	}
}).resize();


/* 
 * Read Cookies
 * 
 */
var read_message_num_cookie = read_cookie('main_chat_messages_num');
var message_num = (read_message_num_cookie != null) ? read_message_num_cookie : 0;
// Cookies for Chat Open/Close - NO MOBILE cookies for this.
var chat_visibility = read_cookie('main_chat_visibility');


// Set Existing page title
page_title.attr('data-title', page_title.text());
// Set Timeago Settings
$.timeago.settings.strings.seconds = "seconds";

/*
 * Wait to load chat.
 * - Wait for click, scroll, or do not wait if cookie set.
 * - only this intense for customers to decrease initial page load time.
 */
function do_when_fully_loaded() {
	pines(function(){
		var main_chat_window = $('#main-chat-window');
		var main_chat_notice = $('#main-chat-notice');
		var first_time = true;
		// Save cookie to say that the customer has already been to the page once.
		var loaded_already = read_cookie('chat-loaded');
		if (loaded_already != null) {
			connectToChannel(); // Load the chat without waiting.
			return; // don't set the listeners below.
		}
		
		// Default, show the chat bar, but nothing loaded yet with channels.
		main_chat_window.removeClass('hide');
		
		// Make Channels Load on Click
		main_chat_notice.click(function(){
			if (first_time) {
				first_time = false;
				make_cookie('chat-loaded', 'true', 7);
				connectToChannel();
			}
		});
		
		// Make Channels Load on Scroll
		$(document).scroll(function(){
			if (first_time) {
				make_cookie('chat-loaded', 'true', 7);
				first_time = false;
				connectToChannel();
			}
		});
	});
}

if (window.addEventListener)
	window.addEventListener("load", do_when_fully_loaded, false);
else if (window.attachEvent)
	window.attachEvent("onload", do_when_fully_loaded);
else 
	window.onload = do_when_fully_loaded;

});