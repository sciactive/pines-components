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
    pingAllCustomersURL = $("#ping_all_customers_url").attr('data-url');
    refreshOnlineUsersURL = $("#refresh_online_users_url").attr('data-url');
    getMessagesURL = $("#get_customer_messages_url").attr('data-url');
    getUsersPlusMessagesURL = $("#get_users_and_messages_url").attr('data-url');
    minMainChatBtn = $("#min-main-chat-btn");
    additionalClients = $("#additional_clients");
    
    // Setting this to false so that when we get the chat_history, we won't get notifications all at once
    // This will allow for new messages to have notifications, but not chat history
    displayNotifications = false;
    
    disconnect_timeouts = {};
    
    customer_chat_histories = {};
    
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
        getUsersPlusMessages();
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
            
            // Consolidate both types of connections into one
            if (data.type == "connection") {
                addChannelUser(data.user);
                createChannelWindow(data.user.channel_id);
                // We should also do an ajax call to get their chat history
                getCustomerChatHistory(data.user.channel_id);
                
            } else if (data.type == "disconnection") {
                handleChannelDisconnect(data.channel_id);
                
            } else if (data.type == "message") {
                addChannelMessage(data.message);

            } else if (data.type == "online_check") {
                handleOnlineTest(data);
                
            } else if (data.type == "customer_check") {
                handleCustomerReconnect(data);
                
            } else if (data.type == "customer_update") {
                updateUserProperties(data.user);
                
            } else if (data.type == "connection_update") {
                handleConnectionUpdate(data.connected_clients, data.disconnected_clients);
            }
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
        connectToEmployeeChannel("true");
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
            var margin_to_right;
            var margin_for_right_percent;
            if (i==0) {
                // Need to just add it to the regular chat div width
                margin_to_right = used_chat_percent + (i * regular_chat_div_width * 100) + 2;
                margin_for_right_percent = margin_to_right.toString() + "%";
            } else {
                var first_chan_width = "#" + openClients[0];
                var real_div_width = $(first_chan_width).width() / $(window).width();
                margin_to_right = used_chat_percent + (i * real_div_width * 100) + (i + 1 + regular_chat_div_margin_extra);
                margin_for_right_percent = margin_to_right.toString() + "%";
            }
            
            $(chan_id).css('right', margin_for_right_percent);
            $(chan_id).show();
            $(chan_id + "-chat-body").show();
        }
    }

    
    function handleConnectionUpdate(connections, disconnections) {
        var connect_length = connections.length;
        var disconnect_length = disconnections.length;
        for (var i=0; i < connect_length; i++) {
            var chan = connections[i];
            $("#" + chan + "-div").show();
            $('#'+ chan).find('.chat-status').removeClass('offline checking');
            
            if (typeof disconnect_timeouts[chan] === "undefined") continue;
            
            if (disconnect_timeouts[chan].length) {
                for (var timeout in disconnect_timeouts[chan]) {
                    clearTimeout(timeout);
                }
                disconnect_timeouts[chan] = [];
            }
        }
        
        for (var i=0; i < disconnect_length; i++) {
            var d = disconnections[i];
            $("#" + d + "-div").hide();
            $('#'+ chan).find('.chat-status').removeClass('checking').addClass('offline');
        }
    }
    
    function getCustomerChatHistory(customer_channel) {
        if (customer_channel === channel_id) return;
        if (customer_chat_histories[customer_channel] !== "undefined") {
            return;
        }
        $.ajax({
           type: 'GET',
           url: getMessagesURL,
           data: {"customer_channel": customer_channel, "channel_token": channel_token, "channel_id": channel_id},
           crossDomain: true,
           dataType: 'json',
           success: function (data) {
               if (data.status === 'success') {
                   customer_chat_histories[customer_channel] = true;
                   handleChatMessageHistory(data.messages);
               }
           },
           error: function () {
               // No need to handle error
           }
       });
    }
    
    /*
     * Handles a customer reconnecting to the channel
     * We want to make sure the name is showing in the client list
     * We also want to make sure that the status is set to online
     * 
     */
    function handleCustomerReconnect(data) {
        // We have a customer who reconnected
        var chat_div = "#" + data.channel_id + "-div";
        $(chat_div).show();
        $('#'+data.channel_id).find('.chat-status').removeClass('offline checking');
        
        if (typeof disconnect_timeouts[data.channel_id] === "undefined") {
            disconnect_timeouts[data.channel_id] = [];
            return;
        }
        
        if (disconnect_timeouts[data.channel_id].length) {
            for (var t in disconnect_timeouts[data.channel_id]) {
                clearTimeout(t);
            }
            disconnect_timeouts[data.channel_id] = [];
        }
        if (typeof data.page_url !== 'undefined') updateCustomerURL(chat_div, data.page_url);
    }

    /*
     * Pings the customer's channel to see if they are still online
     */
    function sendOnlineCheck(customer_channel) {
        $('#'+customer_channel).find('.chat-status').removeClass('offline').addClass('checking');
        
        if (typeof disconnect_timeouts[customer_channel] === "undefined") {
            disconnect_timeouts[customer_channel] = [];
        }
        
        var online_timeout = setTimeout(function() {
                  if (disconnect_timeouts[customer_channel].length) {
                      $('#'+customer_channel).find('.chat-status').removeClass('checking').addClass('offline');
                      disconnect_timeouts[customer_channel] = [];
                  } else {
                  }
              }, 5000);
        disconnect_timeouts[customer_channel].push(online_timeout);
        

        $.ajax({
           type: 'POST',
           url: onlineCheckURL,
           data: {"customer_channel": customer_channel, "channel_token": channel_token, "channel_id": channel_id},
           crossDomain: true,
           dataType: 'json',
           success: function () {
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
            data: {"channel_token": channel_token, "channel_id": channel_id},
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
    function handleOnlineUsersList(users) {
        var user_length = users.length;
        if (!user_length) {
            return;
        }
        
        for (var i=0; i<user_length; i++) {
            addChannelUser(users[i]);
        }
    }
    
    /*
     * Get's the timeago string with the user's own timezone
     */
    function getTimeago(time) {
        return new Date(Number(time)).toISOString();
    }

    function setNewMessageIndicator(chat_div) {
        if (!displayNotifications) return;
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
     * Handle a user being disconnected from their channel
     * We want to remove their div and also change their status to offline
     */
    function handleChannelDisconnect(channel_id) {
        // We have a customer who disconnected
        // Need to hide the div from #main_chat_body
        if (typeof disconnect_timeouts[channel_id] === "undefined") {
            disconnect_timeouts[channel_id] = [];
        }
        var disconnect_timeout = setTimeout(function() {
            $('#'+ channel_id).find('.chat-status').removeClass('checking').addClass('offline');
            $("#" + channel_id + "-div").hide();
            disconnect_timeouts[channel_id] = [];
        }, 10000);
        
        disconnect_timeouts[channel_id].push(disconnect_timeout);
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
                data: {"to_channel": to_channel, "msg": msg, "from_channel": channel_id, "timestamp": timestamp, "channel_token": channel_token, "channel_id": channel_id},
                crossDomain: true,
                dataType: "json",
                success: function(data) {
                    // No need to handle success since we get the message right back
                    // Might want to append the message anyway after success
                    // The addMessage function will check out if we already have received this message
                },
                error: function() {
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
    
    function handleChatMessageHistory(messages) {
        var count = messages.length;
        if (!count) return;
        displayNotifications = false;
        for (var i=0; i<count; i++) {
            var message = messages[i];
            if (typeof chatHistory[message.message_id] === 'undefined' && $("#" + message.channel_id).length) {
                addChannelMessage(message);
            }
        }
        displayNotifications = true;
    }
    
    function getUsersPlusMessages() {
        $.ajax({
            type: 'GET',
            url: getUsersPlusMessagesURL,
            data: {"token": channel_token, "channel_id": channel_id},
            dataType: "json",
            crossDomain: true,
            success: function (data) {
                // We get back {"status": "success", "users": [users]}
                if (data.status == "success") {
                    // users is structured as [{'user': {}, 'messages': []}, {'user': {}, 'messages': []}]
                    var u_length = data.users.length;
                    for (var i = 0; i<u_length; i++) {
                        addChannelUser(data.users[i].user);
                        createChannelWindow(data.users[i].user.channel_id);
                        handleChatMessageHistory(data.users[i].messages);
                    }
                }
            },
            error: function () {
                // Need to let them know that they couldn't connect
            }
        });
    }
    
    function sendCheckToAllCustomers() {
        for (var customer in connected_clients) {
            disconnect_timeouts[customer.channel_id] = [];
        }
        
        setTimeout(function() {
            for (var customer in connected_clients) {
                // If you haven't cleared your disconnects, then set the person offline'
                if (disconnect_timeouts[customer.channel_id].length) {
                    setCustomerOffline(customer);
                }
            }
        }, 8000);
        
        $.ajax({
            type: 'POST',
            url: pingAllCustomersURL,
            data: {"channel_token": channel_token, "channel_id": channel_id},
            dataType: "json",
            crossDomain: true,
            success: function () {
            },
            error: function () {
                // Need to let them know that they couldn't connect
            }
        });

    }
    
    function setCustomerOffline(channel_id) {
        $(connected_clients[channel_id]['chat_div']).hide();
        $(connected_clients[channel_id]['chat_window']).find('.chat-status').removeClass('checking').addClass('offline');
    }
    
    function refreshOnlineList() {
        $.ajax({
            type: 'GET',
            url: refreshOnlineUsersURL,
            data: {"channel_token": channel_token, "channel_id": channel_id},
            dataType: "json",
            crossDomain: true,
            success: function (data) {
                if (data.status == 'success') {
                    handleOnlineUsersList(data.users);
                }
            },
            error: function () {
                // Need to let them know that they couldn't connect
            }
        });
    }
    
    
    function handleUserList(online_users) {
        // Get an array of objects
        
        var users_length = online_users.length;
        
        if (!users_length) return;
        
        for (var i = 0; i < users_length; i++) {
            addChannelUser(online_users[i]);
            createChannelWindow(online_users[i]);
        }
        realignChatWindows();
    }
    
    function updateUserProperties(user) {
        if (typeof user === "undefined") return;
        var chat_user = connected_clients[user.channel_id];
        if (Object.keys(chat_user).length === 0) {
            connected_clients[user.channel_id] = {};
        }
        for (var prop in user) {
            if (user.hasOwnProperty(prop)) {
                connected_clients[user.channel_id][prop] = user[prop];
            }
        }
        
        var div_to_update = '#' + user.channel_id + '-div';
        updateCustomerLocation(div_to_update, user.city, user.region);
    }
    
    function addChannelUser(user) {
        
        if (user.channel_id == channel_id) {
            return;
        }
        
        var timeouts = disconnect_timeouts[user.channel_id];
        
        if (typeof timeouts !== 'undefined' && timeouts.length) {
            for (var timeout in disconnect_timeouts[user.channel_id]) {
                clearTimeout(timeout);
            }
            disconnect_timeouts[user.channel_id] = [];
        }
        
        connected_clients[user.channel_id] = {};
        for (var prop in user) {
            if (user.hasOwnProperty(prop)) {
                connected_clients[user.channel_id][prop] = user[prop];
            }
        }
        
        // Later on, the ChatUser object will have a property chat_div that has this element
        var chat_div = user.channel_id + "-div";
        // Can replace this code with a check to see if the channel id is the the connect_clients object
        // Since we are going to be updating the Online Indicator instead of appending a message, we can just keep track of clients in the object
        // That way, we don't need to check if the element exists, because it will be in the var
        if ($("#" + chat_div).length) {
            $("#" + chat_div).show();
            $("#" + user.channel_id).find('.chat-status').removeClass('offline checking');
            return;
        }
        
        
        // This will be a function on the chatUser object
        // chatUser.appendToClientList()
        if (Boolean(user.is_employee)) {
            var newEmployeeChannelHTML = '<div class="list-group employee-channel-clients-list channel-client-list" id="' + user.channel_id + '-div" data-channelid="' + user.channel_id + '"><a class="list-group-item channel-name-div">' +
                        '<strong class="chat-client-div-username">' + user.username + '</strong>' +
                        '<input type="text" style="display:none;" value="' + user.channel_id + '"/>' +
                        '</a></div>';
            // Append it to the employee's div
            $("#employee-chat-clients").append(newEmployeeChannelHTML);
        } else {
            var distinguished = 'nondistinguished-chat-user';
            if (Boolean(user.distinguished)) {
                distinguished = 'distinguished-chat-user';
            }
            
            var username_link = user.username;
            if (Boolean(user.username_link)) {
                // Need to check if we actually have a ?, &, or = in the url
                // If we don't, then just use what it has
                var regex = /[\?\&\=]/;
                if (regex.test(user.username_link)) {
                    username_link = '<a href="' + user.username_link + '" target="_blank">' + user.username + '</a>';
                }               
            }
            
            connected_clients[user.channel_id].username_link = username_link;
            
            var div_info = '<span class="chat-client-div-info badge"></span>';
            if (user.city) {
                div_info = '<span class="chat-client-div-info badge">' + user.city.capitalize() + ", " + user.region.capitalize() + "</span>";
            }
            
            var newChannelCustomerHTML = '<div class="list-group customer-channel-clients-list channel-client-list ' + distinguished + '" id="' + user.channel_id + '-div" data-channelid="' + user.channel_id + '"><div class="list-group-item channel-name-div">' +
                        '<strong class="chat-client-div-username">' + user.username + '</strong>' +
                        div_info +
                        '<p class="last-chat-message chat-ellipsis"></p>' +
                        '<p class="last-page-url chat-ellipsis"><a href="' + user.page_url + '" target="_blank">' + user.page_url + '</a>' +'</p>' +
                        '<input type="text" style="display:none;" value="' + user.channel_id + '"/>' +
                        '</div></div>';
            // Append it to the customer's div
            $("#customer-chat-clients").append(newChannelCustomerHTML);
        }
    }
    
    
    function addChannelMessage(message) {
        // We just want to get the msg, the sender's channel_id, the receipent's channel_id, timestamp
        
        // Get the sender
        var chat_div;
        var chat_html;
        var chat_body;
        var ourselves = message.from_channel === channel_id;
        var div_to_update;
        var timestamp = getTimeago(message.timestamp);
        var username = message.from_username;
        
        if (Boolean(message.channel_id)) {
            // This is a message that is for a customer
            chat_div = "#" + message.channel_id + "-chat";
            chat_body = "#" + message.channel_id + "-chat-body";
            div_to_update = "#" + message.channel_id + "-div";
        } else if (ourselves) {
            
            if (Boolean(message.employee_to_employee)) {
                chat_div = "#" + message.to_channel + "-chat";
                chat_body = "#" + message.to_channel + "-chat-body";
                div_to_update = "#" + message.to_channel + "-div";
            } else {
                chat_div = "#" + message.channel_id + "-chat";
                chat_body = "#" + message.channel_id + "-chat-body";
                div_to_update = "#" + message.channel_id + "-div";
            }
            // This is a message from us and most likely to another 
            
        } else {
            chat_div = "#" + message.from_channel + "-chat";
            chat_body = "#" + message.from_channel + "-chat-body";
            div_to_update = "#" + message.from_channel + "-div";
        }
        
        
        if (ourselves) {
            chat_html = '<li class="chat-message"><span class="chat-img pull-right"><img src="' + customer_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
                '<div class="chat-message-right clearfix"><div class="header">' +
                  '<small class="text-muted">' +
                  '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small>'+
                  '<strong class="pull-right primary-font">' + username + '</strong>' +
                '</div><p>' + message.message + '</p></div></li>';
        } else {
            chat_html = '<li class="chat-message"><span class="chat-img pull-left"><img src="' + employee_pic_url + '" alt="User Avatar" class="img-circle"/></span>' +
                                '<div class="chat-message-left clearfix"><div class="header"><strong class="primary-font">' + username + '</strong> ' +
                                '<small class="pull-right text-muted">' +
                                '<i class="icon-time"></i><abbr class="timeago chat-timeago" title="' + timestamp + '">' + timestamp + '</abbr></small></div>' +
                                    '<p>' + message.message + '</p></div></li>';
            updateCustomerURL(div_to_update, message.page_url);
            notifyMe(username, message.message, username);
            setNewMessageIndicator(div_to_update);
        }
        
        $(chat_div).append(chat_html);
        $(chat_body).scrollTop($(chat_div).height());
        $(div_to_update).find('.last-chat-message').html(message);
        chatHistory[message.message_id] = message;
        
        $('abbr.timeago').timeago();
        

    }
    
    function updateCustomerURL(customer_div, url) {
        if (typeof url === 'undefined' || !Boolean(url)) return;
        $(customer_div).find('.last-page-url').html('<a href="'+ url + '" target="_blank">' + url + '</a>');
    }
    
    function updateCustomerLocation(customer_div, city, region) {
        $(customer_div).find('.badge').html(city.capitalize() + ', ' + region.capitalize());
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
        if ($("#" + customer_channel).length || customer_channel == channel_id) {
            // We should not have the element with this is yet
            return;
        }
        
        var user = connected_clients[customer_channel];

        var newChannelClientHTML = '<div style="display: none;" class="container chat-window chat-container" id="' + customer_channel + '"><div class="row chat-header" data-channelid="' + customer_channel +'">' +
                '<span class="chat-status"></span><span class="chat-username">' + user.username_link + '</span><div class="btn-group pull-right">' +
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

        additionalClients.append(newChannelClientHTML);
        realignChatWindows();
    }

    /*
     * A jQuery listener for when the employee presses the Enter key for sending a message
     * 
     * Take the value of the input, make sure it's not empty, send the message, and clear the input
     */
    additionalClients.on("keyup", ".chat-input-box", function(e) {
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
    additionalClients.on("click", ".chat-button", function(e) {
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
        
        if ($("#" + chan_id).length) {
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
    additionalClients.on("click", ".min-max-btn", function() {
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
    additionalClients.on("click", ".close-chat-btn", function(e) {
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
    additionalClients.on("click", ".chat-header", function(e) {
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
    additionalClients.on("click", ".do-online-check", function() {

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
    
    $("#ping_all_customers").click(function(e) {
        if (e.target != this) {
            return;
        }
        sendCheckToAllCustomers();
    });
    
    $("#refresh_online_users_list").click(function(e) {
        if (e.target != this) {
            return;
        }
        refreshOnlineList();
    })
    
    if (localStorage.getItem('chatminimized') == "yes") {
        minMainChatBtn.click();
    }
    $.timeago.settings.strings.seconds = "seconds";
    $("abbr.timeago").timeago();
    connectToEmployeeChannel();

});