<?php
/**
 * Includes for the calendar.
 * 
 * Built upon:
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * @package Components
 * @subpackage calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_calendar/includes/fullcalendar.css");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_calendar/includes/customcolors.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_calendar/includes/<?php echo $pines->config->debug_mode ? 'fullcalendar.js' : 'fullcalendar.min.js'; ?>");
</script>