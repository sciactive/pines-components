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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$pines->icons->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pnotify/includes/jquery.pnotify.default.css");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pnotify/includes/jquery.pnotify.default.icons.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pnotify/includes/<?php echo $pines->config->debug_mode ? 'jquery.pnotify.js' : 'jquery.pnotify.min.js'; ?>");
	pines.pnotify_notice_defaults = {pnotify_nonblock: true};
	pines.pnotify_error_defaults = {pnotify_type: "error", pnotify_hide: false, pnotify_nonblock: false};
	pines.load(function(){
		if (!window._alert) {
			window._alert = window.alert;
			window.alert = function(message) {
				$.pnotify({pnotify_title: "Alert", pnotify_text: String(message), pnotify_nonblock: true});
			};
			pines.notice = function(message, title){
				var options = $.extend({pnotify_title: title ? title : "Notice", pnotify_text: String(message)}, pines.pnotify_notice_defaults);
				return $.pnotify(options);
			};
			pines.error = function(message, title){
				var options = $.extend({pnotify_title: title ? title : "Error", pnotify_text: String(message)}, pines.pnotify_error_defaults);
				return $.pnotify(options);
			};
		}
	});
	// ]]>
</script>