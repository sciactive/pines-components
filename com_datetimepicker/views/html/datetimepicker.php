<?php
/**
 * A view to load Time Picker Addon.
 *
 * @package Pines
 * @subpackage com_datetimepicker
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css">
	.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
	.ui-timepicker-div dl { text-align: left; }
	.ui-timepicker-div dl dt { height: 25px; }
	.ui-timepicker-div dl dd { margin: -25px 10px 10px 65px; }
	.ui-timepicker-div td { font-size: 90%; }
	.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
</style>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_datetimepicker/includes/<?php echo $pines->config->debug_mode ? 'jquery-ui-timepicker-addon.js' : 'jquery-ui-timepicker-addon.min.js'; ?>");
	// ]]>
</script>