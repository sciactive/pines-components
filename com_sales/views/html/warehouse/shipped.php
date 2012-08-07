<?php
/**
 * Displays shipped warehouse items.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Shipped Warehouse Orders';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_sales/warehouse/shipped']);
?>
<script type="text/javascript">
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/managestock')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', multi_select: true, url: <?php echo json_encode(pines_url('com_sales', 'stock/edit', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'shipped warehouse orders',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_sales/warehouse/shipped", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Date</th>
			<th>Product</th>
			<th>Serial</th>
			<th>Sale</th>
			<th>Sale Location</th>
			<th>Customer</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->sales as $sale) {
		foreach ($sale->products as $key => $cur_product) {
			// Filter non warehouse products.
			if ($cur_product['delivery'] != 'warehouse')
				continue;
			foreach ((array) $cur_product['shipped_entities'] as $skey => $cur_stock) {
				if ($cur_stock->in_array($cur_product['returned_stock_entities']))
					continue;
		?>
		<tr title="<?php echo (int) $cur_stock->guid ?>">
			<td><?php echo htmlspecialchars(format_date($sale->tender_date, 'full_sort')); ?></td>
			<td><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars("{$cur_product['entity']->sku} : {$cur_product['entity']->name}"); ?></a></td>
			<td><?php echo htmlspecialchars($cur_stock->serial); ?></td>
			<td><a data-entity="<?php echo htmlspecialchars($sale->guid); ?>" data-entity-context="com_sales_sale"><?php echo htmlspecialchars($sale->id); ?></a></td>
			<td><a data-entity="<?php echo htmlspecialchars($sale->group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars("{$sale->group->name} [{$sale->group->groupname}]"); ?></a></td>
			<td><a data-entity="<?php echo htmlspecialchars($sale->customer->guid); ?>" data-entity-context="com_customer_customer"><?php echo htmlspecialchars($sale->customer->name); ?></a></td>
		</tr>
	<?php } } } ?>
	</tbody>
</table>