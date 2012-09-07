<?php
/**
 * Shows a list of employee totals.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Employee Summary ['.htmlspecialchars($this->location->name).']';
if ($this->descendants)
	$this->note = 'Including locations beneath '.htmlspecialchars($this->location->name);
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
?>
<style type="text/css">
	.p_muid_employee_actions button {
		padding: 0;
	}
	.p_muid_employee_actions button .ui-button-text {
		padding: 0;
	}
	.p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#p_muid_grid th {
		text-align: center;
	}
	#p_muid_grid .total {
		text-align: right;
	}
</style>
<script type="text/javascript">
	var p_muid_notice;

	pines(function(){
		search_employees = function(){
			// Submit the form with all of the fields.
			pines.get(<?php echo json_encode(pines_url('com_reports', 'employeesummary')); ?>, {
				"location": location,
				"descendants": descendants,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = <?php echo $this->start_date ? json_encode(format_date($this->start_date, 'date_sort')) : '""'; ?>;
		var end_date = <?php echo $this->end_date ? json_encode(format_date($this->end_date - 1, 'date_sort')) : '""'; ?>;
		// Location Defaults
		var location = <?php echo json_encode("{$this->location->guid}"); ?>;
		var descendants = <?php echo $this->descendants ? 'true' : 'false'; ?>;

		var employees_grid = $("#p_muid_grid").pgrid({
			pgrid_toolbar: true,
			pgrid_sortable: true,
			pgrid_sort_col: 10,
			pgrid_sort_ord: 'desc',
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){employees_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){employees_grid.date_form();}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'employee_summary',
						content: rows
					});
				}}
			]
		});

		employees_grid.date_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'dateselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the date form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Date Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
								search_employees();
							}
						}
					});
					pines.play();
				}
			});
		};
		employees_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'locationselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendants": descendants},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the location form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Location Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendants]").attr('checked'))
									descendants = true;
								else
									descendants = false;
								form.dialog('close');
								search_employees();
							}
						}
					});
					pines.play();
				}
			});
		};
	});
</script>
<div class="pf-element pf-full-width">
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>Employee</th>
				<th># Sold</th>
				<th># Ref</th>
				<th># Net</th>
				<th>$ Sold</th>
				<th>$ Ref</th>
				<th>$ Net</th>
				<th>Adjustment</th>
				<th>Cost</th>
				<th>Profit</th>
				<th>Commission</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($this->invoices as $cur_invoice) {
				if ($cur_invoice->status == 'voided')
					continue;
				if ($cur_invoice->has_tag('sale')) {
					foreach ($cur_invoice->products as $cur_product) {
						if (!isset($totals[$cur_product['salesperson']->guid])){
							$totals[$cur_product['salesperson']->guid] = array(
								'employee' => $cur_product['salesperson'],
								'qty_sold' => 0,
								'qty_returned' => 0,
								'qty_net' => 0,
								'total_sold' => 0,
								'total_returned' => 0,
								'total_net' => 0,
								'adjustment' => 0,
								'cost' => 0,
								'profit' => 0,
								'commission' => 0,
								'counted' => array()
							);
							$commissions[$cur_product['salesperson']->guid] = $cur_product['salesperson']->commissions;
						}
						if (!in_array($cur_invoice->guid, $totals[$cur_product['salesperson']->guid]['counted'])) {
							$totals[$cur_product['salesperson']->guid]['qty_sold']++;
							$totals[$cur_product['salesperson']->guid]['qty_net']++;
							$totals[$cur_product['salesperson']->guid]['counted'][] = $cur_invoice->guid;
						}
						$totals[$cur_product['salesperson']->guid]['total_sold'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						$totals[$cur_product['salesperson']->guid]['total_net'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						foreach ($cur_product['stock_entities'] as $cur_stock)
							$totals[$cur_product['salesperson']->guid]['cost'] += $cur_stock->cost;
						foreach ((array) $commissions[$cur_product['salesperson']->guid] as $key => $cur_commission) {
							if ($cur_commission['ticket']->guid == $cur_invoice->guid) {
								$totals[$cur_product['salesperson']->guid]['commission'] += $cur_commission['amount'];
								unset($commissions[$cur_product['salesperson']->guid][$key]);
							}
						}
					}
				} elseif ($cur_invoice->has_tag('return')) {
					foreach ($cur_invoice->products as $cur_product) {
						if (!isset($totals[$cur_product['salesperson']->guid])){
							$totals[$cur_product['salesperson']->guid] = array(
								'employee' => $cur_product['salesperson'],
								'qty_sold' => 0,
								'qty_returned' => 0,
								'qty_net' => 0,
								'total_sold' => 0,
								'total_returned' => 0,
								'total_net' => 0,
								'adjustment' => 0,
								'cost' => 0,
								'profit' => 0,
								'commission' => 0,
								'counted' => array()
							);
							$commissions[$cur_product['salesperson']->guid] = $cur_product['salesperson']->commissions;
						}
						if (!in_array($cur_invoice->guid, $totals[$cur_product['salesperson']->guid]['counted'])) {
							$totals[$cur_product['salesperson']->guid]['qty_returned']++;
							$totals[$cur_product['salesperson']->guid]['qty_net']--;
							$totals[$cur_product['salesperson']->guid]['counted'][] = $cur_invoice->guid;
						}
						$totals[$cur_product['salesperson']->guid]['total_returned'] += ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						$totals[$cur_product['salesperson']->guid]['total_net'] -= ($cur_product['line_total'] - (float) $cur_product['specials_total']);
						foreach ((array) $commissions[$cur_product['salesperson']->guid] as $key => $cur_commission) {
							if ($cur_commission['ticket']->guid == $cur_invoice->guid) {
								$totals[$cur_product['salesperson']->guid]['commission'] += $cur_commission['amount'];
								unset($commissions[$cur_product['salesperson']->guid][$key]);
							}
						}
					}
				}
			}
			foreach ((array) $totals as $cur_total) {
				$cur_total['profit'] = ($cur_total['total_sold']-$cur_total['total_returned'])-$cur_total['cost'];
			?>
			<tr title="<?php echo htmlspecialchars($cur_total['employee']->guid); ?>">
				<td><?php echo htmlspecialchars($cur_total['employee']->name); ?></td>
				<td><?php echo htmlspecialchars($cur_total['qty_sold']); ?></td>
				<td><?php echo htmlspecialchars($cur_total['qty_returned']); ?></td>
				<td><?php echo htmlspecialchars($cur_total['qty_net']); ?></td>
				<td class="total">$<?php echo htmlspecialchars(number_format($cur_total['total_sold'], 2, '.', '')); ?></td>
				<td class="total">$<?php echo htmlspecialchars(number_format($cur_total['total_returned'], 2, '.', '')); ?></td>
				<td class="total">$<?php echo htmlspecialchars(number_format($cur_total['total_net'], 2, '.', '')); ?></td>
				<td class="total">$<?php echo htmlspecialchars(number_format($cur_total['adjustment'], 2, '.', '')); ?></td>
				<td class="total">$<?php echo htmlspecialchars(number_format($cur_total['cost'], 2, '.', '')); ?></td>
				<td class="total">$<?php echo htmlspecialchars(number_format($cur_total['profit'], 2, '.', '')); ?></td>
				<td class="total">$<?php echo htmlspecialchars(number_format($cur_total['commission'], 2, '.', '')); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>