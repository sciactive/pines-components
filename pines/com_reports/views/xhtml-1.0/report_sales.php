<?php
/**
 * Lists all sales for a given timeframe.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 *
 * Built upon:
 * 
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 */
defined('P_RUN') or die('Direct access prohibited');
// Convert the timespan into the number of days that it covers.
$total_seconds = $this->date[1]-$this->date[0];
$days = $total_seconds/(24*60*60);

$date_array = array();
$total = array();
foreach ($this->sales as $cur_sale) {
	$event_month = date('n', $cur_sale->p_cdate);
	$event_day = date('j', $cur_sale->p_cdate);
	$event_year = date('Y', $cur_sale->p_cdate);
	// This is used to identify daily sales, divided into timespan totals.
	$date_str = date('n/j/Y', $cur_sale->p_cdate);
	$sale_time = date('H', $cur_sale->p_cdate);
	foreach ($pines->config->com_reports->timespans as $timespan) {
		$span = explode('-', $timespan);
		if ( ($sale_time >= $span[0]) && ($sale_time < $span[1]) ) {
			if ($date_array[$date_str][$timespan]) {
				$date_array[$date_str][$timespan][3] += $cur_sale->total;
				// Daily totals.
				$total[$date_str][0] = $cur_sale->p_cdate;
				$total[$date_str][1] += $cur_sale->total;
			} else {
				$date_array[$date_str][$timespan][0] = $cur_sale->p_cdate;
				$date_array[$date_str][$timespan][1] = mktime($span[0],0,0,$event_month,$event_day,$event_year);
				$date_array[$date_str][$timespan][2] = mktime($span[1],0,0,$event_month,$event_day,$event_year);
				$date_array[$date_str][$timespan][3] = $cur_sale->total;
				// Daily totals.
				$total[$date_str][0] = $cur_sale->p_cdate;
				$total[$date_str][1] += $cur_sale->total;
			}
		}
	}
}
?>
<script type='text/javascript'>
// <![CDATA[
	var view_changes = 0;
	$(document).ready(function() {
		// Create the calendar object.
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			dragOpacity: {
				agenda: .5,
				'': 0.85
			},
			<?php
				// Depending on the amount of days being reviewed, show a month, week or day calendar.
				if ($days == 0 || $days > 8) {
					echo 'defaultView: \'month\',';
					$class = 'mint_month';
				} else if ($days <= 2) {
					echo 'defaultView: \'agendaDay\',';
					$class = 'mint';
				} else if ($days <= 8) {
					echo 'defaultView: \'agendaWeek\',';
					$class = 'mint';
				}
			?>
			allDayText: 'Total',
			firstDay: 1,
			firstHour: 10,
			theme: true,
			editable: false,
			events: [
				<?php
				$event_counter = 0;
				// Timespanned sales totals (10am-1pm, 7pm-12pm, etc).
				foreach ($date_array as $item) {
					foreach ($item as $cur_item) {
						if ($event_counter > 0)
							echo ',';
						echo '{';
						echo 'id: 0,';
						echo '_id: 0,';
						echo 'title: \'$'. $cur_item[3] .'\',';
						echo 'start: '. $cur_item[1] .',';
						echo 'end: '. $cur_item[2] .',';
						echo 'className: \''.$class.'\',';
						echo 'allDay: false,';
						echo '}';
						$event_counter++;
					}
				}
				// Total sales for each day.
				foreach ($total as $cur_total) {
					echo ',';
					echo '{';
					echo 'id: 0,';
					echo '_id: 0,';
					echo 'title: \'$'. $cur_total[1] .'\',';
					echo 'start: '. $cur_total[0] .',';
					echo 'end: '. $cur_total[0] .',';
					echo 'className: \'mint_total\',';
					echo 'allDay: true,';
					echo '}';
					$event_counter++;
				}
				unset($date_array);
				unset($total);
				?>
			],
			viewDisplay: function(view) {
				// The first couple of times this fires it is loading the initial calendar.
				if (view_changes < 2) {
					view_changes++;
				} else {
					alert('Loading Relevant Sales');
					var view_start = view.start.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1');
					var view_end = view.end.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1');
					var cur_location = location.toString().replace(/reports\/.*/, 'reports/');
					window.location = cur_location +'reportsales/?start='+view_start+'&end='+view_end;
				}
			}
		});
		var current_date = $.fullCalendar.parseDate(<?php echo $this->date[0]; ?>);
		$('#calendar').fullCalendar('gotoDate', current_date);
	});
// ]]>
</script>
<div id="calendar"></div>