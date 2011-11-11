<?php
/**
 * Lists employee adjustments and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Kirk Johnson <kirk@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employee Adjustments';
$pines->com_pgrid->load();
$pines->com_hrm->load_employee_select();
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var adjustment_id;
		var adjustment_dialog = $("#p_muid_adjustment_dialog");

		$("#p_muid_adjustment_dialog [name=effective_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		adjustment_dialog.find("form").submit(function(){
			adjustment_dialog.dialog('option', 'buttons').Save();
			return false;
		});
		adjustment_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 300,
			buttons: {
				'Save': function(){
					pines.post(<?php echo json_encode(pines_url('com_hrm', 'adjustment/save')); ?>, {
						id: adjustment_id,
						name: $("#p_muid_adjustment_dialog [name=name]").val(),
						employee: $("#p_muid_adjustment_dialog [name=employee]").val(),
						date: $("#p_muid_adjustment_dialog [name=effective_date]").val(),
						amount: $("#p_muid_adjustment_dialog [name=amount]").val(),
						comments: $("#p_muid_adjustment_dialog [name=comments]").val()
					});
					adjustment_dialog.dialog("close");
				}
			}
		});

		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_hrm/listemployees')) { ?>
				{type: 'button', text: '&laquo; Employees', extra_class: 'picon picon-system-users', selection_optional: true, url: <?php echo json_encode(pines_url('com_hrm', 'employee/list')); ?>},
				<?php } if (gatekeeper('com_hrm/editadjustment')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, click: function(){
					adjustment_id = 0;
					$("#p_muid_adjustment_dialog [name=effective_date]").val(<?php echo json_encode(format_date(time(), 'date_sort')); ?>);
					$("#p_muid_adjustment_dialog [name=name]").val("");
					$("#p_muid_adjustment_dialog [name=employee]").val("");
					$("#p_muid_adjustment_dialog [name=amount]").val("");
					$("#p_muid_adjustment_dialog [name=comments]").val("");
					adjustment_dialog.dialog("open");
				}},
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, click: function(e, row){
					adjustment_id = row.attr("title");
					$("#p_muid_adjustment_dialog [name=effective_date]").val(pines.unsafe(row.pgrid_get_value(1)));
					$("#p_muid_adjustment_dialog [name=name]").val(pines.unsafe(row.pgrid_get_value(2)));
					$("#p_muid_adjustment_dialog [name=employee]").val(pines.unsafe(row.pgrid_get_value(3)));
					$("#p_muid_adjustment_dialog [name=amount]").val(pines.unsafe(row.pgrid_get_value(4)).replace('$',''));
					$("#p_muid_adjustment_dialog [name=comments]").val(pines.unsafe(row.pgrid_get_value(5)));
					adjustment_dialog.dialog("open");
				}},
				{type: 'button', text: 'Remove', extra_class: 'picon picon-document-close', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_hrm', 'adjustment/delete', array('id' => '__title__'))); ?>},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'employee_adjustments',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc'
		};
		$("#p_muid_grid").pgrid(cur_defaults);
		$("#p_muid_adjustment_dialog [name=employee]").employeeselect();
	});

	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Date</th>
			<th>Description</th>
			<th>Employee</th>
			<th>Amount</th>
			<th>Comments</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->adjustments as $cur_adjustment) { ?>
		<tr title="<?php echo (int) $cur_adjustment->guid ?>">
			<td><?php echo format_date($cur_adjustment->date); ?></td>
			<td><?php echo htmlspecialchars($cur_adjustment->name); ?></td>
			<td><?php echo htmlspecialchars($cur_adjustment->employee->guid.': '.$cur_adjustment->employee->name); ?></td>
			<td>$<?php echo number_format($cur_adjustment->amount, 2, '.', ''); ?></td>
			<td><?php echo htmlspecialchars($cur_adjustment->comments); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div id="p_muid_adjustment_dialog" title="Employee Adjustment" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element">
			<label><span class="pf-label">Employee</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="employee" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Adjustment</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="name" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Date</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="effective_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Amount</span>
				<span class="pf-field">$ <input class="ui-widget-content ui-corner-all" type="text" size="5" name="amount" /></span></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Comments</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="comments" value="" /></label>
		</div>
	</form>
	<br />
</div>