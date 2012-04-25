<?php
/**
 * Lists all sales for a given timeframe.
 *
 * Built upon:
 * 
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->note = 'Totals are reported without taxes, item fees, or return fees.';
if (isset($this->employee->guid))
	$this->note .= ' Any flat rate specials which applied to an entire ticket are not included in these totals.';

?>
<script type='text/javascript'>
	pines(function() {
		var view_changes = 0;
		// Create the calendar object.
		$('#p_muid_calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			dragOpacity: {
				agenda: .5,
				'': 0.85
			},
			timeFormat: {
				agenda: 'h:mm{ - h:mm}',
				'': '{htt}'
				//'': 'h{-htt}'
			},
			<?php
				// Depending on the amount of days being reviewed, show a month, week or day calendar.
				if ($this->days == 0 || $this->days > 8) {
					echo 'defaultView: \'month\',';
					$class = 'mint_month';
				} elseif ($this->days <= 2) {
					echo 'defaultView: \'agendaDay\',';
					$class = 'mint';
				} elseif ($this->days <= 8) {
					echo 'defaultView: \'agendaWeek\',';
					$class = 'mint';
				}
			?>
			weekMode: 'liquid',
			allDayText: 'Total',
			firstDay: 1,
			firstHour: 10,
			theme: true,
			editable: false,
			events: [
				<?php
				$event_counter = 0;
				// Total sales for each entire day.
				foreach ($this->total as $cur_total) {
					if ($event_counter > 0)
						echo ',';
					echo '{';
					echo 'id: 0,';
					echo '_id: 0,';
					echo 'title: '. json_encode('$'.$pines->com_sales->round($cur_total[3], true)).',';
					echo 'start: '. json_encode($cur_total[1]) .',';
					echo 'end: '. json_encode($cur_total[2]) .',';
					echo 'className: \'mint_total\',';
					echo 'allDay: true,';
					echo '}';
					$event_counter++;
				}
				// Timespan totals (10am-1pm, 7pm-12pm, etc).
				foreach ($this->date_array as $item) {
					foreach ($item as $cur_item) {
						if ($event_counter > 0)
							echo ',';
						echo '{';
						echo 'id: 0,';
						echo '_id: 0,';
						echo 'title: '.json_encode('$'.$pines->com_sales->round($cur_item[3], true)).',';
						echo 'start: '. json_encode($cur_item[1]) .',';
						echo 'end: '. json_encode($cur_item[2]) .',';
						echo 'className: '.json_encode($class).',';
						echo 'allDay: false,';
						echo '}';
						$event_counter++;
					}
				}
				?>
			],
			viewDisplay: function(view) {
				// The first couple of times this fires it is loading the initial calendar.
				if (view_changes < 2) {
					view_changes++;
				} else {
					alert('Loading Relevant Sales');
					pines.get(<?php echo json_encode(pines_url('com_reports', 'reportsales')); ?>, {
						start: view.start.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1'),
						end: view.end.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1'),
						location: <?php echo $this->all ? '"all"' : json_encode($this->location); ?>,
						descendants: "<?php echo $this->descendants ? 'ON' : 'false'; ?>",
						employee: "<?php echo (int) $this->employee->guid ?>"
					});
				}
			}
		});
		var current_date = $.fullCalendar.parseDate(<?php echo (int) $this->date[0]; ?>);
		$('#p_muid_calendar').fullCalendar('gotoDate', current_date);
	});
</script>
<div id="p_muid_calendar"></div>