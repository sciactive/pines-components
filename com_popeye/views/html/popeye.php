<?php
/**
 * A view to load Popeye.
 *
 * @package Components\popeye
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_popeye/includes/jquery.popeye.css");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_popeye/includes/jquery.popeye.style.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_popeye/includes/<?php echo $pines->config->debug_mode ? 'jquery.popeye.js' : 'jquery.popeye.min.js'; ?>");
</script>