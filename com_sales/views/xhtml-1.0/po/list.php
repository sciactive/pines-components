<?php
/**
 * Lists POs and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Purchase Orders';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/po/list'];
$errors = array();
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newpo')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'po/edit')); ?>'},
				<?php } if (gatekeeper('com_sales/editpo')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo addslashes(pines_url('com_sales', 'po/edit', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/completepo')) { ?>
				{type: 'separator'},
				{type: 'button', text: 'Complete', title: 'Mark it complete even with missing items.', extra_class: 'picon picon-checkbox', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_sales', 'po/complete', array('id' => '__title__'))); ?>', delimiter: ','},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletepo')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_sales', 'po/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'pos',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/po/list", state: cur_state});
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
			<th>PO Number</th>
			<th>Reference Number</th>
			<th>Vendor</th>
			<th>Destination</th>
			<th>Shipper</th>
			<th>ETA</th>
			<th>Committed</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->pos as $po) { ?>
		<tr title="<?php echo $po->guid; ?>">
			<td><?php echo htmlspecialchars($po->po_number); ?></td>
			<td><?php echo htmlspecialchars($po->reference_number); ?></td>
			<td><?php echo htmlspecialchars($po->vendor->name); ?></td>
			<td><?php echo htmlspecialchars("{$po->destination->name} [{$po->destination->groupname}]"); ?></td>
			<td><?php echo htmlspecialchars($po->shipper->name); ?></td>
			<td><?php echo ($po->eta ? format_date($po->eta, 'date_sort') : ''); ?></td>
			<td><?php echo $po->final ? 'Yes' : 'No'; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>