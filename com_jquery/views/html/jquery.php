<?php
/**
 * A view to load jQuery.
 *
 * @package Components\jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_jquery/includes/<?php echo $pines->config->debug_mode ? 'jquery-1.7.1.js' : 'jquery-1.7.1.min.js'; ?>");
</script>