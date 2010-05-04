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
$pines->com_pgrid->load();
$pines->com_jstree->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// Group Tree
		var location = $("#assign_dialog [name=location]");
		$("#location_tree").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_sales', 'groupjson'); ?>"
				}
			},
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function(NODE, REF_NODE, TYPE, TREE_OBJ) {
					return false;
				}
			}
		});

		var assign_dialog = $("#assign_dialog");

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
						pines.post("<?php echo pines_url('com_sales', 'assigncashcount'); ?>", {
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
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'editcashcount'); ?>'},
				<?php } if (gatekeeper('com_sales/editcashcount')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_sales', 'editcashcount', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_sales/auditcashcount')) { ?>
				{type: 'button', text: 'Audit', extra_class: 'icon picon_16x16_apps_logviewer', url: '<?php echo pines_url('com_sales', 'auditcashcount', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_sales/skimcashcount')) { ?>
				{type: 'button', text: 'Skim', extra_class: 'icon picon_16x16_emblems_emblem-downloads', url: '<?php echo pines_url('com_sales', 'skimcashcount', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_sales/depositcashcount')) { ?>
				{type: 'button', text: 'Deposit', extra_class: 'icon picon_16x16_apps_system-file-manager', url: '<?php echo pines_url('com_sales', 'depositcashcount', array('id' => '__title__')); ?>'},
				<?php }if (gatekeeper('com_sales/approvecashcount')) { ?>
				{type: 'button', text: 'Review', extra_class: 'icon picon_16x16_stock_generic_stock_mark', url: '<?php echo pines_url('com_sales', 'approvecashcount', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_sales/assigncashcount')) { ?>
				{type: 'button', text: 'Assign', extra_class: 'icon picon_16x16_emblems_emblem-shared', selection_optional: true, click: function(e, rows){
					assign_dialog.dialog("open");
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletecashcount')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deletecashcount', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/list_cashcounts", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#cashcount_grid").pgrid(cur_options);
	});
	// ]]>
</script>
<table id="cashcount_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Location</th>
			<th>Created</th>
			<th>Committed</th>
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
			<td><?php echo $cur_count->group->name; ?></td>
			<td><?php echo format_date($cur_count->p_cdate); ?></td>
			<td><?php echo ($cur_count->final) ? 'Yes' : 'No'; ?></td>
			<td><?php echo count($cur_count->audits); ?></td>
			<td><?php echo count($cur_count->skims); ?></td>
			<td><?php echo count($cur_count->deposits); ?></td>
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
<div id="assign_dialog" title="Assign a Cash Count" style="display: none;">
	<div class="pf-element">
		<label>
			<span class="pf-label">Count Type</span>
			<select class="ui-widget-content" name="count_type">
				<option value="cash_count">Cash Count</option>
				<option value="cash_audit">Cash Audit</option>
				<option value="cash_skim">Cash Skim</option>
				<option value="cash_deposit">Cash Deposit</option>
			</select>
		</label>
	</div>
	<br />
	<div id="location_tree"></div>
	<input type="hidden" name="location" />
</div>