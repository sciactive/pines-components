/*
  Copyright 2012, Hunter Perrin <hunter@sciactive.com>
*/
/**
 * Simple Communications Blocking Plugin
 * Allow easy communication blocking management
 *
 *  Features
 *  * Get block list from server
 *  * Block user
 *  * Unblock user
 *  * Unblock all users
 */
Strophe.addConnectionPlugin('blocking',
{
    _connection: null,

	blocking_support: null,
	_disco_id: null,

    _callbacks : [],
    /** Property: blocklist
     * Array of blocked user JIDs
     */
    blocklist : [],
    /** Function: init
     * Plugin init
     *
     * Parameters:
     *   (Strophe.Connection) conn - Strophe connection
     */
    init: function(conn)
    {
		this._connection = conn;
        this.blocklist = [];
		// Override the connect and attach methods to always add stanza handlers.
		// They are removed when the connection disconnects, so must be added on connection.
		var oldCallback, blocking = this, _connect = conn.connect, _attach = conn.attach;
		var newCallback = function(status)
		{
			if (status == Strophe.Status.ATTACHED || status == Strophe.Status.CONNECTED)
			{
				try
				{
					// Disco Info subscription
					conn.addHandler(blocking._onReceiveDisco.bind(blocking), Strophe.NS.DISCO_INFO, 'iq', 'result');
					// Handle block pushes.
					conn.addHandler(blocking._onReceiveIQ.bind(blocking), Strophe.NS.BLOCKING, 'iq', 'set');
					// Ask the server if it supports blocking.
					var request = $iq({type: 'get', to: Strophe.getDomainFromJid(conn.jid), from: conn.jid}).c('query', {xmlns: Strophe.NS.DISCO_INFO});
					blocking._disco_id = conn.sendIQ(request.tree());
				}
				catch (e)
				{
					Strophe.error(e);
				}
			}
			if (oldCallback !== null)
				oldCallback.apply(this, arguments);
		};
		conn.connect = function(jid, pass, callback, wait, hold)
		{
			oldCallback = callback;
			if (typeof arguments[0] == "undefined")
				arguments[0] = null;
			if (typeof arguments[1] == "undefined")
				arguments[1] = null;
			arguments[2] = newCallback;
			_connect.apply(conn, arguments);
		};
		conn.attach = function(jid, sid, rid, callback, wait, hold, wind)
		{
			oldCallback = callback;
			if (typeof arguments[0] == "undefined")
				arguments[0] = null;
			if (typeof arguments[1] == "undefined")
				arguments[1] = null;
			if (typeof arguments[2] == "undefined")
				arguments[2] = null;
			arguments[3] = newCallback;
			_attach.apply(conn, arguments);
		};

        Strophe.addNamespace('BLOCKING', 'urn:xmpp:blocking');
    },
    /** Function: get
     * Get blocklist on server
     *
     * Parameters:
     *   (Function) userCallback - callback on blocklist result
     */
    get: function(userCallback)
    {
		if (this.blocking_support === null)
		{
			// Wait for blocking support to be determined.
			setTimeout(this.get.bind(this, userCallback), 500);
			return;
		}
		else if (this.blocking_support === false)
		{
			throw "Blocking not supported by server.";
		}
        var attrs = {xmlns: Strophe.NS.BLOCKING};
        this.blocklist = [];
        var iq = $iq({type: 'get',  'id' : this._connection.getUniqueId('blocking')}).c('blocklist', attrs);
        this._connection.sendIQ(iq,
                                this._onReceiveBlocklistSuccess.bind(this, userCallback),
                                this._onReceiveBlocklistError.bind(this, userCallback));
    },
    /** Function: registerCallback
     * register callback on blocklist update
     *
     * Parameters:
     *   (Function) call_back
     */
    registerCallback: function(call_back)
    {
        this._callbacks.push(call_back);
    },
    /** Function: block
     * Block a JID
     *
     * Parameters:
     *   (String) jid
     *   (Function) call_back
     */
    block: function(jid, call_back)
    {
		if (!this.blocking_support)
			throw "Blocking support has not been verified.";
        var iq = $iq({type: 'set'}).c('block', {xmlns: Strophe.NS.BLOCKING}).c('item', {jid: jid});
        this._connection.sendIQ(iq, call_back, call_back);
    },
    /** Function: unblock
     * Unblock a JID
     *
     * Parameters:
     *   (String) jid - leave null to unblock all users.
     *   (Function) call_back
     */
    unblock: function(jid, call_back)
    {
		if (!this.blocking_support)
			throw "Blocking support has not been verified.";
        var iq = $iq({type: 'set'}).c('unblock', {xmlns: Strophe.NS.BLOCKING});
		if (jid && jid != null)
			iq.c('item', {jid: jid});
        this._connection.sendIQ(iq, call_back, call_back);
    },
    /** Function: isBlocked
     * Determine if a JID is blocked
     *
     * Parameters:
     *   (String) jid
     */
    isBlocked: function(jid)
    {
		for (var i=0; i<this.blocklist.length; i++)
		{
			if (this.blocklist[i] === jid)
				return true;
		}
		return false;
    },
    /** PrivateFunction: _onReceiveDisco
     * Determine if the server supports blocking.
     */
	_onReceiveDisco: function(disco)
	{
		var id = disco.getAttribute('id');
		if (id != this._disco_id)
			return true;
		var features = disco.getElementsByTagName('feature');
		this.blocking_support = false;
		for (var i=0; i<features.length; i++)
		{
			if (features[i].getAttribute('var') == "urn:xmpp:blocking")
			{
				this.blocking_support = true;
			}
		}
		// Done with this handler, so return false.
		return false;
	},
    /** PrivateFunction: _onReceiveBlocklistSuccess
     *
     */
    _onReceiveBlocklistSuccess: function(userCallback, stanza)
    {
        this._updateItems(stanza);
        userCallback(this.blocklist);
    },
    /** PrivateFunction: _onReceiveBlocklistError
     *
     */
    _onReceiveBlocklistError: function(userCallback, stanza)
    {
        userCallback(this.blocklist);
    },
    /** PrivateFunction: _call_backs
     *
     */
    _call_backs : function(items, item)
    {
        for (var i=0; i<this._callbacks.length; i++)
        {
            this._callbacks[i](items, item);
        }
    },
    /** PrivateFunction: _onReceiveIQ
     * Handle block push.
     */
    _onReceiveIQ : function(iq)
    {
        var id = iq.getAttribute('id');
        var iqresult = $iq({type: 'result', id: id, from: this._connection.jid});
        this._connection.send(iqresult);
        this._updateItems(iq);
        return true;
    },
    /** PrivateFunction: _updateItems
     * Update items from iq
     */
    _updateItems : function(iq)
    {
        var self = this;
        var blocklist = iq.getElementsByTagName('blocklist');
        if (blocklist.length != 0)
			blocklist = iq.getElementsByTagName('block');
        if (blocklist.length != 0)
        {
            Strophe.forEachChild(blocklist.item(0), 'item',
                function (item)
                {
					// User is blocked.
					var jid = item.getAttribute("jid");
                    self.blocklist.push(jid);
                }
           );
        } else {
			blocklist = iq.getElementsByTagName('unblock');
			if (blocklist.length != 0)
			{
				if (blocklist.item(0).getElementsByTagName('item').length)
				{
					Strophe.forEachChild(blocklist.item(0), 'item',
						function (item)
						{
							// User is unblocked.
							var jid = item.getAttribute("jid");
							for (var i=0; i<self.blocklist.length; i++)
							{
								if (self.blocklist[i] === jid)
								{
									self.blocklist.splice(i, 1);
									i--;
								}
							}
						}
					);
				}
				else
				{
					// All users are unblocked.
					this.blocklist = [];
				}
			}
		}
        this._call_backs(this.blocklist);
    }
});