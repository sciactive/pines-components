<?php
/**
 * A view to load Elastislide.
 *
 * @package Components\elastislide
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<?php if ($pines->config->com_elastislide->combine_js) { ?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elastislide/includes/jquery.elastislide.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elastislide/includes/jquery.complete.elastislide.js");
</script>
<?php } else { ?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elastislide/includes/jquery.elastislide.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elastislide/includes/modernizr.custom.17475.js");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elastislide/includes/jquerypp.custom.js");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elastislide/includes/jquery.elastislide.js");
</script>
<?php } ?>