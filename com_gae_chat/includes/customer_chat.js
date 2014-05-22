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
welcomeChatURL = $("#welcome_chat_url").attr('data-url');

var customer_pic_url = $("#chat_customer_pic").attr('data-url');
var employee_pic_url = $("#chat_employee_pic").attr('data-url');
var chat_body = $("#main-chat-body");
var chat_messages = $("#main-chat-messages");
var chat_header = $("#main-chat-header");
var chat_status = chat_header.find('.chat-status');
var chat_status_text = chat_header.find('.chat-status-text');


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
    chat_status_text.html('Online');
}


/*
 * The onMessage function for the Google App Engine Channel Socket
 *
 * Checks to see what type of message it is and handles it appropriately
 */
function onChannelMessage(msg) {
    var data = jQuery.parseJSON(msg.data);
        if (data !== undefined) {
            
            if (data.type == "customer_message") {
                addMessageToHistory(data.message_id, data.from_channel, data.message, data.username);
                appendChannelMessage(data.message, data.username, getTimeagoString(data.timestamp), false);
            } else if (data.type == "employee_message") {
                addMessageToHistory(data.message_id, data.from_channel, data.message, data.username);
                appendChannelMessage(data.message, data.username, getTimeagoString(data.timestamp), true);
            } else if (data.type == "chat_history") {
                handleChatHistory(data);
                // After connecting and receiving the chat history, we want to do welcomeChat()
                welcomeChat();
            } else if (data.type == "online_check") {
                handleOnlineCheck();
            } else {
                chat_messages.append("<li><p>" + data.message + "</p></li>");
            }
        } else {
            // Ignore messages that don't have a type
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
    chat_status.addClass('Offline');
    chat_status_text.html('Offline');
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
                    appendChannelMessage(msg, data.username, getTimeagoString(timestamp), false);
                    addMessageToHistory(data.message_id, channel_id, msg, data.username);
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
                        appendChannelMessage(msg, data.username, getTimeagoString(timestamp), false);
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
    $("#main-chat-window").remove();
    $("#gae-chat-variables").remove();
}

/*
 * Posts to Pines to get a token for this person
 * 
 */
function connectToChannel(force) {
    chat_status.removeClass('offline').addClass('checking');
    chat_status_text.html('Connecting');
    $.ajax({
        type: "POST",
        url: getTokenURL,
        data: {"force_new_token": force},
        dataType: 'json',
        success: function(data) {
            if (data.status != 'success' || data.action == 'terminate') {
                removeGAEChat();
            } else {
                $("#main-chat-window").removeClass('hide');
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
function handleChatHistory(data) {
    var count = data.messages.length;

    for (var i=0; i<count; i++) {
        var message = data.messages[i];
        if (!chatHistory[message.message_id]) {
            appendChannelMessage(message.message, message.from_username, getTimeagoString(message.timestamp), channel_id != message.from_channel);
            addMessageToHistory(message.message_id, message.from_channel, message.message, message.from_username);
        }
    }

}


/*
 * Appends the Message to the Chat window
 * 
 * Floats it right or left based on if the message is coming from an employee
 * 
 */
function appendChannelMessage(message, username, timestamp, is_employee) {
    if (is_employee) {
        var chat_message = '<li class="left clearfix chat-message"><span class="chat-img pull-left"><img src="' + employee_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
                            '<div class="chat-message-left"><div class="header"><strong class="primary-font chat-username">' + username + '</strong> ' +
                            '<small class="pull-right text-muted">' +
                            '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small></div>' +
                                '<p>' + message + '</p></div></li>';
    } else {
        var chat_message = '<li class="right clearfix chat-message"><span class="chat-img pull-right"><img src="' + customer_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
            '<div class="chat-message-right"><div class="header">' +
              '<small class="text-muted">' +
              '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small>'+
              '<strong class="pull-right primary-font">You</strong>' +
            '</div><p>' + message + '</p></div></li>';
    }

    chat_messages.append(chat_message);
    chat_body.scrollTop(chat_messages.height());
    $("abbr.timeago").timeago();
    
}

function welcomeChat() {
    
    var params = {"channel_token": channel_token, "channel_id": channel_id};
    
    if ($.browser.msie && window.XDomainRequest) {
            // Use MS XDR
            var xdr = new XDomainRequest();
            xdr.onload = function() {
                // Don't care about results
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
            xdr.open("POST", welcomeChatURL);
            xdr.send($.param(params));
        } else {
            $.ajax({
                type: 'POST',
                url: welcomeChatURL,
                data: params,
                crossDomain: true,
                dataType: 'json',
                success: function() {
                    // Don't care
                },
                error: function () {
                    // No care
                }
            });
        }
}

/*
 * jQuery Listener for when the user presses the send button
 */
$('#chat-send-message-btn').click(function() {
    var msg = $("#chat-btn-input").val();
    if (msg) {
        sendMessage(msg);
        $("#chat-btn-input").val("");
    }
});

/*
 * jQuery Listener for when the user presses the enter key to send a message
 */
$("#chat-btn-input").on('keyup', function(e) {
    // jQuery normalizes the keys, using which
    // 13 == Enter Key
    var msg = $('#chat-btn-input').val();
    if (e.which == 13 && msg) {
        sendMessage(msg);
        $('#chat-btn-input').val("");
    }
});

/*
 * jQuery Listener to maximize chat window
 */
chat_header.on('click', function(e) {
    if (e.target != this) {
        return;
    }
    if ($(this).hasClass("chat-minimized")) {
        chat_body.show();
        $(this).removeClass("chat-minimized");
        $("#min-main-chat-btn").html("<i class='icon-chevron-down'></i>");
    }
});

/*
 * jQuery listener to switch between a max and min state for the chat window
 */
$("#min-main-chat-btn").on('click', function(e) {
    if (chat_header.hasClass("chat-minimized")) {
        chat_body.show();
        chat_header.removeClass("chat-minimized");
        $(this).html("<i class='icon-chevron-down'></i>");

    } else {
        chat_body.hide();
        chat_header.addClass("chat-minimized");
        $(this).html("<i class='icon-chevron-up'></i>");
    }
});

$.timeago.settings.strings.seconds = "seconds";
connectToChannel();

});