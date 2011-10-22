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
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/employee/timeclock/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'View', extra_class: 'picon picon-view-time-schedule', double_click: true, url: '<?php echo addslashes(pines_url('com_hrm', 'employee/timeclock/view', array('id' => '__title__'))); ?>'},
				<?php if (gatekeeper('com_hrm/manageclock')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-view-time-schedule-edit', url: '<?php echo addslashes(pines_url('com_hrm', 'employee/timeclock/edit', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_hrm/clock') || gatekeeper('com_hrm/manageclock')) { ?>
				{type: 'button', text: 'Clock In/Out', extra_class: 'picon picon-chronometer', multi_select: true, click: function(e, rows){
					var loader;
					rows.each(function(){
						var cur_row = $(this);
						$.ajax({
							url: "<?php echo addslashes(pines_url('com_hrm', 'employee/timeclock/clock')); ?>",
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
								pines.error("An error occured while communicating with the server:\n"+XMLHttpRequest.status+": "+textStatus);
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
								cur_row.pgrid_set_value(3, data ? 'In' : 'Out');
								//cur_row.pgrid_set_value(4, data[1].time);
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
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_hrm/employee/timeclock/list", state: cur_state});
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
			<th>ID</th>
			<th>Name</th>
			<th>Status</th>
			<th>Time In</th>
			<th>Time Today</th>
			<th>Time Sum</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->employees as $employee) { ?>
		<tr title="<?php echo $employee->guid; ?>">
			<td><?php echo $employee->guid; ?></td>
			<td><?php echo htmlspecialchars($employee->name); ?></td>
			<td><?php echo $employee->timeclock->clocked_in_time() ? 'In' : 'Out'; ?></td>
			<td><?php echo $employee->timeclock->clocked_in_time() ? format_date($employee->timeclock->clocked_in_time(), 'full_sort', '', $employee->get_timezone(true)) : ''; ?></td>
			<td><?php echo round($employee->timeclock->sum(strtotime('Today 12:00 AM'), time()) / (60 * 60), 2).' hours'; ?></td>
			<td><?php echo round($employee->timeclock->sum() / (60 * 60), 2).' hours'; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>