<?php
/**
 * Lists all of the cash counts.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Cash Counts';
if ($this->old)
	$this->title = 'Prior Cash Counts';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/cashcount/list'];
$pines->com_jstree->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// Group Tree
		var location = $("#p_muid_assign_dialog [name=location]");
		$("#p_muid_location_tree")
		.bind("select_node.jstree", function(e, data){
			location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
		})
		.bind("before.jstree", function (e, data){
			if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
				data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
		})
		.bind("loaded.jstree", function(e, data){
			var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
			if (!path.length) return;
			data.inst.open_node("#"+path.join(", #"), false, true);
		})
		.jstree({
			"plugins" : [ "themes", "json_data", "ui" ],
			"json_data" : {
				"ajax" : {
					"dataType" : "json",
					"url" : "<?php echo addslashes(pines_url('com_jstree', 'groupjson')); ?>"
				}
			},
			"ui" : {
				"select_limit" : 1
			}
		});

		var assign_dialog = $("#p_muid_assign_dialog");

		assign_dialog.find("form").submit(function(){
			assign_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		assign_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: "250px",
			buttons: {
				"Assign": function(){
					var assign_to = assign_dialog.find(":input[name=location]").val();
					var assign_type = assign_dialog.find(":input[name=count_type]").val();
					if (assign_to == "") {
						alert("Please select a group");
					} else {
						pines.post("<?php echo addslashes(pines_url('com_sales', 'cashcount/assign')); ?>", {
							count_type: assign_type,
							location: assign_to
						});
						assign_dialog.dialog("close");
					}
				}
			}
		});

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newcashcount')) { ?>
				{type: 'button', text: 'Cash-In', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/edit')); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/edit', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/editcashcount')) { ?>
				{type: 'button', text: 'Cash-Out', extra_class: 'picon picon-view-bank', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/cashout', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/skimcashcount')) { ?>
				{type: 'button', text: 'Skim', extra_class: 'picon picon-list-remove', url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/skim', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/depositcashcount')) { ?>
				{type: 'button', text: 'Deposit', extra_class: 'picon picon-list-add', url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/deposit', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/auditcashcount')) { ?>
				{type: 'button', text: 'Audit', extra_class: 'picon picon-document-edit-verify', url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/audit', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/approvecashcount')) { ?>
				{type: 'button', text: 'Review', extra_class: 'picon picon-checkbox', url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/approve', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/assigncashcount')) { ?>
				{type: 'button', text: 'Assign', extra_class: 'picon picon-view-task-add', selection_optional: true, click: function(e, rows){
					assign_dialog.dialog("open");
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletecashcount')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_sales', 'cashcount/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'cash_counts',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/cashcount/list", state: cur_state});
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
			<th>Location</th>
			<th>Created</th>
			<th>Cashed-In</th>
			<th>Cashed-Out</th>
			<th>Audits</th>
			<th>Skims</th>
			<th>Deposits</th>
			<th>Status</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->counts as $cur_count) { ?>
		<tr title="<?php echo $cur_count->guid; ?>">
			<td><?php echo $cur_count->guid; ?></td>
			<td><?php echo htmlspecialchars($cur_count->group->name); ?></td>
			<td><?php echo format_date($cur_count->p_cdate); ?></td>
			<td><?php echo $cur_count->final ? 'Yes' : 'No'; ?></td>
			<td><?php echo $cur_count->cashed_out ? 'Yes' : 'No'; ?></td>
			<td style="text-align: right;"><?php echo count($cur_count->audits); ?></td>
			<td style="text-align: right;"><?php echo count($cur_count->skims); ?></td>
			<td style="text-align: right;"><?php echo count($cur_count->deposits); ?></td>
			<td><?php switch ($cur_count->status) {
				case 'closed':
					echo 'Closed';
					break;
				case 'flagged':
					echo 'Flagged';
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
<div id="p_muid_assign_dialog" title="Assign a Cash Count" style="display: none;">
	<div class="pf-element">
		<label>
			<span class="pf-label">Count Type</span>
			<select class="ui-widget-content ui-corner-all" name="count_type">
				<option value="cash_count">Cash Count</option>
				<option value="cash_audit">Cash Audit</option>
				<option value="cash_skim">Cash Skim</option>
				<option value="cash_deposit">Cash Deposit</option>
			</select>
		</label>
	</div>
	<br />
	<div id="p_muid_location_tree"></div>
	<input type="hidden" name="location" />
</div>