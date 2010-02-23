<?php
/**
 * Provides a form for the user to edit a manufacturer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var callbacks = [];
		var drawer_opened_properly = false;

		// The modal dialog.
		var dialog = $("<div title=\"Cash Drawer\" />").append($("<p />", {"class": "dialog_text"})).dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600
		});
		
		pines.drawer_not_supported = function(event){
			var current = callbacks.slice();
			callbacks = [];
			$.each(current, function(index, value){
				value("not_supported");
			});
		};
		window.addEventListener("pines_cash_drawer_not_supported", pines.drawer_not_supported, false);
		pines.drawer_error = function(event){
			var current = callbacks.slice();
			callbacks = [];
			$.each(current, function(index, value){
				value("error");
			});
		};
		window.addEventListener("pines_cash_drawer_error", pines.drawer_error, false);
		pines.drawer_is_closed = function(event){
			if (dialog.dialog("isOpen"))
				dialog.dialog("close");
			drawer_opened_properly = false;
			var current = callbacks.slice();
			callbacks = [];
			$.each(current, function(index, value){
				value("is_closed");
			});
		};
		window.addEventListener("pines_cash_drawer_is_closed", pines.drawer_is_closed, false);
		pines.drawer_is_open = function(event){
			if (!dialog.dialog("isOpen")) {
				dialog.find("p.dialog_text").html(
					drawer_opened_properly ?
						"<span style=\"font-size: 2em;\">Close the cash drawer when you are finished in order to continue.</span>" :
						"<span style=\"font-size: 2em; color: red;\">The cash drawer has been opened without authorization. Close the cash drawer immediately. Corporate has been notified and the incident has been logged.</span>"
				);
				if (!drawer_opened_properly) {
					// notify manager
				}
				dialog.dialog("open");
			}
			var current = callbacks.slice();
			callbacks = [];
			$.each(current, function(index, value){
				value("is_open");
			});
		};
		window.addEventListener("pines_cash_drawer_is_open", pines.drawer_is_open, false);
		pines.drawer_not_found = function(event){
			if (!dialog.dialog("isOpen")) {
				dialog.find("p.dialog_text").html(
					"<span style=\"font-size: 2em; color: red;\">The cash drawer has been disconnected. Reconnect the cash drawer immediately. Corporate has been notified and the incident has been logged.</span>"
				);
				if (!drawer_opened_properly) {
					// notify manager
				}
				dialog.dialog("open");
			}
			var current = callbacks.slice();
			callbacks = [];
			$.each(current, function(index, value){
				value("not_found");
			});
		};
		window.addEventListener("pines_cash_drawer_not_found", pines.drawer_not_found, false);
		pines.drawer_misconfigured = function(event){
			var current = callbacks.slice();
			callbacks = [];
			$.each(current, function(index, value){
				value("misconfigured");
			});
		};
		window.addEventListener("pines_cash_drawer_misconfigured", pines.drawer_misconfigured, false);
		pines.drawer_check = function(callback){
			if ($.isFunction(callback))
				$.merge(callbacks, [callback]);
			var evt = document.createEvent("Events");
			evt.initEvent("pines_cash_drawer_check", true, false);
			window.dispatchEvent(evt);
		};
		pines.drawer_open = function(callback){
			drawer_opened_properly = true;
			if ($.isFunction(callback))
				$.merge(callbacks, [callback]);
			var evt = document.createEvent("Events");
			evt.initEvent("pines_cash_drawer_open", true, false);
			window.dispatchEvent(evt);
		};
		
		setInterval(pines.drawer_check, 1000);
	});
	// ]]>
</script>