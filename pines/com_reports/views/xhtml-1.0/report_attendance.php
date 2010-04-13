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
$this->title = 'Employee Attendance: '.($this->employee ? $this->employee->name : $this->location->name).' ('.pines_date_format($this->date[0],null,'Y-m-d').' - '.pines_date_format($this->date[1],null,'Y-m-d').')';
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
	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (isset($this->employees)) { ?>
				{type: 'button', text: 'View', extra_class: 'icon picon_16x16_apps_user-info', double_click: true, url: '<?php echo pines_url('com_reports', 'reportattendance', array('employee' => '#title#', 'start' => pines_date_format($this->date[0], null, 'Y-m-d'), 'end' => pines_date_format($this->date[1], null, 'Y-m-d'), 'location' => $this->location->guid), false); ?>'},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'time_attendance',
						content: rows
					});
				}}
				<?php } else { ?>
				{type: 'button', text: '&laquo; All Employees', extra_class: 'icon picon_16x16_apps_system-users', selection_optional: true, click: function(e, rows){
					pines.post("<?php echo pines_url('com_reports', 'reportattendance'); ?>", {
						start: "<?php echo pines_date_format($this->date[0], null, 'Y-m-d'); ?>",
						end: "<?php echo pines_date_format($this->date[1], null, 'Y-m-d'); ?>",
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
			?>
		<tr title="<?php echo $cur_employee->guid; ?>">
			<td><?php echo $cur_employee->name; ?></td>
			<td><?php echo round($totals[$total_count]['scheduled'] / 3600, 2); ?></td>
			<td><?php echo round($totals[$total_count]['clocked'] / 3600, 2); ?> hours</td>
			<td><?php echo round(($totals[$total_count]['clocked'] - $totals[$total_count]['scheduled']) / 3600, 2); ?> hours</td>
		</tr>
			<?php
			$total_group['scheduled'] += 0; $total_group['clocked'] += $totals[$total_count]['clocked']; $total_count++;
		}
		?>
		<tr class="ui-state-highlight total">
			<td>Total</td>
			<td><?php echo round($total_group['scheduled'] / 3600, 2); ?></td>
			<td><?php echo round($total_group['clocked'] / 3600, 2); ?> hours</td>
			<td><?php echo round(($total_group['clocked'] - $total_group['scheduled']) / 3600, 2); ?> hours</td>
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
				if ($dates[$date_count]['date'] == pines_date_format($entry['time'], null, 'Y-m-d')) {
					// The employee clocked out the same day that they clocked in.
					if ($entry['status'] == 'out') {
						$clocks[$clock_count]['out'] = $entry['time'];
						$dates[$date_count]['total'] += $this->employee->time_sum($clocks[$clock_count]['in'], $entry['time']);
					} else {
						$clock_count++;
						$clocks[$clock_count]['date'] = $date_count;
						$clocks[$clock_count]['in'] = $entry['time'];
					}
				} else {
					// The employee clocked out at a later date after clocking in.
					if ($entry['status'] == 'out') {
						$clocks[$clock_count]['over'] = pines_date_format($entry['time'], null, 'n/j');
						$clocks[$clock_count]['out'] = $entry['time'];
						$dates[$date_count]['total'] += $this->employee->time_sum($clocks[$clock_count]['in'], $entry['time']);
					} else {
						$clock_count++;
						$date_count++;
						$dates[$date_count]['date'] = pines_date_format($entry['time'], null, 'Y-m-d');
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
			?>
			<tr>
				<td><?php echo $cur_date['date']; ?></td>
				<td><?php echo $this->location->name; ?></td>
				<td>Scheduled</td>
				<td></td>
				<td></td>
				<td><?php echo $cur_date['scheduled']; ?></td>
				<td></td>
			</tr>
			<?php foreach ($clocks as $cur_clock) { if ($cur_clock['date'] == $clock_count) { ?>
				<tr>
					<td></td>
					<td></td>
					<td>Clocked</td>
					<td><?php echo pines_date_format($cur_clock['in'], null, 'g:i a'); ?></td>
					<td>
					<?php
						echo pines_date_format($cur_clock['out'], null, 'g:i a');
						echo (isset($cur_clock['over']) ? ' ('.$cur_clock['over'].')' : '');
					?>
					</td>
					<td></td>
					<td></td>
				</tr>
			<?php } }
			$total_hours = floor($cur_date['total'] / 3600);
			$total_mins = round(($cur_date['total'] / 60) - ($total_hours * 60)); ?>
			<tr class="ui-state-highlight total">
				<td></td>
				<td></td>
				<td>Total</td>
				<td></td>
				<td></td>
				<td><?php echo ($total_hours > 0) ? $total_hours.'hours ' : ''; echo ($total_mins > 0) ? $total_mins.'min' : ''; ?></td>
				<td><?php echo round(($cur_date['total'] - $cur_date['scheduled']) / 3600, 2); ?> hours</td>
			</tr>
		<?php $clock_count++; } ?>
	</tbody>
</table>
<?php } ?>