<?php
/**
 * Lists products and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = ($this->enabled ? '' : 'Disabled ').'Products';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/product/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newproduct')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'product/edit')); ?>'},
				<?php } if (gatekeeper('com_sales/editproduct')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo addslashes(pines_url('com_sales', 'product/edit', array('id' => '__title__'))); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteproduct')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_sales', 'product/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } if ($this->enabled) { ?>
				{type: 'button', text: 'Disabled', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'product/list', array('enabled' => 'false'))); ?>'},
				<?php } else { ?>
				{type: 'button', text: 'Enabled', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'product/list')); ?>'},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/product/list", state: cur_state});
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
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->products as $product) {
		$costs = $vendors = array();
		foreach($product->vendors as $cur_vendor) {
			$vendors[] = $cur_vendor['entity']->name;
			$costs[] = '$'.$pines->com_sales->round($cur_vendor['cost'], true);
		}
	?>
		<tr title="<?php echo $product->guid; ?>">
			<td><?php echo htmlspecialchars($product->sku); ?></td>
			<td><?php echo htmlspecialchars($product->name); ?></td>
			<td style="text-align: right;">$<?php echo htmlspecialchars($pines->com_sales->round($product->unit_price, true)); ?></td>
			<td style="text-align: right;"><?php echo htmlspecialchars(implode(', ', $costs)); ?></td>
			<td><?php echo htmlspecialchars(implode(', ', $vendors)); ?></td>
			<td><?php echo htmlspecialchars($product->manufacturer->name); ?></td>
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
		</tr>
	<?php } ?>
	</tbody>
</table>