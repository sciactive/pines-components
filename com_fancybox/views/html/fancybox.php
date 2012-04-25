<?php
/**
 * A view to load FancyBox.
 *
 * @package Components\fancybox
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_fancybox/includes/jquery.fancybox.css");
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_fancybox/includes/<?php echo $pines->config->debug_mode ? 'jquery.fancybox.js' : 'jquery.fancybox.pack.js'; ?>");
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_fancybox/includes/jquery.mousewheel-3.0.6.pack.js");
</script>