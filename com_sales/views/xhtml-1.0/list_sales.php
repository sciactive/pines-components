<?php
/**
 * Lists sales and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales';
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'editsale'); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', url: '<?php echo pines_url('com_sales', 'editsale', array('id' => '#title#')); ?>'},
				{type: 'button', text: 'Receipt', extra_class: 'icon picon_16x16_actions_document-print-preview', double_click: true, url: '<?php echo pines_url('com_sales', 'receiptsale', array('id' => '#title#')); ?>'},
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deletesale', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'sales',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 'col_1',
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_sales/list_sales", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#sale_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="sale_grid">
	<thead>
		<tr>
			<th>GUID</th>
			<th>Date</th>
			<th>Status</th>
			<th>User</th>
			<th>Customer</th>
			<th>Products</th>
			<th>Subtotal</th>
			<th>Item Fees</th>
			<th>Tax</th>
			<th>Total</th>
			<th>Tendered</th>
			<th>Change Given</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->sales as $sale) { ?>
		<tr title="<?php echo $sale->guid; ?>">
			<td><?php echo $sale->guid; ?></td>
			<td><?php echo date('Y-m-d', $sale->p_cdate); ?></td>
			<td><?php echo ucwords($sale->status); ?></td>
			<td><?php $user = user::factory($sale->uid);
			echo is_null($user->guid) ? '' : "{$user->name} [{$user->username}]"; ?></td>
			<td><?php echo htmlentities($sale->customer->guid ? "{$sale->customer->guid}: \"{$sale->customer->name}\"" : ''); ?></td>
			<td><?php if (is_array($sale->products)) {
				$number = 0;
				foreach ($sale->products as $cur_product) {
					$number += (int) $cur_product['quantity'];
				}
				echo $number;
			} else {
				echo '0';
			} ?></td>
			<td><?php echo $sale->subtotal; ?></td>
			<td><?php echo $sale->item_fees; ?></td>
			<td><?php echo $sale->taxes; ?></td>
			<td><?php echo $sale->total; ?></td>
			<td><?php echo $sale->amount_tendered; ?></td>
			<td><?php echo $sale->change; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>