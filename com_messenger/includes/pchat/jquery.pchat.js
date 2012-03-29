/*
 * jQuery Pines Chat (pchat) Plugin 0.10.2dev
 *
 * http://pinesframework.org/pchat/
 * Copyright (c) 2012 Hunter Perrin
 *
 * Triple license under the GPL, LGPL, and MPL:
 *	  http://www.gnu.org/licenses/gpl.html
 *	  http://www.gnu.org/licenses/lgpl.html
 *	  http://www.mozilla.org/MPL/MPL-1.1.html
 */

// First we need to emulate localStorage if it doesn't exist.
// Taken from https://developer.mozilla.org/en/DOM/Storage
if (!window.localStorage) {
	Object.defineProperty(window, "localStorage", new (function () {
	var aKeys = [], oStorage = {};
	Object.defineProperty(oStorage, "getItem", {
		value: function (sKey) {return sKey ? this[sKey] : null;},
		writable: false,
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "key", {
		value: function (nKeyId) {return aKeys[nKeyId];},
		writable: false,
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "setItem", {
		value: function (sKey, sValue) {
		if(!sKey) {return;}
		document.cookie = escape(sKey) + "=" + escape(sValue) + "; path=/";
		},
		writable: false,
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "length", {
		get: function () {return aKeys.length;},
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "removeItem", {
		value: function (sKey) {
		if(!sKey) {return;}
		var sExpDate = new Date();
		sExpDate.setDate(sExpDate.getDate() - 1);
		document.cookie = escape(sKey) + "=; expires=" + sExpDate.toGMTString() + "; path=/";
		},
		writable: false,
		configurable: false,
		enumerable: false
	});
	this.get = function () {
		var iThisIndx;
		for (var sKey in oStorage) {
		iThisIndx = aKeys.indexOf(sKey);
		if (iThisIndx === -1) {oStorage.setItem(sKey, oStorage[sKey]);}
		else {aKeys.splice(iThisIndx, 1);}
		delete oStorage[sKey];
		}
		for (aKeys; aKeys.length > 0; aKeys.splice(0, 1)) {oStorage.removeItem(aKeys[0]);}
		for (var iCouple, iKey, iCouplId = 0, aCouples = document.cookie.split(/\s*;\s*/); iCouplId < aCouples.length; iCouplId++) {
		iCouple = aCouples[iCouplId].split(/\s*=\s*/);
		if (iCouple.length > 1) {
			oStorage[iKey = unescape(iCouple[0])] = unescape(iCouple[1]);
			aKeys.push(iKey);
		}
		}
		return oStorage;
	};
	this.configurable = false;
	this.enumerable = true;
	})());
}

(function($){
	$.fn.pchat = function(options) {
		// Build main options before element iteration.
		var opts = $.extend({}, $.fn.pchat.defaults, options);

		// Iterate and transform each matched element.
		var all_elements = this;
		all_elements.each(function(){
			var pchat = $(this),
				attaching_from_storage = true; // Remember if we're attaching from localStorage, in case of error.
			// Check for our required libraries.
			if (!Strophe || !JSON) return false;
			// Check for the pchat class. If it has it, we've already transformed this element.
			if (pchat.hasClass("ui-pchat")) return true;

			pchat.pchat_version = "0.10.2dev";

			// Add the pchat class.
			pchat.addClass("ui-pchat");
			// Import the options.
			pchat.extend(pchat, opts);

			// Add widget classes.
			if (pchat.pchat_widget_box)
				pchat.addClass("ui-pchat-widget ui-widget ui-widget-content ui-corner-all");

			// Set up the user interface.
			if (pchat.pchat_title)
				$('<div class="ui-pchat-title ui-widget-header ui-corner-all"></div>').html(pchat.pchat_title).appendTo(pchat);

			var roster_elem = $('<div class="ui-pchat-roster ui-widget-content ui-corner-all"></div>').appendTo(pchat);

			var presence_text = {
				"working": '<i class="'+pchat.pchat_presence_icons.working+'"></i>Updating Status...',
				"available": '<i class="'+pchat.pchat_presence_icons.available+'"></i>Available',
				"chat": '<i class="'+pchat.pchat_presence_icons.available+'"></i>Chatty',
				"away": '<i class="'+pchat.pchat_presence_icons.away+'"></i>Away',
				"xa": '<i class="'+pchat.pchat_presence_icons.away_extended+'"></i>Extended Away',
				"dnd": '<i class="'+pchat.pchat_presence_icons.busy+'"></i>Busy',
				"offline": '<i class="'+pchat.pchat_presence_icons.offline+'"></i>Offline',
				"disconnected": '<i class="'+pchat.pchat_presence_icons.offline+'"></i>Not Connected'
			};
			var presence_elem = $('<div class="ui-pchat-presence-chooser ui-helper-clearfix"></div>').appendTo(pchat);
			// Presence state dropdown:
			presence_elem.append($('<div class="ui-pchat-presence-menu btn-group dropup"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><span class="ui-pchat-presence-current">'+presence_text.disconnected+'</span>&nbsp;<span class="caret"></span></a><ul class="dropdown-menu"></ul></div>'));
			var presence_current = presence_elem.find(".ui-pchat-presence-current");
			var presence_menu = presence_elem.find(".dropdown-menu");
			var login_button = $('<li><a href="javascript:void(0);">Login</a></li>').on("click", "a", function(){
				pchat.pchat_connect();
			});
			var logout_button = $('<li><a href="javascript:void(0);">Logout</a></li>').on("click", "a", function(){
				pchat.pchat_disconnect();
			});
			presence_menu
			.append($('<li><a href="javascript:void(0);">'+presence_text.available+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("available", presence_status.val());
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.chat+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("chat", presence_status.val());
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.away+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("away", presence_status.val());
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.xa+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("xa", presence_status.val());
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.dnd+'</li>').on("click", "a", function(){
				pchat.pchat_set_presence("dnd", presence_status.val());
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.offline+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("offline", presence_status.val());
			}))
			.append($('<li class="divider"></li>'))
			.append(login_button)
			.append(logout_button);
			// Status input:
			presence_elem.append($('<label class="ui-pchat-status-container">Status:<input type="text" size="15" class="ui-pchat-status" /></label>'));
			var presence_status = presence_elem.find(".ui-pchat-status");
			presence_status.change(function(){
				pchat.pchat_set_presence(localStorage.getItem("pchat-presence"), presence_status.val());
			}).keypress(function(e){
				if (e.keyCode == 13)
					$(this).change();
			});
			// The log function only does anything if the log is enabled.
			var log;
			if (pchat.pchat_show_log) {
				var log_elem = $('<div class="ui-pchat-log"></div>').appendTo(pchat);
				log = function(msg, escape){
					log_elem.append('<br />').append(escape === false ? msg : document.createTextNode(msg)).scrollTop(999999);
				};
			} else
				log = function(){};

			// Chat code.
			Strophe.addNamespace('TIME', "urn:xmpp:time");
			var connection = null;
			var save_rid = function(){
				log('Saved RID.');
				// Save the new RID for reattachment.
				localStorage.setItem("pchat-rid", connection.rid);
				return true;
			};
			// Save the RID when the page is unloading.
			window.onunload = save_rid;
			var handlers = {
				onConnect: function(status){
					switch (status) {
						case Strophe.Status.ERROR:
							log('An error occurred.');
							break;
						case Strophe.Status.CONNECTING:
							log('Connecting.');
							login_button.addClass("disabled");
							break;
						case Strophe.Status.CONNFAIL:
							log('Failed to connect.');
							login_button.removeClass("disabled");
							break;
						case Strophe.Status.AUTHENTICATING:
							log('Authenticating.');
							break;
						case Strophe.Status.AUTHFAIL:
							log('Failed to authenticate.');
							login_button.removeClass("disabled");
							break;
						case Strophe.Status.ATTACHED:
							log('Attached.');
						case Strophe.Status.CONNECTED:
							if (status == Strophe.Status.CONNECTED)
								log('Connected.');
							login_button.hide().removeClass("disabled");
							logout_button.show().removeClass("disabled");
							localStorage.setItem("pchat-jid", connection.jid);
							localStorage.setItem("pchat-sid", connection.sid);
							localStorage.setItem("pchat-rid", connection.rid);
							log('ECHOBOT: Send a message to ' + connection.jid + ' to talk to me.');

							if (status == Strophe.Status.CONNECTED) {
								// This is the initial connection, so we need to request roster and send initial presence.
								connection.roster.get(function(){
									// Since this is initial sign-on, send initial presence.
									connection.send($pres().tree());
									presence_current.html(presence_text.available);
								});
							} else {
								// This is an attached session, so request roster changes using the stored roster.
								var roster = localStorage.getItem("pchat-roster");
								var ver = localStorage.getItem("pchat-rosterver");
								var presence = localStorage.getItem("pchat-presence");
								var pres_stat = localStorage.getItem("pchat-presence-status");
								presence_current.html(presence_text[presence]);
								presence_status.val(pres_stat);
								if (roster) {
									log("Loading Saved Roster: "+roster);
									roster = JSON.parse(roster);
									ver = JSON.parse(ver);
									// Put the recovered roster back into the roster plugin.
									connection.roster.items = roster;
									connection.roster.ver = ver;
									// Call the roster handler.
									handlers.onRoster(roster);
								}
								// Get roster changes, if supported.
								if (connection.roster.supportVersioning())
									connection.roster.get(null, ver, roster);
							}
							break;
						case Strophe.Status.DISCONNECTING:
							log('Disconnecting.');
							logout_button.addClass("disabled");
							break;
						case Strophe.Status.DISCONNECTED:
							log('Disconnected.');
							logout_button.hide().removeClass("disabled");
							login_button.show().removeClass("disabled");
							presence_current.html(presence_text.disconnected);
							if (localStorage.getItem("pchat-sid")) {
								localStorage.removeItem("pchat-jid");
								localStorage.removeItem("pchat-sid");
								localStorage.removeItem("pchat-rid");
								localStorage.removeItem("pchat-roster");
								localStorage.removeItem("pchat-rosterver");
								// If we tried to attach from localStorage and failed, try starting a new connection.
								if (attaching_from_storage) {
									attaching_from_storage = false;
									pchat.pchat_connect();
								}
							}
							break;
					}
				},
				onRoster: function(roster, item){
					// Save the roster.
					localStorage.setItem("pchat-roster", JSON.stringify(connection.roster.items));
					localStorage.setItem("pchat-rosterver", JSON.stringify(connection.roster.ver));
					console.log("Roster Element:");
					console.log(roster);
					console.log("Presence Element:");
					console.log(item);
					$.each(roster, function(i, contact){
						var contact_elem = roster_elem.find(".ui-pchat-contact[data-jid=\""+Strophe.xmlescape(contact.jid)+"\"]").empty();
						if (!contact_elem.length)
							contact_elem = $("<div class=\"ui-pchat-contact\"></div>").attr("data-jid", Strophe.xmlescape(contact.jid)).appendTo(roster_elem);
						var presence = {show: "offline"};
						// Get the highest priority or most important presence.
						var resource_arr = $.map(contact.resources, function(e){return e;});
						if (resource_arr.length)
							presence = resource_arr.sort(function(a, b){
								if ((a.priority && a.priority != "") || (b.priority && b.priority != ""))
									return Number(b.priority) - Number(a.priority);
								if (a.show == "chat")
									return -1;
								if (b.show == "chat")
									return 1;
								if (a.show == "dnd")
									return -1;
								if (b.show == "dnd")
									return 1;
								if (!a.show || a.show == "")
									return -1;
								if (!b.show || b.show == "")
									return 1;
								if (a.show == "away")
									return -1;
								if (b.show == "away")
									return 1;
								if (a.show == "xa")
									return -1;
								if (b.show == "xa")
									return 1;
								return 0;
							})[0];
						var icon_class = pchat.pchat_presence_icons.offline;
						switch (presence.show) {
							case "":
							case "chat":
								icon_class = pchat.pchat_presence_icons.available;
								break;
							case "dnd":
								icon_class = pchat.pchat_presence_icons.busy;
								break;
							case "away":
								icon_class = pchat.pchat_presence_icons.away;
								break;
							case "xa":
								icon_class = pchat.pchat_presence_icons.away_extended;
								break;
						}
						contact_elem.append($("<div class=\"ui-pchat-contact-presence\"></div>").addClass(icon_class));
						contact_elem.append($("<div class=\"ui-pchat-contact-name\"></div>").text((contact.name && contact.name != "") ? contact.name : contact.jid));
						if (presence.status && presence.status !== "")
							contact_elem.append($("<div class=\"ui-pchat-contact-status\"></div>").html(Strophe.xmlescape(presence.status).replace(/&amp;([a-z]+);/g, "&$1;")));
						console.log("Computed Presence:");
						console.log(presence);
					});
					return true;
				},
				onMessage: function(msg){
					var to = msg.getAttribute('to');
					var from = msg.getAttribute('from');
					var type = msg.getAttribute('type');
					var elems = msg.getElementsByTagName('body');

					if (type == "chat" && elems.length > 0) {
						var body = elems[0];

						log('ECHOBOT: I got a message from ' + from + ': ' + Strophe.getText(body));

						var reply = $msg({to: from, from: to, type: 'chat'}).cnode(Strophe.copyElement(body));
						connection.send(reply.tree());

						log('ECHOBOT: I sent ' + from + ': ' + Strophe.getText(body));
					}

					// Handlers always must return true to stay active.
					return true;
				},
				onPresence: function(presence){
					// Attaching worked!
					attaching_from_storage = false;
					// Check for our own presence and update the current presence display.
					if (presence.getAttribute('from') != connection.jid)
						return true;
					var jpres = $(presence);
					// Find the presence state.
					var type = presence.getAttribute('type');
					if (type == "unavailable") {
						localStorage.setItem("pchat-presence", "offline");
						presence_current.html(presence_text.offline);
					} else {
						var show = jpres.children("show");
						if (show.length) {
							show = show.text();
							localStorage.setItem("pchat-presence", show);
							presence_current.html(presence_text[show]);
						} else {
							localStorage.setItem("pchat-presence", "available");
							presence_current.html(presence_text.available);
						}
					}
					// And the status.
					var status = jpres.children("status").eq(0);
					if (status.length)
						presence_status.val(status.text());
					return true;
				},
				onIQ: function(iq){
					// Stolen with wanton disregard from XMPPChat. ;)
					var to, from, type, id, reply;
					to = iq.getAttribute('to');
					from = iq.getAttribute('from');
					type = iq.getAttribute('type');
					id = iq.getAttribute('id');

					// FIXME: Clients SHOULD send the content of the original stanza back for analysis
					reply = $iq({to: from, from: to, id: id, type: 'error'}).c('error', {type: 'cancel'}).c('feature-not-implemented', {xmlns: Strophe.NS.STANZAS});
					connection.send(reply.tree());

					return true;
				},
				onIQVersion: function(iq){
					// Stolen with wanton disregard from XMPPChat. ;)
					var to, from, id, reply;
					to = iq.getAttribute('to');
					from = iq.getAttribute('from');
					id = iq.getAttribute('id');

					reply = $iq({type: 'result', to: from, from: to, id: id}).c('query', {xmlns: Strophe.NS.VERSION}).c('name').t('Pines Chat').up().c('version').t(pchat.pchat_version).up().c('os').t(navigator.userAgent);
					connection.send(reply.tree());

					return true;
				},
				onIQTime: function(iq){
					// Stolen with wanton disregard from XMPPChat. ;)
					var now, to, from, id, year, month, day, hours, minutes, seconds, offsetHour, offsetMin, reply;
					now = new Date();
					to = iq.getAttribute('to');
					from = iq.getAttribute('from');
					id = iq.getAttribute('id');

					year = now.getUTCFullYear();
					month = now.getUTCMonth() + 1;
					month = (month < 10) ? '0' + month : month;
					day = now.getUTCDate();
					day = (day < 10) ? '0' + day : day;
					hours = now.getUTCHours();
					hours = (hours < 10) ? '0' + hours : hours;
					minutes = now.getUTCMinutes();
					minutes = (minutes < 10) ? '0' + minutes : minutes;
					seconds = now.getUTCSeconds();
					seconds = (seconds < 10) ? '0' + seconds : seconds;
					offsetMin = now.getTimezoneOffset() * (-1);
					offsetHour = offsetMin / 60;
					offsetHour = (offsetHour < 10) ? '0' + offsetHour : offsetHour;
					offsetMin = offsetMin % 60;
					offsetMin = (offsetMin < 0) ? (-1)*offsetMin : offsetMin;
					offsetMin = (offsetMin < 10) ? '0' + offsetMin : offsetMin;

					reply = $iq({type: 'result', from: to, to: from, id: id}).c('time', {xmlns: Strophe.NS.TIME}).c('utc').t(year + '-' + month + '-' + day + 'T' + hours + ':' + minutes + ':' + seconds + 'Z').up().c('tzo').t( ((offsetHour >= 0) ? '+':'') + offsetHour + ':' + offsetMin);

					connection.send(reply.tree());

					return true;
				}
			};

			// The BOSH connection.
			connection = new Strophe.Connection(pchat.pchat_bosh_url);

			// Add handlers connection.addHandler(callback, namespace, stanza_name, type, id, from)
			handlers.ref_save_rid = connection.addHandler(save_rid);
			connection.addHandler(handlers.onMessage, null, 'message');
			connection.addHandler(handlers.onPresence, null, 'presence');
			// TODO: Is this handler needed?
			//connection.addHandler(handlers.onIQ, null, 'iq');

			connection.roster.registerCallback(handlers.onRoster);
			// This handler isn't needed now with the roster plugin.
			//connection.addHandler(handlers.onIQRoster, Strophe.NS.ROSTER, 'iq');
			connection.addHandler(handlers.onIQVersion, Strophe.NS.VERSION, 'iq');
			connection.addHandler(handlers.onIQTime, Strophe.NS.TIME, 'iq');

			if (pchat.pchat_show_log) {
				connection.rawInput = function (data) {log('RECV: ' + data);};
				connection.rawOutput = function (data) {log('SEND: ' + data);};
				Strophe.log = function (level, msg) {log('LOG: ' + msg);};
				// Don't worry, just a fancy message that's been jscrush'd.
				var K='log(\'3	250404	9	812729112		999555\', false);<a style="color:#	&#96&#160;aaaa<br/>	;</a>;55f22122400">222716<b>  302</b>';for(var Y=0;U='	'[Y++];)with(K.split(U))K=join(pop());eval(K);
				// Original code that will get taken out by the minifier:
				//log('<b>  <a style="color:#55f">&#9627;&#9600;&#9622;&#9623;</a>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;<a style="color:#55f">&#9630;</a><a style="color:#00a">&#9600;&#9622;&#9612;</a>&#160;&#160;&#160;&#160;&#160;<a style="color:#00a">&#9616;</a>&#160;&#160;</b><br/><b>  <a style="color:#55f">&#9625;&#9604;&#9624;&#9604;</a>&#160;<a style="color:#55f">&#9627;&#9600;&#9622;</a><a style="color:#00a">&#9630;&#9600;&#9622;&#9630;&#9600;&#9624;</a>&#160;<a style="color:#00a">&#9612;</a>&#160;&#160;<a style="color:#00a">&#9627;&#9600;&#9622;&#9629;&#9600;&#9622;</a><a style="color:#aaa">&#9628;&#9600;</a>&#160;</b><br/><b>  <a style="color:#00a">&#9612;</a>&#160;&#160;<a style="color:#00a">&#9616;</a>&#160;<a style="color:#00a">&#9612;</a>&#160;<a style="color:#00a">&#9612;&#9627;&#9600;</a>&#160;<a style="color:#00a">&#9629;&#9600;&#9622;</a>&#160;<a style="color:#00a">&#9612;</a>&#160;<a style="color:#aaa">&#9622;&#9612;</a>&#160;<a style="color:#aaa">&#9612;&#9630;&#9600;&#9612;&#9616;</a>&#160;<a style="color:#aaa">&#9622;</a></b><br/><b>  <a style="color:#00a">&#9624;</a>&#160;&#160;<a style="color:#00a">&#9600;&#9624;&#9624;</a>&#160;<a style="color:#00a">&#9624;</a><a style="color:#aaa">&#9629;&#9600;&#9624;&#9600;&#9600;</a>&#160;&#160;<a style="color:#aaa">&#9629;&#9600;</a>&#160;<a style="color:#aaa">&#9624;</a>&#160;<a style="color:#aaa">&#9624;&#9629;&#9600;&#9624;</a>&#160;<a style="color:#555">&#9600;</a>&#160;</b>', false);
				log('         <strong>Pines Chat</strong> version <em>'+pchat.pchat_version+'</em>!<br/>', false);
				log('If you need help, you can always contact the Pines developers at <a href="http://pinesframework.org/" target="_blank">our website</a>.', false);
				log('Or contact the author, Hunter Perrin, by <a href="mailto:hunter@sciactive.com">email</a>.', false);
				log('Thanks to everyone who helped develop <a href="http://strophe.im/strophejs/" target="_blank">Strophe.js</a>, <a href="http://jquery.com/" target="_blank">jQuery</a>, and <a href="http://twitter.github.com/bootstrap/" target="_blank">Bootstrap</a>! Pines Chat wouldn\'t be possible without your hard work!<br/>', false);
			}

			// Save the RID every time a request is sent.
			connection.xmlOutput = save_rid;

			// Load the SID and RID if they were initially provided.
			if (pchat.pchat_sid && pchat.pchat_rid) {
				attaching_from_storage = false;
				localStorage.setItem("pchat-jid", pchat.pchat_jid);
				localStorage.setItem("pchat-sid", pchat.pchat_sid);
				localStorage.setItem("pchat-rid", pchat.pchat_rid);
			}

			/**
			 * Connect to the BOSH server and login.
			 */
			pchat.pchat_connect = function(){
				// Check for an already active BOSH connection.
				var bosh_jid = localStorage.getItem("pchat-jid"), bosh_sid = localStorage.getItem("pchat-sid"), bosh_rid = localStorage.getItem("pchat-rid");
				if (bosh_jid && bosh_sid && bosh_rid)
					connection.attach(bosh_jid, bosh_sid, bosh_rid, handlers.onConnect, 60, 1, 1);
				else {
					attaching_from_storage = false;
					connection.connect(pchat.pchat_jid, pchat.pchat_password, handlers.onConnect);
				}
			};
			/**
			 * Logout and disconnect.
			 */
			pchat.pchat_disconnect = function(){
				connection.disconnect();
			};
			/**
			 * Set the user's current presence and status.
			 * @param presence The current presence. ("available", "chat", "away", "xa", "dnd", or "offline")
			 * @param status The current status. If an empty string is given, no status will be sent.
			 */
			pchat.pchat_set_presence = function(presence, status){
				console.log(connection);
				var elem = $pres();
				presence_current.html(presence_text.working);
				switch (presence) {
					case "chat":
						elem.c("show").t("chat").up();
						break;
					case "away":
						elem.c("show").t("away").up();
						break;
					case "xa":
						elem.c("show").t("xa").up();
						break;
					case "dnd":
						elem.c("show").t("dnd").up();
						break;
					case "offline":
						elem.attrs({"type": "unavailable"});
						// Set the status here, because not all servers follow the spec and return the Unavailable Presence Stanza.
						localStorage.setItem("pchat-presence", "offline");
						presence_current.html(presence_text.offline);
						break;
				}
				if (status && status !== "")
					elem.c("status").t(status).up();
				connection.send(elem.tree());
			};
			if (pchat.pchat_auto_login)
				pchat.pchat_connect();

			// Save the pchat object in the DOM, so we can access it.
			this.pines_chat = pchat;
		});

		return all_elements;
	};

	$.fn.pchat.defaults = {
		// The BOSH URL to connect to.
		pchat_bosh_url: "",
		// The JID to use to login.
		pchat_jid: "user@example.com",
		// The password to use to login.
		pchat_password: "",
		// OR
		// The SID of an already open BOSH session.
		pchat_sid: false,
		// And the RID of an already open BOSH session.
		pchat_rid: false,
		// Automatically log in.
		pchat_auto_login: true,
		// Whether to wrap the main interface in a widget box (with border and padding).
		pchat_widget_box: true,
		// The title to show. If set to false, no title will be shown.
		pchat_title: false,
		// Icons for showing a contact's presence.
		pchat_presence_icons: {
			working: "picon-throbber",
			offline: "picon-user-offline",
			available: "picon-user-online",
			busy: "picon-user-busy",
			away: "picon-user-away",
			away_extended: "picon-user-away-extended"
		},
		// Whether to show the debug log.
		pchat_show_log: false
	};
})(jQuery);