<?php
/**
 * A view to load Inuit CSS.
 *
 * @package Pines
 * @subpackage com_inuitcss
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_inuitcss/includes/core/css/inuit.css");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_inuitcss/includes/<?php echo htmlspecialchars(clean_filename($pines->config->com_inuitcss->grid_layout)); ?>");
	// Get the current number of columns in the CSS grid.
	pines.com_inuitcss_get_columns = function(){
		var cur_grid = 0, cur_test;
		do {
			cur_grid++;
			cur_test = $("<div class=\"grids\"><div class=\"grid-"+cur_grid+"\"></div></div>");
		} while (cur_grid <= 256 && cur_test.children().css("width") != "0px");
		cur_grid--;
		return cur_grid;
	};
	<?php /* Example:
	pines(function(){
		alert(pines.com_inuitcss_get_columns());
	});
	*/ ?>
</script>