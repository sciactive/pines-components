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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Hours Clocked: '.htmlspecialchars($this->employee ? $this->employee->name : $this->location->name);
if (!$this->all_time)
	$this->note = htmlspecialchars(format_date($this->start_date, 'date_short')).' - '.htmlspecialchars(format_date($this->end_date - 1, 'date_short'));

$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_reports/attendance/hours_clocked']);
?>
<style type="text/css" >
	#p_muid_grid tr.total td {
		font-weight: bold;
	}
</style>
<script type="text/javascript">
	pines(function(){
		pines.search_attendance = function(){
			// Submit the form with all of the fields.
			pines.get(<?php echo json_encode(pines_url('com_reports', 'attendance/hoursclocked')); ?>, {
				"employee": employee,
				"location": location,
				"descendants": descendants,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		var employee = "<?php echo isset($this->employee) ? $this->employee->guid : ''; ?>";
		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = <?php echo $this->start_date ? json_encode(format_date($this->start_date, 'date_sort')) : '""'; ?>;
		var end_date = <?php echo $this->end_date ? json_encode(format_date($this->end_date - 1, 'date_sort')) : '""'; ?>;
		// Location Defaults
		var location = "<?php echo (int) $this->location->guid ?>";
		var descendants = <?php echo $this->descendants ? 'true' : 'false'; ?>;

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){attendance_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){attendance_grid.date_form();}},
				{type: 'separator'},
				<?php if (isset($this->employees)) { ?>
				{type: 'button', text: 'View', extra_class: 'picon picon-user-identity', double_click: true, url: <?php echo json_encode(pines_url('com_reports', 'attendance/hoursclocked', array('employee' => '__title__', 'start_date' => format_date($this->start_date, 'date_sort'), 'end_date' => format_date($this->end_date - 1, 'date_sort'), 'all_time' => ($this->all_time ? 'true' : 'false'), 'location' => $this->location->guid, 'descendants' => $this->descendants ? 'true' : 'false'), false)); ?>},
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
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_reports/attendance/hours_clocked", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		var attendance_grid = $("#p_muid_grid").pgrid(cur_options);

		attendance_grid.date_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'dateselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the date form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Date Selector\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
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
					pines.play();
				}
			});
		};
		attendance_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'locationselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendants": descendants},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the location form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Location Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendants]").attr('checked'))
									descendants = true;
								else
									descendants = false;
								form.dialog('close');
								employee = '';
								pines.search_attendance();
							}
						}
					});
					pines.play();
				}
			});
		};
	});
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
		<?php foreach($this->employees as $cur_employee) { ?>
		<tr title="<?php echo (int) $cur_employee['employee']->guid ?>">
			<td><?php echo htmlspecialchars($cur_employee['employee']->name); ?></td>
			<td><?php echo (float) $cur_employee['scheduled']; ?> hours</td>
			<td><?php echo (float) $cur_employee['clocked']; ?> hours</td>
			<td><span<?php if ($cur_employee['variance'] < 0) echo ' style="color: red;"'; ?>><?php echo (float) $cur_employee['variance']; ?> hours</span></td>
		</tr>
		<?php } ?>
		<tr class="ui-state-highlight total">
			<td>Total</td>
			<td><?php echo (float) $this->totals['scheduled']; ?> hours</td>
			<td><?php echo (float) $this->totals['clocked']; ?> hours</td>
			<td><span<?php if ($this->totals['variance'] < 0) echo ' style="color: red;"'; ?>><?php echo (float) $this->totals['variance']; ?> hours</span></td>
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
		<?php $clock_count = 1; foreach ($this->dates as $cur_date) { ?>
		<tr class="total">
			<td><?php echo htmlspecialchars($cur_date['date']); ?></td>
			<td><?php echo htmlspecialchars($this->employee->group->name); ?></td>
			<td>Scheduled</td>
			<td><?php if (isset($cur_date['sched_start'])) echo htmlspecialchars(format_date($cur_date['sched_start'], 'time_short')); ?></td>
			<td><?php if (isset($cur_date['sched_end'])) echo htmlspecialchars(format_date($cur_date['sched_end'], 'time_short')); ?></td>
			<td><?php echo round($cur_date['scheduled'] / 3600, 2).' hours'; ?></td>
			<td></td>
		</tr>
		<?php foreach ($this->clocks as $cur_clock) {
			if ($cur_clock['date'] != $clock_count)
				continue; ?>
		<tr>
			<td></td>
			<td></td>
			<td>Clocked</td>
			<td><?php echo htmlspecialchars(format_date($cur_clock['in'], 'time_short')); ?></td>
			<td><?php echo htmlspecialchars(format_date($cur_clock['out'], 'time_short')); ?></td>
			<td><?php echo round($cur_clock['total'] / 3600, 2).' hours'; ?></td>
			<td></td>
		</tr>
		<?php } ?>
		<tr class="ui-state-highlight total">
			<td></td>
			<td></td>
			<td>Total</td>
			<td></td>
			<td></td>
			<td><?php echo ($cur_date['total_hours'] > 0) ? ((float) $cur_date['total_hours']).' hours ' : ''; echo ($cur_date['total_mins'] > 0) ? ((float) $cur_date['total_mins']).' min' : ''; ?></td>
			<td><span<?php if ($cur_date['variance'] < 0) echo ' style="color: red;"'; ?>><?php echo (float) $cur_date['variance']; ?> hours</span></td>
		</tr>
		<?php $clock_count++; } ?>
	</tbody>
</table>
<?php } ?>