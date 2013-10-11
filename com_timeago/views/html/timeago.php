<?php
/**
 * A view to load Pines Steps.
 *
 * @package Components\psteps
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_timeago/includes/<?php echo $pines->config->debug_mode ? 'jquery.timeago.js' : 'jquery.timeago.min.js'; ?>");
</script>