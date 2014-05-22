pines(function() {
    var gae_chat_variables = $("#gae-chat-variables");
    // If we don't have the div, get out
    if (!gae_chat_variables.length) {
        return;
    }
    
    sendMessageURL = $("#send_message_url").attr('data-url');
    getTokenURL = $("#get_token_url").attr('data-url');
    onlineTestURL = $("#online_test_url").attr('data-url');
    onlineCheckURL = $("#send_online_check_url").attr('data-url');
    minMainChatBtn = $("#min-main-chat-btn");
    
    // Setting this to false so that when we get the chat_history, we won't get notifications all at once
    // This will allow for new messages to have notifications, but not chat history
    displayNotifications = false;
    
    connectedToChannel = false;
    
    // Structure will be {channel_id1: {username: 'username', token: 'token', online_status: 'status', city: 'city', state: 'state'}, channel_id2: {}, channel_id3: {}}
    // This way, I can set up the initial objects and then lookup based on channel id to get the info about a customer
    connected_clients = {};
    extra_clients = {};
    // Variable to hold all of our chat messages so we don't end up duplication messages
    chatHistory = {};
    needToRestartChannels = false;
    
    var customer_pic_url = $("#chat_customer_pic").attr('data-url');
    var employee_pic_url = $("#chat_employee_pic").attr('data-url');
    
    var main_chat_window = $("#main-chat-window");
    var main_chat_body = $("#main-chat-body");
    var main_chat_header = $("#main-chat-header");
    var chat_status = main_chat_header.find('.chat-status');
    var chat_status_text = main_chat_header.find('.chat-status-text');
    var window_width = $(window).width();
    
    // Going to determine the chat window size and how much spacing there will be between chat windows
    // As percentage
    var regular_chat_div_width = main_chat_window.width() / window_width;
    var regular_chat_div_margin_extra = 1;
    // Make sure to add the margin-right into the calculation
    var chat_window_margin = parseInt(main_chat_window.css('right'));
    var main_chat_window_width_percentage = (main_chat_window.width() + chat_window_margin) / $(window).width();

    var used_chat_percent = main_chat_window_width_percentage * 100;

    var openClients = [];

    // From Mozilla
    // Modified for our purposes, we know that we have compatiable browsers
    function notifyMe(title, message, tag) {
            if (!displayNotifications) {
                return;
            }
            
            if (Notification.permission === "granted") {
                // If it's okay let's create a notification
                var notification = new Notification(title, {body: message, tag: tag});
            }

            // Otherwise, we need to ask the user for permission
            // Note, Chrome does not implement the permission static property
            // So we have to check for NOT 'denied' instead of 'default'
            else if (Notification.permission !== 'denied') {
                Notification.requestPermission(function (permission) {
                    // Whatever the user answers, we make sure we store the information
                    if(!('permission' in Notification)) {
                        Notification.permission = permission;
                    }

                    // If the user is okay, let's create a notification
                    if (permission === "granted") {
                        var notification = new Notification(title, {body: message, tag: tag});
                    }
                });
            }

    // At this point, we don't have permission, let's leave the person alone
    }
    
    String.prototype.capitalize = function() {
        return this.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
    }
    
    /*
     * Add the message to the chat history object
     */
    function addToChatHistory(message_id, from_channel, username, timestamp, message) {
        if (!connected_clients[from_channel]) {
            connected_clients[from_channel] = {username: username};
        }
        chatHistory[message_id] = {"from_channel": from_channel, "username": username, "timestamp": timestamp, "message": message}
    }

    /*
     * Set's the channel info parameters and connect to the channel
     */
    function setChannelInfo(chan_id, chan_token, username, guid) {
        channel_id = chan_id;
        channel_token = chan_token;
        channel_username = username;
        channel_guid = guid;
        // Set the cookie for the token
        setUpChannel();
    }
    
    /*
     * The Google App Engine Channel onOpen function
     */
    function onChannelOpen() {
        chat_status.removeClass('offline checking');
        chat_status_text.html('Online');
        connectedToChannel = true;
    }
    
    
    /*
     * The Google App Engine Channel onMessage function
     * Get's called when their is a new channel message
     * Get's the type of message and handles it accordingly
     *
     */
    function onChannelMessage(msg) {
        // Parse the data that comes through
        var data = JSON.parse(msg.data);
        if (data !== undefined) {
            if (data.type == 'customer_connect') {
                addNewChannelCustomer(data, false);
                handleMessageHistory(data);

            } else if (data.type == 'customer_disconnect') {
                handleChannelDisconnect(data);

            } else if (data.type == 'employee_connect') {
                addNewChannelCustomer(data, true);

            } else if (data.type == 'employee_disconnect') {
                handleChannelDisconnect(data);

            } else if (data.type == "customer_message") {
                handleCustomerChannelMessage(data, false);

            } else if (data.type == "employee_message") {
                handleEmployeeChannelMessage(data, true);

            } else if (data.type == "employee_to_employee") {
                handleEmployeeToEmployeeMessage(data, true);

            } else if (data.type == "online_users") {
                handleOnlineUsersList(data);

            } else if (data.type == "online_check") {
                handleOnlineTest(data);
                
            } else if (data.type == "customer_check") {
                handleCustomerReconnect(data);
                
            } else if (data.type == "message_history") {
                handleMessageHistory(data);
                displayNotifications = true;
                
            } else if (data.type == "customer_update") {
                updateCustomerInfo(data.channel_id, data.city, data.region, data.page_url);
                
            } else {
                // For some reason, this is a message we don't know about.
                // We want to see it just so we can see what the hell is going on
                handleCustomerChannelMessage(data);
            }

        } else {
            // Malformed Message, Ignore it
        }
    }
    
    /*
     * The Google App Engine Channels onError handler
     */
    function onChannelError(err) {
        if (err.code == '401') {
            needToRestartChannels = true;
        }
    }
    
    /*
     * The Google App Engine Channels onClose Handler
     * 
     * Sets connectedToChannel to false and attempts to restartChannel is we timed out
     */
    function onChannelClose() {
        chat_status.removeClass('checking').addClass('offline');
        connectedToChannel = false;
        if (needToRestartChannels) {
            restartChannel();
        }
    }
    
    /*
     * Updates customer info with their current city and region (Based on IP)
     */
    function updateCustomerInfo(chan_id, city, region, page_url) {
        var chat_div = $("#" + chan_id + "-div");
        if (!chat_div.length) return;
        chat_div.find('.chat-client-div-info').html(city.capitalize() + ", " + region.capitalize());
        chat_div.find('.last-page-url').html('<a href="' + page_url + '" target="_blank">' + page_url + '</a>');
    }
    
    /*
     * Restarts the channel by forcing to get a new token
     */
    function restartChannel() {
        needToRestartChannels = false;
        connectToEmployeeChannel(true);
    }
    
    /*
     * Plays an open-source notification
     */
    function playNotification() {
        // If you want sound, add this element with the id and give it a sound
        document.getElementById("channel-notification").pause();
        document.getElementById("channel-notification").play();
    }
    
    /*
     * Opens the channel and sets the sockets various handlers
     */
    function setUpChannel() {
        channel = null;
        socket = null;
        channel = new goog.appengine.Channel(channel_token);
        socket = channel.open();
        socket.onopen = onChannelOpen;
        socket.onmessage = onChannelMessage;
        socket.onerror = onChannelError;
        socket.onclose = onChannelClose;
    }

    /*
     * Function to realign the chat divs when we remove one or when we add one
     * 
     * Iterates over the openClients and assigns them all the correct margin
     * 
     */
    function realignChatWindows() {
        var chat_windows_open = openClients.length;

        for (var i=0; i<chat_windows_open; i++) {
            var chan_id = "#" + openClients[i];
            if (i==0) {
                // Need to just add it to the regular chat div width
                var margin_to_right = used_chat_percent + (i * regular_chat_div_width * 100) + 2;
                var margin_for_right_percent = margin_to_right.toString() + "%";
            } else {
                var first_chan_width = "#" + openClients[0];
                var real_div_width = $(first_chan_width).width() / $(window).width();
                var margin_to_right = used_chat_percent + (i * real_div_width * 100) + (i + 1 + regular_chat_div_margin_extra);
                var margin_for_right_percent = margin_to_right.toString() + "%";
            }
            
            $(chan_id).css('right', margin_for_right_percent);
            $(chan_id).show();
            $(chan_id + "-chat-body").show();
        }
    }

    
    /*
     * Appends the messages to the ChatHistory object as well as appending to the chat window
     *
     * Can make it so saving messages to localStorage as well
     *
     */
    function handleMessageHistory(data) {
        if (!data.messages) {
            return;
        }
        var count = data.messages.length;
        if (count < 1) {
            return;
        }
        displayNotifications = false;
        for (var i=0; i<count; i++) {
            var message = data.messages[i];
            if (!chatHistory[message.message_id]) {
                createChannelWindow(message.channel_id);
                appendChannelMessage(message.channel_id, message.message, message.from_username, getTimeago(message.timestamp), "#"+message.channel_id+"-chat", message.from_channel == channel_id, message.message_id);
                if (message.page_url) {
                    $("#" + message.channel_id + "-div").find('.last-page-url').html('<a href="' + message.page_url + '" target="_blank">' + message.page_url + '</a>');
                }
            }
        }
        displayNotifications = true;
    }
    
    /*
     * Handles a customer reconnecting to the channel
     * 
     */
    function handleCustomerReconnect(data) {
        // We have a customer who reconnected
        $("#" + data.channel_id + "-div").show();
        $('#'+data.channel_id).find('.chat-status').removeClass('offline checking');
        connected_clients[data.channel_id].checking_online = false;
        addNewChannelCustomer(data, false);
    }

    /*
     * Pings the customer's channel to see if they are still online
     */
    function sendOnlineCheck(customer_channel) {
        $('#'+customer_channel).find('.chat-status').removeClass('offline').addClass('checking');
        connected_clients[customer_channel].checking_online = true;


        $.ajax({
           type: 'POST',
           url: onlineCheckURL,
           data: {"customer_channel": customer_channel, "channel_token": channel_token},
           crossDomain: true,
           dataType: 'json',
           success: function () {
              setTimeout(function() {
                  if (connected_clients[customer_channel].checking_online) {
                      $('#'+customer_channel).find('.chat-status').removeClass('checking').addClass('offline');
                      connected_clients[customer_channel].checking_online = false;
                  } else {
                  }
              }, 5000);
           },
           error: function () {
               // No need to handle error
           }
       });
    }
    
    /*
     * Handles the response to an online check
     */
    function handleOnlineTest() {

        $.ajax({
            type: 'POST',
            url: onlineTestURL,
            data: {"channel_token": channel_token},
            crossDomain: true,
            dataType: "json",
            success: function () {
                // No need to do anything
            },
            error: function () {
                // No need to do anything
            }
        });

    }

    
    /*
     * Adds the online users to their respective categories in the main chat window
     * Need to save all the tokens here as well so we can just use tokens to send messages
     *
     */
    function handleOnlineUsersList(data) {
        var count = data.online_users.length;
        var e_count = data.online_employees.length;
        for (var i=0; i<count; i++) {
            addNewChannelCustomer(data.online_users[i], false);	
        }

        for (var i=0; i<e_count; i++) {
            addNewChannelCustomer(data.online_employees[i], true);
        }
    }
    
    /*
     * Get's the timeago string with the user's own timezone
     */
    function getTimeago(time) {
        return new Date(Number(time)).toISOString();
    }


    // Convience function to test whether a given element already exists
    function doesElementExist(element_id) {
        return document.contains(document.getElementById(element_id));
    }
    
    function setNewMessageIndicator(chat_div) {
        var new_message_indication = setInterval(function() {
                if ($(chat_div).hasClass('alert-info')) {
                    $(chat_div).removeClass('alert-info');
                } else {
                    $(chat_div).addClass('alert-info');
                }
            }, 1000);
            
        var new_message_timeout = setTimeout(function() {
            clearInterval(new_message_indication);
            $(chat_div).addClass('alert-info');
        }, 5000);

        $(chat_div).on('click', function() {
            // jQuery listerner for when employees clicks on name of person with pending message
            clearInterval(new_message_indication);
            clearTimeout(new_message_timeout);
            $(chat_div).removeClass('alert-info');
        });
    }
    
    
    /*
     * Handles an employee to employee message
     * Checks to see if it's our message or not and appends it to the right chat window
     */
    function handleEmployeeToEmployeeMessage(data) {
        // Is this the message we sent to another employee
        if (data.from_channel == channel_id) {
            // This is just the message that we sent and we are getting it back as confirmation
            if (document.contains(document.getElementById(data.to_channel))) {
                //$("#" + data.to_channel + "-chat-body").show();
                var chat_id = "#" + data.to_channel + "-chat";
                appendChannelMessage(data.to_channel, data.message, channel_username, getTimeago(data.timestamp), chat_id, true, data.message_id);
            } else {
                createChannelWindow(data.to_channel);
                handleEmployeeToEmployeeMessage(data);
            }

        } else {
            // This is a message coming from someone else
            // We need to see who is sending this message
            // Here we need to make a mark as this is an employee-to-employee
            // Also need to check if it is the first message that is being sent
            if (document.contains(document.getElementById(data.from_channel))) {
                //$("#" + data.from_channel + "-chat-body").show();
                var chat_id = "#" + data.from_channel + "-chat";
                appendChannelMessage(data.from_channel, data.message, data.username, getTimeago(data.timestamp), chat_id, false, data.message_id);
                setNewMessageIndicator("#" + data.from_channel + "-div");
            } else {
                createChannelWindow(data.from_channel);
                handleEmployeeToEmployeeMessage(data);
            }
        }
    }


    /*
     * Takes an employee message directed at a customer and appends it to the right window
     */
    function handleEmployeeChannelMessage(data) {
        if (doesElementExist(data.to_channel)) {
            // Pop up the relevant chat window
            // In the future, can just make it to where the chat client flashes a color or something,
            //$("#" + data.to_channel + "-chat-body").show();
            var chat_id = "#" + data.to_channel + "-chat";
            var need_to_pull = false;
            if (data.from_channel == channel_id) {
                    need_to_pull = true;
            }
            appendChannelMessage(data.to_channel, data.message, data.username, getTimeago(data.timestamp), chat_id, need_to_pull, data.message_id);
            if (!need_to_pull) setNewMessageIndicator("#" + data.to_channel + "-div");
        } else {
            createChannelWindow(data.to_channel);
            handleEmployeeChannelMessage(data);
        }
    }

    /*
     * Handles a customer message
     * Appends the message to the right window and it notifies the user that they have a new message
     */
    function handleCustomerChannelMessage(data) {
        // This is a message from the customer
        // Need to check if we already have a chat window for this customer
        
        if (doesElementExist(data.from_channel)) {
            var chat_body = "#" + data.from_channel + "-chat";
            var chat_div = "#" + data.from_channel + "-div";
            appendChannelMessage(data.from_channel, data.message, data.username, getTimeago(data.timestamp), chat_body, false, data.message_id);
            if (data.page_url) {
                $(chat_div).find('.last-page-url').html('<a href="' + data.page_url + '" target="_blank">' + data.page_url + '</a>');
            }
            setNewMessageIndicator(chat_div);
        } else {
            createChannelWindow(data.from_channel);
            handleCustomerChannelMessage(data);
        }

    }

    /*
     * Handle a user being disconnected from their channel
     * We want to remove their div and also change their status to offline
     */
    function handleChannelDisconnect(data) {
        // We have a customer who disconnected
        // Need to hide the div from #main_chat_body
        var chan_id = data.channel_id;
        var disconnect_timeout = setTimeout(function() {
            $('#'+ chan_id).find('.chat-status').removeClass('checking').addClass('offline');
            $("#" + chan_id + "-div").hide();
            connected_clients[chan_id].disconnect_timeout = null;
        }, 10000);
        
        connected_clients[chan_id].disconnect_timeout = disconnect_timeout;
    }
    
    
    /*
     * Sends a message to the specified channel
     * Note that in the customer js file, we append the message right back as part of the on success call
     * For employees, we always send them the message via channels since we iterate over the employees array
     */
    function sendMessage(msg, to_channel) {
        if (connectedToChannel) {
            var timestamp = new Date().getTime();

            $.ajax({
                type: "POST",
                url: sendMessageURL,
                data: {"to_channel": to_channel, "msg": msg, "from_channel": channel_id, "timestamp": timestamp, "channel_token": channel_token},
                crossDomain: true,
                dataType: "json",
                success: function() {
                    // No need to handle success since we get the message right back
                },
                error: function (msg) {
                    $("#" + to_channel + "-chat").append("<li><p>There was an error sending the message.</p></li>");
                }

            });

        } else {
            connectToEmployeeChannel();
        }

    }

    /*
     * POSTs to PINES to get a channel token
     * After getting token, it sets the Channel Info which will trigger the actual connection of the channel
     */
    function connectToEmployeeChannel(force_token) {
        chat_status.removeClass('offline').addClass('checking');
        chat_status_text.html('Connecting');
        var gae_host = getTokenURL;

        $.ajax({
            type: 'POST',
            url: gae_host,
            data: {"force_new_token": force_token},
            dataType: "json",
            success: function (msg) {
                setChannelInfo(msg.channel_id, msg.channel_token, msg.username, msg.guid);
            },
            error: function () {
                // Need to let them know that they couldn't connect
                alert("Could not get a token. Please refresh your page");
            }
        });

    }

    
    /**
     * Used to add customer info to the object connected_clients
     * 
     * We add information so that we can lookup the customer info without having to navigate the DOM
     * We use the channel_id as the key that holds the customer object
     *
     */
    function addToConnectedClients(data) {
        if (!connected_clients[data.channel_id]) {
            connected_clients[data.channel_id] = {username: data.username, token: data.token, online_status: false, city: data.city, region: data.region,
                header: 'header_div', chat_body: 'chat_body_div', online_icon: 'online_icon_status', checking_online: false, "username_link" : data.username_link, "page_url": data.page_url};
        }
    }
    
    /*
     * This adds a new chat window for the connected customer
     * Determines whether to append it to the employees or customer list
     */
    function addNewChannelCustomer(data, is_employee) {
        // Need to check if a customer or an employee connected
        // If it was a customer, append the customers list else append the employees' list
        var client = connected_clients[data.channel_id];
        
        if (Boolean(client) && Boolean(client.disconnect_timeout)) {
            clearTimeout(connected_clients[data.channel_id].disconnect_timeout);
            connected_clients[data.channel_id].disconnect_timeout = null;
            return;
        }
        
        addToConnectedClients(data);
        
        if (data.channel_id == channel_id) {
            return;
        }

        var chat_div = data.channel_id + "-div";
        // Can replace this code with a check to see if the channel id is the the connect_clients object
        // Since we are going to be updating the Online Indicator instead of appending a message, we can just keep track of clients in the object
        // That way, we don't need to check if the element exists, because it will be in the var
        if (doesElementExist(chat_div)) {
            $("#"+chat_div).show();
            $("#"+data.channel_id).find('.chat-status').removeClass('offline checking');

            return;
        }
        

        if (is_employee) {
            var newEmployeeChannelHTML = '<div class="list-group employee-channel-clients-list channel-client-list" id="' + data.channel_id + '-div" data-channelid="' + data.channel_id + '"><a class="list-group-item channel-name-div">' +
                        '<strong class="chat-client-div-username">' + data.username + '</strong>' +
                        '<input type="text" style="display:none;" value="' + data.channel_id + '"/>' +
                        '</a></div>';
            // Append it to the employee's div
            $("#employee-chat-clients").append(newEmployeeChannelHTML);
        } else {
            var distinguished = 'nondistinguished-chat-user';
            if (data.distinguished) {
                distinguished = 'distinguished-chat-user';
            }
            
            var username_link = data.username;
            if (Boolean(data.username_link)) {
                // Need to check if we actually have a ?, &, or = in the url
                // If we don't, then just use what it has
                var regex = /[\?\&\=]/;
                if (regex.test(data.username_link)) {
                    username_link = '<a href="' + data.username_link + '" target="_blank">' + data.username + '</a>';
                }               
            }
            
            connected_clients[data.channel_id].username_link = username_link;
            
            var div_info = '<span class="chat-client-div-info badge"></span>';
            if (data.city) {
                div_info = '<span class="chat-client-div-info badge">' + data.city.capitalize() + ", " + data.region.capitalize() + "</span>";
            }
            
            var newChannelCustomerHTML = '<div class="list-group customer-channel-clients-list channel-client-list ' + distinguished + '" id="' + data.channel_id + '-div" data-channelid="' + data.channel_id + '"><div class="list-group-item channel-name-div">' +
                        '<strong class="chat-client-div-username">' + data.username + '</strong>' +
                        div_info +
                        '<p class="last-chat-message chat-ellipsis"></p>' +
                        '<p class="last-page-url chat-ellipsis"><a href="' + data.page_url + '" target="_blank">' + data.page_url + '</a>' +'</p>' +
                        '<input type="text" style="display:none;" value="' + data.channel_id + '"/>' +
                        '</div></div>';
            // Append it to the customer's div
            $("#customer-chat-clients").append(newChannelCustomerHTML);
        }
    }
    
    
    /*
     * Appends the Channel Message to it's respective chat window
     */
    function appendChannelMessage(channel, message, username, timestamp, chat_id, is_employee, message_id) {
        if (is_employee) {
            var chat_message = '<li class="chat-message"><span class="chat-img pull-right"><img src="' + customer_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
                '<div class="chat-message-right clearfix"><div class="header">' +
                  '<small class="text-muted">' +
                  '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small>'+
                  '<strong class="pull-right primary-font">' + username + '</strong>' +
                '</div><p>' + message + '</p></div></li>';
        } else {
        //playNotification();
        notifyMe(username, message, username);
        //alertTabTitle("A New Message");
        var chat_message = '<li class="chat-message"><span class="chat-img pull-left"><img src="' + employee_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
                                '<div class="chat-message-left clearfix"><div class="header"><strong class="primary-font">' + username + '</strong> ' +
                                '<small class="pull-right text-muted">' +
                                '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small></div>' +
                                    '<p>' + message + '</p></div></li>';
        }
        $(chat_id).append(chat_message);
        $("#" + channel + "-chat-body").scrollTop($(chat_id).height());
        $('abbr.timeago').timeago();
        addToChatHistory(message_id, channel, username, timestamp, message);
        $("#" + channel + "-div").find('.last-chat-message').html(message);
    }


    /*
     * Creates a chat window for the customer
     * 
     * @param customer_channel The customer's channel
     * 
     */
    function createChannelWindow(customer_channel) {

        // If we already have the client window, don't open another one,
        // Maybe alert the current one
        if (document.contains(document.getElementById(customer_channel))) {
            // We should not have the element with this is yet
            return;
        }

        var newChannelClientHTML = '<div style="display: none;" class="container chat-window chat-container" id="' + customer_channel + '"><div class="row chat-header" data-channelid="' + customer_channel +'">' +
                '<span class="chat-status"></span><span class="username">' + connected_clients[customer_channel].username_link + '</span><div class="btn-group pull-right">' +
                '<button type="button" class="btn btn-small min-max-btn" data-channelid="' + customer_channel + '"><i class="icon-chevron-down"></i></button>' +
                '<button type="button" class="btn btn-small close-chat-btn" data-channelid="' + customer_channel + '"><i class="icon-remove"></i></button>' +
                '<button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown">' +
                '<i class="icon-wrench"></i></button><ul class="dropdown-menu slidedown"><li>' +
                '<a href="#" class="do-online-check" data-channelid="' + customer_channel + '"><i class="icon-chevron-right"></i> Online Check</a></li></ul>' +
                '</div></div><div class="row chat-body" id="' + customer_channel + '-chat-body">' +
                '<ul class="chat-window-messages" id="' + customer_channel + '-chat"></ul></div><div class="row chat-footer">' +
                '<div class="input-append chat-message-input"><input id="' + customer_channel + '-btn-input" data-channelid="' + customer_channel + '" type="text" class="form-control chat-input-box" placeholder="Type Message Here...">' +
                '<span class="input-group-btn"><button  type="button" class="btn btn-warning chat-button send-chat-btn" data-channelid="' + customer_channel + '">Send</button>' +
                '</span></div></div></div>';

        $('#additional_clients').append(newChannelClientHTML);
        //openClients.push(customer_channel);
        realignChatWindows();
    }

    /*
     * A jQuery listener for when the employee presses the Enter key for sending a message
     * 
     * Take the value of the input, make sure it's not empty, send the message, and clear the input
     */
    $("#additional_clients").on("keyup", ".chat-input-box", function(e) {
        if (e.which == 13) {
            var chan_id = $(this).attr('data-channelid');
            var msg = $(this).val();
            if (msg) {
                sendMessage(msg, chan_id);
                $(this).val("");
            }
        }

    });


    /*
     * A jQuery listener for when an employee clicks the send button
     * 
     * We grab the value, check to make sure it isn't empty and then send it
     * Finally, we clear the text
     */
    $("#additional_clients").on("click", ".chat-button", function(e) {
        var chan_id = $(this).attr('data-channelid');
        var chan_input = "#" + chan_id + "-btn-input";
        var msg = $(chan_input).val();
        if (msg) {
            sendMessage(msg, chan_id);
            $(chan_input).val("");
        }

    });


    /*
     * A jQuery listener for when an employee clicks on a channel user's name in the main chat client
     * 
     * This will either show the intended chat client or create a new channel window
     */
    main_chat_body.on("click", ".channel-client-list", function() {
        var chan_id = $(this).attr("data-channelid");
        
        if (doesElementExist(chan_id)) {
            // Need to check if we have this element in the openClients array
            if (openClients.indexOf(chan_id) === -1) {
                // We need to add it 
                openClients.push(chan_id);
            }
        } else {
            createChannelWindow(chan_id);
            openClients.push(chan_id);
        }
        realignChatWindows();
    });


    /*
     * A jQuery listener for when an employee wants to minimize the main chat window
     * 
     * Changes the icon for the button depending on the state
     * Remember to update the header with the relevant class as well
     */
    minMainChatBtn.click(function() {

        if (main_chat_body.hasClass("chat-minimized")) {
            main_chat_body.removeClass("chat-minimized").show();
            $(this).html('<i class="icon-chevron-down"></i>');
            localStorage.setItem('chatminimized', "no");
        } else {
            main_chat_body.addClass("chat-minimized").hide();
            $(this).html('<i class="icon-chevron-up"></i>');
            localStorage.setItem('chatminimized', "yes");
        }
    });


    /*
     * A jQuery listener for when an employee wants to minimize/maximize an individual chat client
     */
    $("#additional_clients").on("click", ".min-max-btn", function() {
        // Need to make it switch just like before from min-to-max and vice-versa
        // Give class to parent div so that we can max chat when we click on the chat heading

        var chat_id = "#" + $(this).attr('data-channelid') + "-chat-body";
        if ($(chat_id).hasClass("chat-minimized")) {
            $(chat_id).show();
            $(this).parent().parent().removeClass("chat-minimized");
            $(chat_id).removeClass("chat-minimized");
            $(this).html("<i class='icon-chevron-down'></i>");
        } else {
            $(chat_id).hide();
            $(this).parent().parent().addClass("chat-minimized");
            $(chat_id).addClass("chat-minimized");
            $(this).html("<i class='icon-chevron-up'></i>");
        }
    });


    /*
     * A jQuery listener for when an employee clicks on the close chat button for an individual chat client
     * 
     * All it does is hide the window
     *      - Need to make it so that we keep track of the hidden divs because of the margin issues
     *
     */
    $("#additional_clients").on("click", ".close-chat-btn", function(e) {
        var chan_id = $(this).attr("data-channelid");
        var channel_location = openClients.indexOf(chan_id);
        openClients.splice(channel_location, 1);
        $("#" + chan_id).hide();
        // Need to reshuffle the other chat divs so that they have the correct margin
        realignChatWindows();

    });


    /*
     * A jQuery listener for when an employee clicks on the main header of an individual chat client
     * When they click the header, the chat window will maximize itself to show the chat body
     * It will also change the icon and add a chat-minimized class to the header for checking
     */
    $("#additional_clients").on("click", ".chat-header", function(e) {
        if (e.target != this) {
            return;
        }
        // Probably going to need to check to make sure that it's still grabbing the correct parent
        // We really only need the data-channelid and then we can get the body and add the correct class
        if ($(this).hasClass("chat-minimized")) {
            $(this).find('.min-max-btn').html('<i class="icon-chevron-down"></i>');
            $(this).parent().find('.chat-body').show().removeClass("chat-minimized");
            $(this).removeClass("chat-minimized");
        }
    });

    // Listener for when employee clicks on header
    main_chat_header.on("click", function(e) {
        if (e.target != this) {
            return;
        }
        if (main_chat_body.hasClass("chat-minimized")) {
            minMainChatBtn.html('<i class="icon-chevron-down"></i>');
            main_chat_body.removeClass("chat-minimized").show();
            localStorage.setItem("chatminimized", "no");
        }
    });

    // Listener for clicking the online check button
    $("#additional_clients").on("click", ".do-online-check", function() {

        var customer_channel = $(this).attr("data-channelid");
        sendOnlineCheck(customer_channel);

    });

    /*
     * A jQuery listener for when the employee clicks on the enable notifications button
     * 
     * This is for use with Google Chrome
     *      - Chrome requires that in order to ask for notification permission,
     *          it must originate from a user interaction
     *      - Mozilla Firefox does not have this issue
     * 
     * It is passed the event parameter to stopPropagation of any other listener that may be called
     */
    $("#enableNotifications").click(function(e) {
        if (e.target != this) {
            return;
        }
        if (Notification.permission !== 'denied') {
            Notification.requestPermission(function (permission) {
                // Whatever the user answers, we make sure we store the information
                if(!('permission' in Notification)) {
                    Notification.permission = permission;
                }
            });
        }
    });
    
    if (localStorage.getItem('chatminimized') == "yes") {
        minMainChatBtn.click();
    }
    $.timeago.settings.strings.seconds = "seconds";
    $("abbr.timeago").timeago();
    connectToEmployeeChannel();

});