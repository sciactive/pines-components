<?php
/**
 * Shows a list of employee totals.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Employee Summary ['.$this->location->name.']';
if ($this->descendents)
	$this->note = 'Including locations beneath '.$this->location->name;
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
?>
<style type="text/css">
	/* <![CDATA[ */
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
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var p_muid_notice;

	pines(function(){
		search_employees = function(){
			// Submit the form with all of the fields.
			pines.get("<?php echo addslashes(pines_url('com_reports', 'employeesummary')); ?>", {
				"location": location,
				"descendents": descendents,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

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
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'employee_summary',
						content: rows
					});
				}}
			]
		});

		employees_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'dateselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the date form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Date Selector\" />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 315,
						modal: true,
						open: function(){
							form.html(data);
						},
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
				}
			});
		};
		employees_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_reports', 'locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the location form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Location Selector\" />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 250,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
								form.dialog('close');
								search_employees();
							}
						}
					});
				}
			});
		};
	});
	// ]]>
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
				if ($cur_sale->status == 'voided')
					continue;
				if (!isset($totals[$cur_invoice->user->guid])){
					$totals[$cur_invoice->user->guid] = array(
						'employee' => $cur_invoice->user,
						'qty_sold' => 0,
						'qty_returned' => 0,
						'qty_net' => 0,
						'total_sold' => 0,
						'total_returned' => 0,
						'total_net' => 0,
						'adjustment' => 0,
						'cost' => 0,
						'profit' => 0,
						'commission' => 0
					);
				}
				if ($cur_invoice->has_tag('sale')) {
					$totals[$cur_invoice->user->guid]['qty_sold']++;
					$totals[$cur_invoice->user->guid]['qty_net']++;
					$totals[$cur_invoice->user->guid]['total_sold'] += $cur_invoice->total;
					$totals[$cur_invoice->user->guid]['total_net'] += $cur_invoice->total;
					foreach ($cur_invoice->products as $cur_item) {
						foreach ($cur_item['stock_entities'] as $cur_stock)
							$totals[$cur_invoice->user->guid]['cost'] += $cur_stock->cost;
					}
					foreach ($cur_invoice->user->commissions as $cur_commission) {
						if ($cur_commission['ticket']->guid == $cur_invoice->guid)
							$totals[$cur_invoice->user->guid]['commission'] += $cur_commission['amount'];
					}
				} elseif ($cur_invoice->has_tag('return')) {
					$totals[$cur_invoice->user->guid]['qty_returned']++;
					$totals[$cur_invoice->user->guid]['qty_net']--;
					$totals[$cur_invoice->user->guid]['total_returned'] += $cur_invoice->total;
					$totals[$cur_invoice->user->guid]['total_net'] -= $cur_invoice->total;
					foreach ($cur_invoice->user->commissions as $cur_commission) {
						if ($cur_commission['ticket']->guid == $cur_invoice->guid)
							$totals[$cur_invoice->user->guid]['commission'] -= $cur_commission['amount'];
					}
				}
			}
			foreach ($totals as $cur_total) {
				$cur_total['profit'] = ($cur_total['total_sold']-$cur_total['total_returned'])-$cur_total['cost'];
			?>
			<tr title="<?php echo $cur_total['employee']->guid; ?>">
				<td><?php echo $cur_total['employee']->name; ?></td>
				<td><?php echo $cur_total['qty_sold']; ?></td>
				<td><?php echo $cur_total['qty_returned']; ?></td>
				<td><?php echo $cur_total['qty_net']; ?></td>
				<td>$<?php echo number_format($cur_total['total_sold'], 2); ?></td>
				<td>$<?php echo number_format($cur_total['total_returned'], 2); ?></td>
				<td>$<?php echo number_format($cur_total['total_net'], 2); ?></td>
				<td>$<?php echo number_format($cur_total['adjustment'], 2); ?></td>
				<td>$<?php echo number_format($cur_total['cost'], 2); ?></td>
				<td>$<?php echo number_format($cur_total['profit'], 2); ?></td>
				<td>$<?php echo number_format($cur_total['commission'], 2); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>