<?php
/**
 * A view to load Pines Notify.
 *
 * @package Pines
 * @subpackage com_pnotify
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<link href="<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/jquery.pnotify.default.css" media="all" rel="stylesheet" type="text/css" />
<link href="<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/jquery.pnotify.default.icons.css" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/<?php echo $pines->config->debug_mode ? 'jquery.pnotify.js' : 'jquery.pnotify.min.js'; ?>"></script>
<script type="text/javascript">
	// <![CDATA[
	if (!_alert) {
		var _alert;
		_alert = window.alert;
		window.alert = function(message) {
			$.pnotify({pnotify_title: "Alert", pnotify_text: String(message).replace("\n", "<br />")});
		};
	}
	pines.alert = function(message, title, iconstyles, otheroptions){
		var options = $.extend({}, {
			pnotify_title: title ? title : "Alert",
			pnotify_text: String(message).replace("\n", "<br />"),
			pnotify_notice_icon: iconstyles ? iconstyles : $.pnotify.defaults.pnotify_notice_icon
		}, otheroptions);
		return $.pnotify(options);
	};
	pines.error = function(message, title, iconstyles, otheroptions){
		var options = $.extend({}, {
			pnotify_type: "error",
			pnotify_title: title ? title : "Error",
			pnotify_text: String(message).replace("\n", "<br />"),
			pnotify_error_icon: iconstyles ? iconstyles : $.pnotify.defaults.pnotify_error_icon
		}, otheroptions);
		return $.pnotify(options);
	};
	// ]]>
</script>