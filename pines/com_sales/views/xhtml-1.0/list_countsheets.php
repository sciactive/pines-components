<?php
/**
 * Lists all of the countsheets.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Countsheets';
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var employee_search = function(search_string){
			var loader;
			$.ajax({
				url: "<?php echo pines_url("com_hrm", "employeesearch"); ?>",
				type: "POST",
				dataType: "json",
				data: {"q": search_string},
				beforeSend: function(){
					loader = pines.alert('Searching for employees...', 'Employee Search', 'icon picon_16x16_animations_throbber', {pnotify_hide: false, pnotify_history: false});
					employee_table.pgrid_get_all_rows().pgrid_delete();
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to find employee:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (!data) {
						alert("No employees were found that matched the query.");
						return;
					}
					employee_dialog.dialog('open');
					employee_table.pgrid_add(data);
				}
			});
		};

		var assign_dialog = $("#assign_dialog");
		var employee_box = $("#employee");
		var employee_search_box = $("#employee_search");
		var employee_search_button = $("#employee_search_button");
		var employee_table = $("#employee_table");
		var employee_dialog = $("#employee_dialog");

		assign_dialog.find("form").submit(function(){
			assign_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		assign_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: "350px",
			buttons: {
				"Done": function(){
					var assign_to = assign_dialog.find(":input[name=employee]").val();
					if (assign_to == "No Employee Selected") {
						alert("Please select an employee");
					} else {
						pines.post("<?php echo pines_url('com_sales', 'assigncountsheet'); ?>", {
							employee: assign_to
						});
						assign_dialog.dialog("close");
					}
				}
			}
		});

		employee_search_box.keydown(function(eventObject){
			if (eventObject.keyCode == 13) {
				employee_search(this.value);
				return false;
			}
		});
		employee_search_button.click(function(){
			employee_search(employee_search_box.val());
		});

		employee_table.pgrid({
			pgrid_paginate: false,
			pgrid_multi_select: false,
			pgrid_double_click: function(){
				employee_dialog.dialog('option', 'buttons').Done();
			}
		});

		employee_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			buttons: {
				"Done": function(){
					var rows = employee_table.pgrid_get_selected_rows().pgrid_export_rows();
					if (!rows[0]) {
						alert("Please select an employee.");
						return;
					} else {
						var employee = rows[0];
					}
					employee_box.val(employee.key+": \""+employee.values[0]+" "+employee.values[1]+"\"");
					employee_search_box.val("");
					employee_dialog.dialog('close');
				}
			}
		});

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newcountsheet')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'editcountsheet'); ?>'},
				<?php } if (gatekeeper('com_sales/editcountsheet')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_sales', 'editcountsheet', array('id' => '#title#')); ?>'},
				<?php } if (gatekeeper('com_sales/approvecountsheet')) { ?>
				{type: 'button', text: 'Review', extra_class: 'icon picon_16x16_stock_generic_stock_mark', url: '<?php echo pines_url('com_sales', 'approvecountsheet', array('id' => '#title#')); ?>'},
				<?php } if (gatekeeper('com_sales/assigncountsheet')) { ?>
				{type: 'button', text: 'Assign', extra_class: 'icon picon_16x16_emblems_emblem-shared', selection_optional: true, click: function(e, rows){
					assign_dialog.dialog("open");
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletecountsheet')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deletecountsheet', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'countsheets',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/list_countsheets", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#countsheet_grid").pgrid(cur_options);
	});
	// ]]>
</script>
<table id="countsheet_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Created By</th>
			<th>Created</th>
			<th>Modified</th>
			<th>Committed</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->countsheets as $countsheet) { ?>
		<tr title="<?php echo $countsheet->guid; ?>">
			<td><?php echo $countsheet->guid; ?></td>
			<td><?php echo $countsheet->creator->name; ?></td>
			<td><?php echo pines_date_format($countsheet->p_cdate); ?></td>
			<td><?php echo pines_date_format($countsheet->p_mdate); ?></td>
			<td><?php echo $countsheet->final ? 'Yes' : 'No'; ?></td>
			<td><?php switch ($countsheet->status) {
				case 'approved':
					echo 'Approved';
					break;
				case 'declined':
					echo 'Declined';
					break;
				case 'info_requested':
					echo 'Info Requested';
					break;
				case 'pending':
					echo 'Pending';
					break;
				default:
					echo 'Unrecognized';
					break;
			} ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div id="assign_dialog" title="Assign a Countsheet" style="display: none;">
	<input class="ui-widget-content" type="text" id="employee" name="employee" size="24" onfocus="this.blur();" value="No Employee Selected" /><br />
	<input class="ui-widget-content" type="text" id="employee_search" name="employee_search" size="24" /><button type="button" id="employee_search_button"><span class="picon_16x16_actions_system-search" style="padding-left: 16px; background-repeat: no-repeat;"> Search</span></button>
</div>
<div id="employee_dialog" title="Pick an Employee">
	<table id="employee_table">
		<thead>
			<tr>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Job Title</th>
				<th>Email</th>
				<th>City</th>
				<th>State</th>
				<th>Zip</th>
				<th>Cell Phone</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>----------------------</td>
				<td>----------------------</td>
				<td>----------------------</td>
				<td>----------------------</td>
				<td>----------------------</td>
				<td>----------------------</td>
				<td>----------------------</td>
				<td>----------------------</td>
			</tr>
		</tbody>
	</table>
	<br style="clear: both; height: 1px;" />
</div>