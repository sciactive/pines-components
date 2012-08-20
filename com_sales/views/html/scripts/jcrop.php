<?php
/**
 * A view to load Jcrop.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_sales/includes/jcrop/css/<?php echo $pines->config->debug_mode ? 'jquery.Jcrop.css' : 'jquery.Jcrop.min.css'; ?>");
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_sales/includes/jcrop/js/<?php echo $pines->config->debug_mode ? 'jquery.Jcrop.js' : 'jquery.Jcrop.min.js'; ?>");
</script>