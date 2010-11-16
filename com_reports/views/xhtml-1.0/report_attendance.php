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

$this->title = 'Employee Attendance: '.($this->employee ? $this->employee->name : $this->location->name);
if (!$this->all_time)
	$this->note = format_date($this->start_date, 'date_short').' - '.format_date($this->end_date, 'date_short');

$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_attendance'];
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_grid tr.total td {
		font-weight: bold;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		pines.search_attendance = function(){
			// Submit the form with all of the fields.
			pines.post("<?php echo addslashes(pines_url('com_reports', 'reportattendance')); ?>", {
				"employee": employee,
				"location": location,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		var employee = "<?php echo isset($this->employee) ? $this->employee->guid : ''; ?>";
		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo $this->location->guid; ?>";

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){attendance_grid.location_form();}},
				{type: 'button', text: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){attendance_grid.date_form();}},
				{type: 'separator'},
				<?php if (isset($this->employees)) { ?>
				{type: 'button', text: 'View', extra_class: 'picon picon-user-identity', double_click: true, url: '<?php echo addslashes(pines_url('com_reports', 'reportattendance', array('employee' => '__title__', 'start_date' => format_date($this->start_date, 'date_sort'), 'end_date' => format_date($this->end_date, 'date_sort'), 'all_time' => ($this->all_time ? 'true' : 'false'), 'location' => $this->location->guid), false)); ?>'},
				{type: 'separator'},
				<?php } else { ?>
				{type: 'button', text: '&laquo; All Employees', extra_class: 'picon picon-system-users', selection_optional: true, click: function(){
					employee = '';
					pines.search_attendance();
				}},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'time_attendance',
						content: rows
					});
				}}
			],
			pgrid_sortable: false,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_reports/report_attendance", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		var attendance_grid = $("#p_muid_grid").pgrid(cur_options);

		attendance_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'dateselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the date form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Date Selector\" />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 315,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
								pines.search_attendance();
							}
						}
					});
				}
			});
		};
		attendance_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the location form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Location Selector\" />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 250,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								location = form.find(":input[name=location]").val();
								form.dialog('close');
								employee = '';
								pines.search_attendance();
							}
						}
					});
				}
			});
		};
	});
	// ]]>
</script>
<?php if (isset($this->employees)) { ?>
<table id="p_muid_grid">
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
			$schedule = $pines->entity_manager->get_entities(
					array('class' => com_hrm_event),
					array('&',
						'tag' => array('com_hrm', 'event'),
						'gte' => array('start', $this->start_date),
						'lte' => array('end', $this->end_date),
						'ref' => array('employee', $cur_employee)
					)
				);
			foreach ($schedule as $cur_schedule)
				$totals[$total_count]['scheduled'] += $cur_schedule->scheduled;
			$totals[$total_count]['clocked'] = $cur_employee->timeclock->sum($this->start_date, $this->end_date);

			$scheduled = round($totals[$total_count]['scheduled'] / 3600, 2);
			$clocked = round($totals[$total_count]['clocked'] / 3600, 2);
			$variance = round(($totals[$total_count]['clocked'] - $totals[$total_count]['scheduled']) / 3600, 2);
			?>
		<tr title="<?php echo $cur_employee->guid; ?>">
			<td><?php echo htmlspecialchars($cur_employee->name); ?></td>
			<td><?php echo $scheduled; ?> hours</td>
			<td><?php echo $clocked; ?> hours</td>
			<td><span<?php if ($variance < 0) echo ' style="color: red;"'; ?>><?php echo $variance; ?> hours</span></td>
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
			<td><span<?php if ($variance < 0) echo ' style="color: red;"'; ?>><?php echo $variance; ?> hours</span></td>
		</tr>
	</tbody>
</table>
<?php } elseif (isset($this->employee)) { ?>
<table id="p_muid_grid">
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
		foreach($this->employee->timeclock->timeclock as $key => $entry) {
			if ( $this->all_time ||
				($entry['in'] >= $this->start_date &&
				($entry['out'] <= $this->end_date || !isset($entry['out']))) ) {
				if ($dates[$date_count]['date'] != format_date($entry['in'], 'date_sort')) {
					$date_count++;
					$dates[$date_count]['start'] = strtotime('00:00', $entry['in']);
					$dates[$date_count]['end'] = strtotime('23:59', $entry['out']);
					$dates[$date_count]['date'] = format_date($entry['in'], 'date_sort');
					$dates[$date_count]['scheduled'] = 0;
					$dates[$date_count]['total'] = 0;
				}
				$clock_count++;
				$clocks[$clock_count] = $entry;
				$clocks[$clock_count]['date'] = $date_count;
				$dates[$date_count]['total'] += $clocks[$clock_count]['total'] = $this->employee->timeclock->sum($entry['in'], isset($entry['out']) ? $entry['out'] : time());
			}
		}
		$clock_count = 1;
		foreach ($dates as $cur_date) {
			$scheduled = $pines->entity_manager->get_entities(
					array('class' => com_hrm_event),
					array('&',
						'tag' => array('com_hrm', 'event'),
						'gte' => array('start', $cur_date['start']),
						'lte' => array('end', $cur_date['end']),
						'ref' => array('employee', $this->employee)
					)
				);
			foreach ($scheduled as $cur_schedule) {
				$cur_date['sched_start'] = $cur_schedule->start;
				$cur_date['sched_end'] = $cur_schedule->end;
				$cur_date['scheduled'] += $cur_schedule->scheduled;
			}
		?>
			<tr class="total">
				<td><?php echo htmlspecialchars($cur_date['date']); ?></td>
				<td><?php echo htmlspecialchars($this->employee->group->name); ?></td>
				<td>Scheduled</td>
				<td><?php if (isset($cur_date['sched_start'])) echo format_date($cur_date['sched_start'], 'time_short'); ?></td>
				<td><?php if (isset($cur_date['sched_end'])) echo format_date($cur_date['sched_end'], 'time_short'); ?></td>
				<td><?php echo round($cur_date['scheduled'] / 3600, 2).' hours'; ?></td>
				<td></td>
			</tr>
			<?php
			foreach ($clocks as $cur_clock) {
				if ($cur_clock['date'] == $clock_count) { ?>
				<tr>
					<td></td>
					<td></td>
					<td>Clocked</td>
					<td><?php echo format_date($cur_clock['in'], 'time_short'); ?></td>
					<td><?php echo format_date($cur_clock['out'], 'time_short'); ?></td>
					<td><?php echo round($cur_clock['total'] / 3600, 2).' hours'; ?></td>
					<td></td>
				</tr>
			<?php }
			}
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