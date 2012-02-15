<?php
/**
 * A view to load Bootstrap CSS.
 *
 * @package Pines
 * @subpackage com_bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_bootstrap/includes/css/<?php echo $pines->config->debug_mode ? 'bootstrap.css' : 'bootstrap.min.css'; ?>");
	// Don't load JavaScript yet, cause it messes with jQuery UI.
	//pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_bootstrap/includes/js/<?php echo $pines->config->debug_mode ? 'bootstrap.js' : 'bootstrap.min.js'; ?>");
	// Get the current number of columns in the CSS grid.
	pines.com_bootstrap_get_columns = function(){
		var cur_grid = 0, cur_test;
		do {
			cur_grid++;
			cur_test = $("<div class=\"row\"><div class=\"span"+cur_grid+"\"></div></div>");
		} while (cur_grid <= 256 && cur_test.children().css("width") != "0px");
		cur_grid--;
		return cur_grid;
	};
	<?php /* Example:
	pines(function(){
		alert(pines.com_bootstrap_get_columns());
	});
	*/ ?>
	// ]]>
</script>