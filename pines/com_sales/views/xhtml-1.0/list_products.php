<?php
/**
 * Lists products and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Products';
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newproduct')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'editproduct'); ?>'},
				<?php } if (gatekeeper('com_sales/editproduct')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_sales', 'editproduct', array('id' => '#title#')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteproduct')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deleteproduct', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'products',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/list_products", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#product_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="product_grid">
	<thead>
		<tr>
			<th>SKU</th>
			<th>Name</th>
			<th>Enabled</th>
			<th>Manufacturer</th>
			<th>Manufacturer SKU</th>
			<th>Pricing Method</th>
			<th>Unit Price</th>
			<th>Margin</th>
			<th>Floor</th>
			<th>Ceiling</th>
			<th>Tax Exempt</th>
			<th>Weight (lbs)</th>
			<th>RMA After (days)</th>
			<th>Serialized</th>
			<th>Discountable</th>
			<?php if ($pines->com_sales->com_customer) { ?>
			<th>Require Customer</th>
			<?php } ?>
			<th>Hide on Invoice</th>
			<th>Non-Refundable</th>
			<th>Additional Barcodes</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->products as $product) { ?>
		<tr title="<?php echo $product->guid; ?>">
			<td><?php echo $product->sku; ?></td>
			<td><?php echo $product->name; ?></td>
			<td><?php echo ($product->enabled ? 'Yes' : 'No'); ?></td>
			<td><?php echo $product->manufacturer->name; ?></td>
			<td><?php echo $product->manufacturer_sku; ?></td>
			<td><?php echo $product->pricing_method; ?></td>
			<td>$<?php echo $product->unit_price; ?></td>
			<td><?php echo $product->margin; ?>%</td>
			<td>$<?php echo $product->floor; ?></td>
			<td>$<?php echo $product->ceiling; ?></td>
			<td><?php echo ($product->tax_exempt ? 'Yes' : 'No'); ?></td>
			<td><?php echo $product->weight; ?></td>
			<td><?php echo $product->rma_after; ?></td>
			<td><?php echo ($product->serialized ? 'Yes' : 'No'); ?></td>
			<td><?php echo ($product->discountable ? 'Yes' : 'No'); ?></td>
			<?php if ($pines->com_sales->com_customer) { ?>
			<td><?php echo ($product->require_customer ? 'Yes' : 'No'); ?></td>
			<?php } ?>
			<td><?php echo ($product->hide_on_invoice ? 'Yes' : 'No'); ?></td>
			<td><?php echo ($product->non_refundable ? 'Yes' : 'No'); ?></td>
			<td><?php echo implode(', ', $product->additional_barcodes); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>