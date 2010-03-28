<?php
/**
 * Lists POs and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Purchase Orders';
$errors = array();
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newpo')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'editpo'); ?>'},
				<?php } if (gatekeeper('com_sales/editpo')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_sales', 'editpo', array('id' => '#title#')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletepo')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deletepo', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/list_pos", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#po_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="po_grid">
	<thead>
		<tr>
			<th>PO Number</th>
			<th>Reference Number</th>
			<th>Vendor</th>
			<th>Destination</th>
			<th>Shipper</th>
			<th>ETA</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->pos as $po) { ?>
		<tr title="<?php echo $po->guid; ?>">
			<td><?php echo $po->po_number; ?></td>
			<td><?php echo $po->reference_number; ?></td>
			<td><?php echo $po->vendor->name; ?></td>
			<td><?php echo "{$po->destination->name} [{$po->destination->groupname}]"; ?></td>
			<td><?php echo $po->shipper->name; ?></td>
			<td><?php echo ($po->eta ? date('Y-m-d', $po->eta) : 'None'); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>