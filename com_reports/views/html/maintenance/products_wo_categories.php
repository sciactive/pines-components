<?php
/**
 * Shows a list of products without categories.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Products Without Categories';
$this->note = 'That is, <em>enabled</em> products without <em>enabled</em> categories.';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	pines(function(){
		<?php if ($this->fix_cat_count) { ?>
		pines.notice("Also, while this report was running, I took the liberty of fixing <?php echo (int) $this->fix_cat_count; ?> broken product references I found.", "Cleanup");
		<?php } ?>
		$("#p_muid_grid").pgrid({
			pgrid_toolbar: true,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/editproduct')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_sales', 'product/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteproduct')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_sales', 'product/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'products_without_categories',
						content: rows
					});
				}}
			]
		});
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>SKU</th>
			<th>Name</th>
			<th>Price</th>
			<th>Cost(s)</th>
			<th>Vendor(s)</th>
			<th>Manufacturer</th>
			<th>Manufacturer SKU</th>
			<th>Stock Type</th>
			<th>Serialized</th>
			<th>Discountable</th>
			<th>Additional Barcodes</th>
			<th>Receipt Description</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->products as $product) {
		$costs = $vendors = array();
		foreach($product->vendors as $cur_vendor) {
			$vendors[] = '<a href="'.htmlspecialchars(pines_url('com_sales','vendor/edit',array('id'=> $cur_vendor['entity']->guid))).'" onclick="window.open(this.href); return false;">'.htmlspecialchars($cur_vendor['entity']->name).'</a>';
			$costs[] = '$'.$pines->com_sales->round($cur_vendor['cost'], true);
		}
	?>
		<tr title="<?php echo (int) $product->guid ?>">
			<td><?php echo htmlspecialchars($product->sku); ?></td>
			<td><?php echo htmlspecialchars($product->name); ?></td>
			<td style="text-align: right;">$<?php echo htmlspecialchars($pines->com_sales->round($product->unit_price, true)); ?></td>
			<td style="text-align: right;"><?php echo htmlspecialchars(implode(', ', $costs)); ?></td>
			<td><?php echo implode(', ', $vendors); ?></td>
			<td><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'manufacturer/edit', array('id' => $product->manufacturer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($product->manufacturer->name); ?></a></td>
			<td><?php echo htmlspecialchars($product->manufacturer_sku); ?></td>
			<td><?php switch ($product->stock_type) {
				case 'non_stocked':
					echo 'Non Stocked';
					break;
				case 'stock_optional':
					echo 'Stock Optional';
					break;
				case 'regular_stock':
					echo 'Regular Stock';
					break;
				default:
					echo 'Unrecognized';
					break;
			} ?></td>
			<td><?php echo ($product->serialized ? 'Yes' : 'No'); ?></td>
			<td><?php echo ($product->discountable ? 'Yes' : 'No'); ?></td>
			<td><?php echo htmlspecialchars(implode(', ', $product->additional_barcodes)); ?></td>
			<td><?php echo htmlspecialchars($product->receipt_description); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>