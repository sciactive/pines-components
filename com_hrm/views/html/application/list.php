<?php
/**
 * Lists applications and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employment Applications';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_hrm/application/list']);
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_hire_dialog ul, #p_muid_reject_dialog ul {
		padding-left: 30px;
		list-style: circle;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var selected_ids;
		var hire_dialog = $("#p_muid_hire_dialog");
		var reject_dialog = $("#p_muid_reject_dialog");

		hire_dialog.find("form").submit(function(){
			hire_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		hire_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Hire": function(){
					pines.post(<?php echo json_encode(pines_url('com_hrm', 'application/hire')); ?>, {
						items: selected_ids,
						date: $("#p_muid_hire_dialog [name=effective_date]").val()
					});
					hire_dialog.dialog("close");
				}
			}
		});

		reject_dialog.find("form").submit(function(){
			reject_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		reject_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Reject": function(){
					pines.post(<?php echo json_encode(pines_url('com_hrm', 'application/reject')); ?>, {
						items: selected_ids
					});
					hire_dialog.dialog("close");
				}
			}
		});

		$("[name=effective_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_hrm', 'application/edit')); ?>},
				<?php if (gatekeeper('com_hrm/addemployee')) { ?>
					{type: 'button', text: 'Hire', extra_class: 'picon picon-get-hot-new-stuff', multi_select: true, click: function(e, rows){
					selected_ids = "";
					var list = '<ul>';
					rows.each(function(){
						if (selected_ids != "")
							selected_ids += ",";
						selected_ids += $(this).attr('title');
						list += '<li>'+pines.safe($(this).pgrid_get_value(2))+'</li>';
					});
					list += '</ul>';
					hire_dialog.find("div.dialog_info").html(list);
					hire_dialog.dialog("open");
					}},
					{type: 'button', text: 'Reject', extra_class: 'picon picon-dialog-cancel', multi_select: true, click: function(e, rows){
					selected_ids = "";
					var list = '<ul>';
					rows.each(function(){
						if (selected_ids != "")
							selected_ids += ",";
						selected_ids += $(this).attr('title');
						list += '<li>'+pines.safe($(this).pgrid_get_value(2))+'</li>';
					});
					list += '</ul>';
					reject_dialog.find("div.dialog_info").html(list);
					reject_dialog.dialog("open");
					}},
				<?php } if (gatekeeper('com_hrm/editapplication')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-properties', url: <?php echo json_encode(pines_url('com_hrm', 'application/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				{type: 'button', text: 'View', extra_class: 'picon picon-document-preview', double_click: true, url: <?php echo json_encode(pines_url('com_hrm', 'application/view', array('id' => '__title__'))); ?>},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'employment_applications',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_hrm/employee/list", state: cur_state});
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
			<th>Date</th>
			<th>Name</th>
			<th>Position</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->applications as $cur_application) { ?>
		<tr title="<?php echo (int) $cur_application->guid ?>">
			<td><?php echo htmlspecialchars(format_date($cur_application->p_cdate)); ?></td>
			<td><?php echo htmlspecialchars($cur_application->name); ?></td>
			<td><?php echo htmlspecialchars($cur_application->position); ?></td>
			<td><?php echo htmlspecialchars(ucwords($cur_application->status)); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div id="p_muid_hire_dialog" title="Hire" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element">
			<div class="pf-element dialog_info"></div>
			<label><span class="pf-label">Effective Date</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="effective_date" value="<?php echo htmlspecialchars(format_date(time(), 'date_sort')); ?>" /></label>
		</div>
	</form>
	<br />
</div>
<div id="p_muid_reject_dialog" title="Reject" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element dialog_info"></div>
	</form>
	<br />
</div>