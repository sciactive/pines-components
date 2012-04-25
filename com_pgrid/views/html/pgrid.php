<?php
/**
 * A view to load Pines Grid.
 *
 * @package Components\pgrid
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
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pgrid/includes/jquery.pgrid.<?php echo htmlspecialchars($pines->config->com_pgrid->styling); ?>.css");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pgrid/includes/jquery.pgrid.<?php echo htmlspecialchars($pines->config->com_pgrid->styling); ?>.icons.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_pgrid/includes/<?php echo $pines->config->debug_mode ? 'jquery.pgrid.js' : 'jquery.pgrid.min.js'; ?>");
	pines.load(function(){
		$.fn.pgrid.defaults.pgrid_toolbar_target = "<?php echo stripslashes($pines->config->com_pgrid->toolbar_target); ?>";
	});
</script>