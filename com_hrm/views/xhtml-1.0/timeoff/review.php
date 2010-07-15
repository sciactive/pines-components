<?php
/**
 * Display a form to review time off requests.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/timeoff/review'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_view_height: "175px",
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Approve', extra_class: 'picon picon-checkbox', multi_select: true, url: '<?php echo addslashes(pines_url('com_hrm', 'timeoff/classify', array('id' => '__title__', 'status' => 'approved'))); ?>', delimiter: ',', confirm: function(e, rows){
					var approved = true;
					$.each(rows, function(){
						if (!approved) return;
						if ($(this).pgrid_get_value(4) == 'Yes')
							approved = false;
					});
					if (!approved)
						approved = confirm('At least one employee is scheduled for a time which they are requesting off. Are you sure you want to approve time off during a time the employee is scheduled?');
					return approved;
				}},
				{type: 'button', text: 'Decline', extra_class: 'picon picon-dialog-error', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_hrm', 'timeoff/classify', array('id' => '__title__', 'status' => 'declined'))); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'timeoff_requests',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_hrm/timeoff/review", state: cur_state});
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
			<th>Employee</th>
			<th>From</th>
			<th>To</th>
			<th>Scheduled</th>
			<th>Reason</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->requests as $cur_request) {
		$date_format = $cur_request->all_day ? 'n/j/y' : 'n/j/y g:ia'; ?>
		<tr title="<?php echo $cur_request->guid; ?>">
			<td><?php echo htmlentities($cur_request->employee->name); ?></td>
			<td><?php echo format_date($cur_request->start, 'custom', $date_format); ?></td>
			<td><?php echo format_date($cur_request->end, 'custom', $date_format); ?></td>
			<td><?php echo $cur_request->conflicting() ? 'Yes' : 'No'; ?></td>
			<td><?php echo htmlentities($cur_request->reason); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>