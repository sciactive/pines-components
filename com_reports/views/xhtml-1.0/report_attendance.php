<?php
/**
 * Shows an employees timeclock history.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employee Attendance: '.($this->employee ? $this->employee->name : $this->location->name).' ('.format_date($this->date[0], 'date_short').' - '.format_date($this->date[1], 'date_short').')';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_attendance'];
?>
<style type="text/css" >
	/* <![CDATA[ */
	#timeclock_grid tr.total td {
		font-weight: bold;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (isset($this->employees)) { ?>
				{type: 'button', text: 'View', extra_class: 'picon picon_16x16_user-identity', double_click: true, url: '<?php echo pines_url('com_reports', 'reportattendance', array('employee' => '__title__', 'start' => format_date($this->date[0], 'date_short'), 'end' => format_date($this->date[1], 'date_short'), 'location' => $this->location->guid), false); ?>'},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'picon picon_16x16_document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon_16x16_document-close', select_none: true},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon_16x16_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'time_attendance',
						content: rows
					});
				}}
				<?php } else { ?>
				{type: 'button', text: '&laquo; All Employees', extra_class: 'picon picon_16x16_system-users', selection_optional: true, click: function(e, rows){
					pines.post("<?php echo pines_url('com_reports', 'reportattendance'); ?>", {
						start: "<?php echo format_date($this->date[0], 'date_short'); ?>",
						end: "<?php echo format_date($this->date[1], 'date_short'); ?>",
						location: "<?php echo $this->location->guid; ?>"
					});
				}}
				<?php } ?>
			],
			pgrid_sortable: false,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_reports/report_attendance", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		$("#timeclock_grid").pgrid(cur_options);
	});
	// ]]>
</script>
<?php if (isset($this->employees)) { ?>
<table id="timeclock_grid">
	<thead>
		<tr>
			<th>Employee</th>
			<th>Scheduled</th>
			<th>Clocked</th>
			<th>Variance</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$totals = array();
		$total_group['scheduled'] = $total_group['clocked'] = $time_punch = $total_count = 0;
		foreach($this->employees as $cur_employee) {
			$totals[$total_count]['scheduled'] = $totals[$total_count]['clocked'] = 0;
			$schedule = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'event'), 'ref' => array('employee' => $cur_employee), 'class' => com_hrm_event));
			foreach ($schedule as $cur_schedule)
				$totals[$total_count]['scheduled'] += ($cur_schedule->end - $cur_schedule->start);
			foreach($cur_employee->timeclock as $clock) {
				if ($clock['time'] >= $this->date[0] && $clock['time'] <= $this->date[1]) {
					if ($clock['status'] == 'out' && ($time_punch > 0)) {
						$totals[$total_count]['clocked'] += ($clock['time'] - $time_punch);
						$time_punch = 0;
					} else if ($clock['status'] == 'in') {
						$time_punch = $clock['time'];
					}
				}
			}
			$scheduled = round($totals[$total_count]['scheduled'] / 3600, 2);
			$clocked = round($totals[$total_count]['clocked'] / 3600, 2);
			$variance = round(($totals[$total_count]['clocked'] - $totals[$total_count]['scheduled']) / 3600, 2);
			?>
		<tr title="<?php echo $cur_employee->guid; ?>">
			<td><?php echo $cur_employee->name; ?></td>
			<td><?php echo $scheduled ?> hours</td>
			<td><?php echo $clocked ?> hours</td>
			<td><span<?php if ($variance < 0) echo ' style="color: red;"'; ?>><?php echo $variance ?> hours</span></td>
		</tr>
			<?php
			$total_group['scheduled'] += $totals[$total_count]['scheduled'];
			$total_group['clocked'] += $totals[$total_count]['clocked'];
			$total_count++;
		}
		$scheduled = round($total_group['scheduled'] / 3600, 2);
		$clocked = round($total_group['clocked'] / 3600, 2);
		$variance = round(($total_group['clocked'] - $total_group['scheduled']) / 3600, 2);
		?>
		<tr class="ui-state-highlight total">
			<td>Total</td>
			<td><?php echo $scheduled; ?> hours</td>
			<td><?php echo $clocked; ?> hours</td>
			<td><span<?php if ($variance < 0) echo ' style="color: red;"'; ?>><?php echo $variance ?> hours</span></td>
		</tr>
	</tbody>
