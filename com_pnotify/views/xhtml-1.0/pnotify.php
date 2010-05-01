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
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/jquery.pnotify.default.css");
	pines.loadcss("<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/jquery.pnotify.default.icons.css");
	pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/<?php echo $pines->config->debug_mode ? 'jquery.pnotify.js' : 'jquery.pnotify.min.js'; ?>");
	pines.load(function(){
		if (!_alert) {
			var _alert;
			_alert = window.alert;
			window.alert = function(message) {
				$.pnotify({pnotify_title: "Alert", pnotify_text: String(message), pnotify_nonblock: true});
			};
		}
		pines.alert = function(message, title){
			var options = {
				pnotify_title: title ? title : "Alert",
				pnotify_text: String(message),
				pnotify_nonblock: true
			};
			return $.pnotify(options);
		};
		pines.error = function(message, title){
			var options = {
				pnotify_type: "error",
				pnotify_title: title ? title : "Error",
				pnotify_text: String(message),
				pnotify_hide: false
			};
			return $.pnotify(options);
		};
	});
	// ]]>
</script>