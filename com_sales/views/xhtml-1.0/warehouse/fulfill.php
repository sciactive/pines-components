<?php
/**
 * Displays pending warehouse sales and options to fulfill them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

pines_session();

$this->title = 'Pending Warehouse Sales';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/warehouse/fulfill'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Fulfill', extra_class: 'picon picon-view-task', double_click: true, url: '<?php echo addslashes(pines_url('com_sales', 'warehouse/fulfill', array('id' => '__title__'))); ?>'},
				<?php if (gatekeeper('com_sales/editsale')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', url: '<?php echo addslashes(pines_url('com_sales', 'sale/edit', array('id' => '__title__'))); ?>'},
				<?php } ?>
				{type: 'button', text: 'Receipt', extra_class: 'picon picon-document-print-preview', url: '<?php echo addslashes(pines_url('com_sales', 'sale/receipt', array('id' => '__title__'))); ?>'},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'warehouse_sales',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/warehouse/fulfill", state: cur_state});
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
			<th>Date</th>
			<th>Status</th>
			<th>User</th>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<th>Customer</th>
			<?php } ?>
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
			<td><?php echo htmlspecialchars($sale->id); ?></td>
			<td><?php echo format_date($sale->p_cdate); ?></td>
			<td><?php echo htmlspecialchars(ucwords($sale->status)); ?></td>
			<td><?php echo isset($sale->user->guid) ? htmlspecialchars("{$sale->user->name} [{$sale->user->username}]") : ''; ?></td>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<td><?php echo $sale->customer->guid ? htmlspecialchars("{$sale->customer->guid}: \"{$sale->customer->name}\"") : ''; ?></td>
			<?php } ?>
			<td><?php
			$number = 0;
			foreach ($sale->products as $cur_product) {
				$number += (int) $cur_product['quantity'];
			}
			echo $number; ?></td>
			<td><?php echo isset($sale->subtotal) ? '$'.number_format($sale->subtotal, 2) : ''; ?></td>
			<td><?php echo isset($sale->item_fees) ? '$'.number_format($sale->item_fees, 2) : ''; ?></td>
			<td><?php echo isset($sale->taxes) ? '$'.number_format($sale->taxes, 2) : ''; ?></td>
			<td><?php echo isset($sale->total) ? '$'.number_format($sale->total, 2) : ''; ?></td>
			<td><?php echo isset($sale->amount_tendered) ? '$'.number_format($sale->amount_tendered, 2) : ''; ?></td>
			<td><?php echo isset($sale->change) ? '$'.number_format($sale->change, 2) : ''; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>