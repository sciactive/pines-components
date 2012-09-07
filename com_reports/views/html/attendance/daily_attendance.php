<?php
/**
 * Daily attendance report.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Daily Attendance: '.htmlspecialchars($this->location->name);
$this->note = htmlspecialchars(format_date($this->date, 'date_short'));

$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_reports/attendance/daily_attendance']);
?>
<script type="text/javascript">
	pines(function(){
		pines.search_attendance = function(){
			// Submit the form with all of the fields.
			pines.get(<?php echo json_encode(pines_url('com_reports', 'attendance/dailyattendance')); ?>, {
				"location": location,
				"descendants": descendants,
				"date": date
			});
		};

		// Date Defaults
		var date = <?php echo $this->date ? json_encode(format_date($this->date, 'date_sort')) : '""'; ?>;
		// Location Defaults
		var location = <?php echo json_encode("{$this->location->guid}"); ?>;
		var descendants = <?php echo $this->descendants ? 'true' : 'false'; ?>;

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){attendance_grid.location_form();}},
				{type: 'button', title: 'Date', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){attendance_grid.date_form();}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'time_attendance',
						content: rows
					});
				}}
			],
			pgrid_hidden_cols: [1],
			pgrid_sort_col: 3,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_reports/attendance/daily_attendance", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		var attendance_grid = $("#p_muid_grid").pgrid(cur_options);

		attendance_grid.date_form = function(){
			var form = $("#p_muid_date_dialog");
			form.dialog({
				bgiframe: true,
				autoOpen: true,
				modal: true,
				buttons: {
					"Done": function(){
						date = form.find(":input[name=date]").val();
						form.dialog('close');
						pines.search_attendance();
					}
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
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Date</th>
			<th>Employee</th>
			<th>Location</th>
			<th>Scheduled Start</th>
			<th>Scheduled End</th>
			<th>Clocked Start</th>
			<th>Clocked End</th>
			<th>Start Variance</th>
			<th>End Variance</th>
			<th>Scheduled Total</th>
			<th>Clocked Total</th>
			<th>IP Locations</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->attendance as $cur_attendance) { $timezone = $cur_attendance['employee']->get_timezone(); ?>
		<tr title="<?php echo htmlspecialchars($cur_attendance['employee']->guid); ?>">
			<td><?php echo htmlspecialchars(format_date($this->date, 'date_sort')); ?></td>
			<td><?php echo htmlspecialchars($cur_attendance['employee']->name); ?></td>
			<td><?php echo htmlspecialchars($cur_attendance['employee']->group->name); ?></td>
			<td><?php if ($cur_attendance['scheduled_in']) echo htmlspecialchars(format_date($cur_attendance['scheduled_in'], 'time_short', '', $timezone)); ?></td>
			<td><?php if ($cur_attendance['scheduled_out']) echo htmlspecialchars(format_date($cur_attendance['scheduled_out'], 'time_short', '', $timezone)); ?></td>
			<td><?php if ($cur_attendance['clocked_in']) echo htmlspecialchars(format_date($cur_attendance['clocked_in'], 'time_short', '', $timezone)); ?></td>
			<td><?php if ($cur_attendance['clocked_out']) echo htmlspecialchars(format_date($cur_attendance['clocked_out'], 'time_short', '', $timezone)); ?></td>
			<td><?php if ($cur_attendance['scheduled_in'] && $cur_attendance['clocked_in']) echo htmlspecialchars(format_date_range($cur_attendance['scheduled_in'], $cur_attendance['clocked_in'], '#minutes# minutes', $timezone)); ?></td>
			<td><?php if ($cur_attendance['scheduled_out'] && $cur_attendance['clocked_out']) echo htmlspecialchars(format_date_range($cur_attendance['scheduled_out'], $cur_attendance['clocked_out'], '#minutes# minutes', $timezone)); ?></td>
			<td><?php if ($cur_attendance['scheduled_total']) echo htmlspecialchars(format_date_range(0, $cur_attendance['scheduled_total'], '{#hours# hours}{#hour# hour} {#minutes# minutes}{#minute# minute}', $timezone)); ?></td>
			<td><?php if ($cur_attendance['clocked_total']) echo htmlspecialchars(format_date_range(0, $cur_attendance['clocked_total'], '{#hours# hours}{#hour# hour} {#minutes# minutes}{#minute# minute}', $timezone)); ?></td>
			<td><?php echo htmlspecialchars(implode(', ', $cur_attendance['clocked_ips'])); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<div id="p_muid_date_dialog" title="Date Selector" style="display: none;">
	<style type="text/css" >
		#p_muid_form {
			padding-left: 25px;
		}
		#p_muid_form .form_date {
			width: 80%;
			text-align: center;
		}
	</style>
	<script type='text/javascript'>
		pines(function(){
			$("#p_muid_date").datepicker({
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true
			});
		});
	</script>
	<div class="pf-form" id="p_muid_form" action="">
		<div class="timespan">
			<div class="pf-element">
				<label><span class="pf-label">Pick a Date</span>
					<input class="pf-field form_date" type="text" id="p_muid_date" name="date" value="<?php echo isset($this->date) ? htmlspecialchars(format_date($this->date, 'date_sort')) : ''; ?>" /></label>
			</div>
		</div>
	</div>
	<br />
</div>