<?php
/**
 * Lists all sales for a given timeframe.
 *
 * Built upon:
 * 
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

// Convert the timespan into the number of days that it covers.
$total_seconds = $this->date[1]-$this->date[0];
$days = $total_seconds/(24*60*60);

$date_array = array();
$total = array();
foreach ($this->invoices as $cur_invoice) {
	$event_month = format_date($cur_invoice->p_cdate, 'custom', 'n');
	$event_day = format_date($cur_invoice->p_cdate, 'custom', 'j');
	$event_year = format_date($cur_invoice->p_cdate, 'custom', 'Y');
	// This is used to identify daily sales, divided into timespan totals.
	$date_str = format_date($cur_invoice->p_cdate, 'date_sort');
	$sale_time = format_date($cur_invoice->p_cdate, 'custom', 'H');
	if (!$total[$date_str]) {
		$total[$date_str][0] = $cur_invoice->p_cdate;
		$total[$date_str][1] = mktime(0, 1, 1, $event_month, $event_day, $event_year);
		$total[$date_str][2] = mktime(0, 1, 1, $event_month, $event_day, $event_year);
		$total[$date_str][3] = 0;
	}
	// NOTE: Sales made outside of the specified timespans will be excluded!
	foreach ($pines->config->com_reports->timespans as $timespan) {
		$span = explode('-', $timespan);
		if (!$date_array[$date_str][$timespan]) {
			$date_array[$date_str][$timespan][0] = $cur_invoice->p_cdate;
			$date_array[$date_str][$timespan][1] = mktime($span[0], 0, 0, $event_month, $event_day, $event_year);
			$date_array[$date_str][$timespan][2] = mktime($span[1], 0, 0, $event_month, $event_day, $event_year);
			$date_array[$date_str][$timespan][3] = 0;
		}
		if ( ($sale_time >= $span[0]) && ($sale_time < $span[1]) ) {
			if ($cur_invoice->has_tag('sale')) {
				foreach ($cur_invoice->products as $cur_product) {
					if (isset($this->employee->guid) && !$cur_product['salesperson']->is($this->employee))
						continue;
					$date_array[$date_str][$timespan][3] += (float) $cur_product['line_total'];
					$total[$date_str][3] += (float) $cur_product['line_total'];
				}
			} elseif ($cur_invoice->has_tag('return')) {
				foreach ($cur_invoice->products as $cur_product) {
					if (isset($this->employee->guid) && !$cur_product['salesperson']->is($this->employee))
						continue;
					$date_array[$date_str][$timespan][3] -= (float) $cur_product['line_total'];
					$total[$date_str][3] -= (float) $cur_product['line_total'];
				}
			}
		}
		$span_count++;
	}
}
?>
<script type='text/javascript'>
	// <![CDATA[
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
				// Timespan totals (10am-1pm, 7pm-12pm, etc).
				foreach ($date_array as $item) {
					foreach ($item as $cur_item) {
						if ($event_counter > 0)
							echo ',';
						echo '{';
						echo 'id: 0,';
						echo '_id: 0,';
						echo 'title: \'$'.addslashes($cur_item[3]).'\',';
						echo 'start: '. addslashes($cur_item[1]) .',';
						echo 'end: '. addslashes($cur_item[2]) .',';
						echo 'className: \''.addslashes($class).'\',';
						echo 'allDay: false,';
						echo '}';
						$event_counter++;
					}
				}
				// Total sales for each entire day.
				foreach ($total as $cur_total) {
					if ($event_counter > 0)
						echo ',';
					echo '{';
					echo 'id: 0,';
					echo '_id: 0,';
					echo 'title: \'$'. addslashes($cur_total[3]) .'\',';
					echo 'start: '. addslashes($cur_total[1]) .',';
					echo 'end: '. addslashes($cur_total[2]) .',';
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
					pines.get("<?php echo addslashes(pines_url('com_reports', 'reportsales')); ?>", {
						start: view.start.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1'),
						end: view.end.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1'),
						location: "<?php echo $this->all ? 'all' : addslashes($this->location); ?>",
						descendents: "<?php echo $this->descendents ? 'ON' : 'false'; ?>",
						employee: "<?php echo (int) $this->employee->guid ?>"
					});
				}
			}
		});
		var current_date = $.fullCalendar.parseDate(<?php echo (int) $this->date[0]; ?>);
		$('#p_muid_calendar').fullCalendar('gotoDate', current_date);
	});
	// ]]>
</script>
<div id="p_muid_calendar"></div>