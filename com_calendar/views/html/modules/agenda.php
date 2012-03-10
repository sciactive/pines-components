<?php
/**
 * Personal agenda.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Agenda';
if (!isset($this->view_type))
	$this->view_type = 'basicDay';
?>
<style type="text/css">
	#p_muid_calendar .fc-header-title * {
		font-size: .5em;
		line-height: normal;
	}
</style>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_calendar/includes/fullcalendar.css");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_calendar/includes/customcolors.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_calendar/includes/<?php echo $pines->config->debug_mode ? 'fullcalendar.js' : 'fullcalendar.min.js'; ?>");
	pines(function(){
		// Create the calendar object.
		$('#p_muid_calendar').fullCalendar({
			header: {
				left: 'title',
				center: '',
				right: 'prev,next'
			},
			titleFormat: {
				basicDay: "ddd, MMM d",
				basicWeek: "MMM d[ yyyy]{'-'[MMM ]d[ yyyy]}"
			},
			defaultView: <?php echo json_encode($this->view_type); ?>,
			firstDay: 1,
			selectable: false,
			theme: true,
			ignoreTimezone: false,
			firstHour: <?php echo isset($min_start) ? $min_start : 8; ?>,
			editable: false,
			events: {
				url: <?php echo json_encode(pines_url('com_calendar', 'events_json')); ?>,
				type: "POST",
				data: {
					location: "",
					employee: <?php echo json_encode((string) $_SESSION['user']->guid); ?>,
					descendants: "false",
					filter: "all"
				}
			},
			eventRender: function(event, element){
				var header;
				if (event.allDay)
					header = "<div><strong>All Day</strong></div>";
				else
					header = "<div><strong>Start:</strong> <span>"+pines.safe($.fullCalendar.formatDate(event.start, "ddd MMM dS, yyyy h:mm tt"))+"</span></div><div><strong>End:</strong> <span>"+pines.safe($.fullCalendar.formatDate(event.end, "ddd MMM dS, yyyy h:mm tt"))+"</span></div>";
				element.popover({
					title: pines.safe(event.title),
					content: header+"<p>"+pines.safe(event.info)+"</p>",
					placement: "top"
				});
			}
		});
		var current_date = $.fullCalendar.parseDate(<?php echo strtotime(format_date(time(), 'custom', 'Y-m-d')); ?>);
		$('#p_muid_calendar').fullCalendar('gotoDate', current_date);
	});
</script>
<div id="p_muid_calendar"></div>