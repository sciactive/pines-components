<?php
/**
 * A view to load jQuery UI.
 *
 * @package Components
 * @subpackage jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_jquery/includes/jquery-ui/<?php echo htmlspecialchars($pines->config->com_jquery->theme); ?>/jquery-ui.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_jquery/includes/<?php echo $pines->config->debug_mode ? 'jquery-ui-1.8.17.js' : 'jquery-ui-1.8.17.min.js'; ?>");
	pines.load(function(){
		// This allows to use jquitabs, jquibutton. (In case of name conflict, like Bootstrap.)
		$.widget.bridge('jquitabs', $.ui.tabs);
		$.widget.bridge('jquibutton', $.ui.button);
		<?php if (isset($pines->com_bootstrap)) { ?>
		// And this fixes buttons in dialogs using Bootstrap.
		var real_dialog = $.fn.dialog;
		$.fn.dialog = function(){
			var d = real_dialog.apply(this, arguments);
			if (typeof d == "object" && d.jquery && d.hasClass("ui-dialog-content"))
				real_dialog.call(d, "widget").find(".ui-dialog-buttonpane button").addClass("btn");
			return d;
		};
		<?php } ?>
	});
</script>