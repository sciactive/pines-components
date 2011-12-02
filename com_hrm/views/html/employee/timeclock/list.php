<?php
/**
 * Lists employees and provides functions to manipulate their timeclock.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employee Timeclock';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_hrm/employee/timeclock/list']);
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Report', extra_class: 'picon picon-view-time-schedule-calculus', multi_select: true, double_click: true, click: function(e, rows){
					var employee_ids = [];
					rows.each(function(){
						employee_ids.push($(this).pgrid_get_value(1));
					});
					var dialog = $("#p_muid_report_dialog").clone().removeAttr("id")
					.find("[name=employees]").val(employee_ids.join(",")).end()
					.dialog({
						width: 600,
						modal: true,
						buttons: {
							"Run Report": function(){
								dialog.find("form").submit();
							}
						}
					}).find("[name=date_start], [name=date_end]").datepicker({
						dateFormat: "yy-mm-dd",
						changeMonth: true,
						changeYear: true,
						showOtherMonths: true,
						selectOtherMonths: true
					}).end();
				}},
				{type: 'button', text: 'View Full History', extra_class: 'picon picon-view-time-schedule', url: <?php echo json_encode(pines_url('com_hrm', 'employee/timeclock/view', array('id' => '__title__'))); ?>},
				<?php if (gatekeeper('com_hrm/manageclock')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-view-time-schedule-edit', url: <?php echo json_encode(pines_url('com_hrm', 'employee/timeclock/edit', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_hrm/clock') || gatekeeper('com_hrm/manageclock')) { ?>
				{type: 'button', text: 'Clock In/Out', extra_class: 'picon picon-chronometer', multi_select: true, click: function(e, rows){
					var loader;
					rows.each(function(){
						var cur_row = $(this);
						$.ajax({
							url: <?php echo json_encode(pines_url('com_hrm', 'employee/timeclock/clock')); ?>,
							type: "POST",
							dataType: "json",
							data: {"id": cur_row.pgrid_export_rows()[0].key},
							beforeSend: function(){
								if (!loader)
									loader = $.pnotify({
										pnotify_title: 'Timeclock',
										pnotify_text: 'Communicating with server...',
										pnotify_notice_icon: 'picon picon-throbber',
										pnotify_nonblock: true,
										pnotify_hide: false,
										pnotify_history: false
									});
							},
							complete: function(){
								loader.pnotify_remove();
							},
							error: function(XMLHttpRequest, textStatus){
								pines.error("An error occured while communicating with the server:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
							},
							success: function(data){
								if (data === undefined) {
									alert("No data was returned.");
									return;
								}
								if (data === false) {
									pines.error("There was an error saving the change to the database.");
									return;
								}
								cur_row.pgrid_set_value(4, data ? 'In' : 'Out');
								//cur_row.pgrid_set_value(5, pines.safe(data[1].time));
							}
						});
					});
				}},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'timeclock',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_hrm/employee/timeclock/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>GUID</th>
			<th>Name</th>
			<th>Location</th>
			<th>Status</th>
			<th>Time In</th>
			<th>Time Today *</th>
			<th>Time This Week *</th>
		</tr>
	</thead>
	<tbody>
	<?php $cur_timezone = date_default_timezone_get(); foreach($this->employees as $employee) {
		// Calculate times in the employee's timezone.
		$employee_timezone = $employee->get_timezone();
		date_default_timezone_set($employee_timezone);
		$today_start = strtotime('Today 12:00 AM');
		if (date('w') == '1')
			$week_start = strtotime('Today 12:00 AM');
		else
			$week_start = strtotime('last monday 12:00 AM');
		?>
		<tr title="<?php echo (int) $employee->guid ?>">
			<td><?php echo (int) $employee->guid ?></td>
			<td><?php echo htmlspecialchars($employee->name); ?></td>
			<td><?php echo htmlspecialchars($employee->group->name); ?></td>
			<td><?php echo $employee->timeclock->clocked_in_time() ? 'In' : 'Out'; ?></td>
			<td><?php echo $employee->timeclock->clocked_in_time() ? htmlspecialchars(format_date($employee->timeclock->clocked_in_time(), 'full_sort', '', $employee_timezone)) : ''; ?></td>
			<td><?php echo round($employee->timeclock->sum($today_start, time()) / (60 * 60), 2).' hours'; ?></td>
			<td><?php echo round($employee->timeclock->sum($week_start, time()) / (60 * 60), 2).' hours'; ?></td>
		</tr>
	<?php } date_default_timezone_set($cur_timezone); ?>
	</tbody>
</table>
<small>* Today and this week are calculated with regard to the employee's timezone. Week starts on Monday.</small>
<div title="Hours Clocked Report" id="p_muid_report_dialog" style="display: none;">
	<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_hrm', 'employee/timeclock/report')); ?>">
		<?php
		// Calculate the start of last week.
		if (date('w') == '1')
			$week_end = strtotime('Today 12:00 AM');
		else
			$week_end = strtotime('last monday 12:00 AM');
		$week_start = strtotime('-1 week', $week_end);
		?>
		<div class="pf-element">
			<label><span class="pf-label">Start Date</span>
				<input class="pf-field ui-widget-content ui-corner-all" name="date_start" type="text" size="24" value="<?php echo htmlspecialchars(format_date($week_start, 'custom', 'Y-m-d')); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">End Date</span>
				<input class="pf-field ui-widget-content ui-corner-all" name="date_end" type="text" size="24" value="<?php echo htmlspecialchars(format_date($week_end - 1, 'custom', 'Y-m-d')); ?>" /></label>
		</div>
		<div class="pf-element">
			<span class="pf-label">Local Timezones</span>
			<label><input class="pf-field" type="checkbox" checked="checked" name="local_timezones" value="ON" /> Calculate dates using the employee's timezone.</label>
		</div>
		<div class="pf-element">
			<span class="pf-label">Paginate</span>
			<label><input class="pf-field" type="checkbox" checked="checked" name="paginate" value="ON" /> Paginate report so each user is on a separate page.</label>
		</div>
		<div class="pf-element">
			<span class="pf-label">Show Details</span>
			<label><input class="pf-field" type="checkbox" checked="checked" name="show_details" value="ON" /> Show comments and clock in/out details.</label>
		</div>
		<input type="hidden" name="employees" value="" />
	</form>
	<br />
</div>