</table>
<?php } else if (isset($this->employee)) { ?>
<table id="timeclock_grid">
	<thead>
		<tr>
			<th>Local Time</th>
			<th>Location</th>
			<th>Time</th>
			<th>In</th>
			<th>Out</th>
			<th>Total</th>
			<th>Variance</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$clocks = $dates = array();
		$clock_count = $date_count = 0;
		foreach($this->employee->timeclock as $key => $entry) {
			if ($entry['time'] >= $this->date[0] && $entry['time'] <= $this->date[1]) {
				if ($dates[$date_count]['date'] == format_date($entry['time'], 'date_short')) {
					// The employee clocked out the same day that they clocked in.
					if ($entry['status'] == 'out') {
						$clocks[$clock_count]['out'] = $entry['time'];
						$dates[$date_count]['total'] += $clocks[$clock_count]['total'] = $this->employee->time_sum($clocks[$clock_count]['in'], $entry['time']);
					} else {
						$clock_count++;
						$clocks[$clock_count]['date'] = $date_count;
						$clocks[$clock_count]['in'] = $entry['time'];
					}
				} else {
					// The employee clocked out at a later date after clocking in.
					if ($entry['status'] == 'out') {
						$clocks[$clock_count]['over'] = format_date($entry['time'], 'custom', 'n/j');
						$clocks[$clock_count]['out'] = $entry['time'];
						$dates[$date_count]['total'] += $clocks[$clock_count]['total'] = $this->employee->time_sum($clocks[$clock_count]['in'], $entry['time']);
					} else {
						$clock_count++;
						$date_count++;
						$dates[$date_count]['start'] = strtotime('00:00', $entry['time']);
						$dates[$date_count]['end'] = strtotime('23:59', $entry['time']);
						$dates[$date_count]['date'] = format_date($entry['time'], 'date_short');
						$dates[$date_count]['scheduled'] = 0;
						$dates[$date_count]['total'] = 0;
						$clocks[$clock_count]['date'] = $date_count;
						$clocks[$clock_count]['in'] = $entry['time'];
					}
				}
			}
		}
		$clock_count = 1;
		foreach ($dates as $cur_date) {
			$scheduled = $pines->entity_manager->get_entities(array('tags' => array('com_hrm', 'event'), 'ref' => array('employee' => $this->employee), 'gte' => array('start' => $cur_date['start']), 'lte' => array('end' => $cur_date['end']), 'class' => com_hrm_event));
			foreach ($scheduled as $cur_schedule) {
				$cur_date['sched_start'] = $cur_schedule->start;
				$cur_date['sched_end'] = $cur_schedule->end;
				$cur_date['scheduled'] += ($cur_schedule->end - $cur_schedule->start);
			}
		?>
			<tr class="total">
				<td><?php echo $cur_date['date']; ?></td>
				<td><?php echo $this->location->name; ?></td>
				<td>Scheduled</td>
				<td><?php if (isset($cur_date['sched_start'])) echo format_date($cur_date['sched_start'], 'time_short'); ?></td>
				<td><?php if (isset($cur_date['sched_end'])) echo format_date($cur_date['sched_end'], 'time_short'); ?></td>
				<td><?php echo round($cur_date['scheduled'] / 3600, 2).' hours'; ?></td>
				<td></td>
			</tr>
			<?php foreach ($clocks as $cur_clock) { if ($cur_clock['date'] == $clock_count) { ?>
				<tr>
					<td></td>
					<td></td>
					<td>Clocked</td>
					<td><?php echo format_date($cur_clock['in'], 'time_short'); ?></td>
					<td>
					<?php
						echo format_date($cur_clock['out'], 'time_short');
						echo (isset($cur_clock['over']) ? ' ('.$cur_clock['over'].')' : '');
					?>
					</td>
					<td><?php echo round($cur_clock['total'] / 3600, 2).' hours'; ?></td>
					<td></td>
				</tr>
			<?php } }
			$total_hours = floor($cur_date['total'] / 3600);
			$total_mins = round(($cur_date['total'] / 60) - ($total_hours * 60));
			$variance = round(($cur_date['total'] - $cur_date['scheduled']) / 3600, 2);
			?>
			<tr class="ui-state-highlight total">
				<td></td>
				<td></td>
				<td>Total</td>
				<td></td>
				<td></td>
				<td><?php echo ($total_hours > 0) ? $total_hours.'hours ' : ''; echo ($total_mins > 0) ? $total_mins.'min' : ''; ?></td>
				<td><span<?php if ($variance < 0) echo ' style="color: red;"'; ?>><?php echo $variance; ?> hours</span></td>
			</tr>
		<?php $clock_count++; } ?>
	</tbody>
</table>
<?php } ?>