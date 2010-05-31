<?php
/**
 * Lists all of the countsheets.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Countsheets';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/list_countsheets'];
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
					url : "<?php echo pines_url('com_jstree', 'groupjson'); ?>"
				}
			},
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function() {
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
					if (assign_to == "") {
						alert("Please select a group");
					} else {
						pines.post("<?php echo pines_url('com_sales', 'assigncountsheet'); ?>", {
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
				<?php if (gatekeeper('com_sales/newcountsheet')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon_16x16_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'editcountsheet'); ?>'},
				<?php } if (gatekeeper('com_sales/editcountsheet')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon_16x16_document-edit', double_click: true, url: '<?php echo pines_url('com_sales', 'editcountsheet', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_sales/approvecountsheet')) { ?>
				{type: 'button', text: 'Review', extra_class: 'picon picon_16x16_checkbox', url: '<?php echo pines_url('com_sales', 'approvecountsheet', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_sales/assigncountsheet')) { ?>
				{type: 'button', text: 'Assign', extra_class: 'picon picon_16x16_view-task-add', selection_optional: true, click: function(e, rows){
					assign_dialog.dialog("open");
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletecountsheet')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon_16x16_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deletecountsheet', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'picon picon_16x16_document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon_16x16_document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon_16x16_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
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
			<td><?php echo format_date($countsheet->p_cdate); ?></td>
			<td><?php echo format_date($countsheet->p_mdate); ?></td>
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
<div id="assign_dialog" title="Assign a Countsheet to" style="display: none;">
	<div id="location_tree"></div>
	<input type="hidden" name="location" />
</div>