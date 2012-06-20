<?php
/**
 * A view to load Pines Notify.
 *
 * @package Components\pnotify
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$pines->icons->load();
?>
<script type="text/javascript">
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pnotify/includes/jquery.pnotify.default.css");
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pnotify/includes/jquery.pnotify.default.icons.css");
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pnotify/includes/<?php echo $pines->config->debug_mode ? 'jquery.pnotify.js' : 'jquery.pnotify.min.js'; ?>");
pines.pnotify_alert_defaults = {nonblock: true};
pines.pnotify_notice_defaults = {nonblock: true};
pines.pnotify_error_defaults = {type: "error", hide: false, nonblock: false};
pines.load(function(){
	if (!window._alert) {
		window._alert = window.alert;
		window.alert = function(message){
			var options = $.extend({title: "Alert", text: pines.safe(message)}, pines.pnotify_alert_defaults);
			return $.pnotify(options);
		};
		pines.notice = function(message, title){
			var options = $.extend({title: title ? title : "Notice", text: String(message)}, pines.pnotify_notice_defaults);
			return $.pnotify(options);
		};
		pines.error = function(message, title){
			var options = $.extend({title: title ? title : "Error", text: String(message)}, pines.pnotify_error_defaults);
			return $.pnotify(options);
		};
	}
});
</script>
<style type="text/css">
.ui-pnotify-history-pulldown {
width: 16px;
}
.ui-pnotify-closer span, .ui-pnotify-sticker span {
width: 16px;
display: inline-block;
}
</style>