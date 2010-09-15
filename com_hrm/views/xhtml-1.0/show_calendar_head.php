<?php
/**
 * Includes for the calendar.
 * 
 * Built upon:
 *
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * Very Simple Context Menu Plugin by Intekhab A Rizvi
 * http://intekhabrizvi.wordpress.com/
 * 
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_hrm/includes/fullcalendar.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_hrm/includes/<?php echo $pines->config->debug_mode ? 'fullcalendar.js' : 'fullcalendar.min.js'; ?>");
	<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
		pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_hrm/includes/context/css/vscontext.css");
		pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_hrm/includes/context/vscontext.jquery.js");
	<?php } ?>
	// ]]>
</script>
