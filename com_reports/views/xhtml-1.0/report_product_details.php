<?php
/**
 * Shows a product details report.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Product Details Report ['.$this->location->name.']';
$this->note = format_date($this->date[0], 'date_short').' - '.format_date($this->date[1], 'date_short');

$pines->icons->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_issues'];
?>
<style type="text/css" >
	/* <![CDATA[ */
	.p_muid_issue_actions button {
		padding: 0;
	}
	.p_muid_issue_actions button .ui-button-text {
		padding: 0;
	}
	.p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	.p_muid_return td {
		color: red;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var p_muid_notice;

	pines(function(){
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'product_details',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_sort_col: 2,
			pgrid_sort_ord: "desc"
		};
		var cur_options = $.extend(cur_defaults);
		$("#p_muid_grid").pgrid(cur_options);
	});
	// ]]>
</script>
<div class="pf-element pf-full-width">
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>ID</th>
				<th>Date</th>
				<th>Transaction</th>
				<th>Delivery</th>
				<th>Location</th>
				<th>Employee</th>
				<th>Customer</th>
				<th>SKU</th>
				<th>Serial</th>
				<th>Product</th>
				<th>Unit Cost</th>
				<th>Price</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($this->transactions as $cur_tx) {
				if (empty($cur_tx->products))
					continue;
				if ($cur_tx->has_tag('return')) {
					$class = 'class="p_muid_return"';
					$tx_type = 'RETURN';
				} else {
					$class = '';
					if ($cur_tx->status == 'voided')
						$tx_type = 'VOID';
					elseif ($cur_tx->status == 'invoiced')
						$tx_type = 'INVOICE';
					else
						$tx_type = 'SALE';
				}
				foreach ($cur_tx->products as $cur_item) { ?>
				<tr <?php echo $class; ?>>
					<td><?php echo $tx_type.$cur_tx->id; ?></td>
					<td><?php echo format_date($cur_tx->p_cdate); ?></td>
					<td><?php echo $tx_type; //htmlspecialchars($cur_tx->status); ?></td>
					<td><?php echo htmlspecialchars($cur_item['delivery']); ?></td>
					<td><?php echo htmlspecialchars($cur_tx->group->name); ?></td>
					<td><?php echo htmlspecialchars($cur_tx->user->name); ?></td>
					<td><?php echo htmlspecialchars($cur_tx->customer->name); ?></td>
					<td><?php echo htmlspecialchars($cur_item['sku']); ?></td>
					<td><?php echo htmlspecialchars($cur_item['serial']); ?></td>
					<td><?php echo htmlspecialchars($cur_item['entity']->name); ?></td>
					<td>$<?php echo round($cur_item['entity']->vendors[0]['cost'], 2); ?></td>
					<td>$<?php echo round($cur_item['price'], 2); ?></td>
				</tr>
			<?php }
			} ?>
		</tbody>
	</table>
</div>