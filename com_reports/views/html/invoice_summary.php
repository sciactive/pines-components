<?php
/**
 * Shows a list of all sales, returns and voids.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Invoice Summary ['.htmlspecialchars($this->location->name).']';
if ($this->descendents)
	$this->note = 'Including locations beneath '.htmlspecialchars($this->location->name);
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	.p_muid_invoice_actions button {
		padding: 0;
	}
	.p_muid_invoice_actions button .ui-button-text {
		padding: 0;
	}
	.p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	.return td {
		color: red;
	}
	.sale td {
		font-weight: bold;
	}
	.void td {
		font-style: italic;
	}
	#p_muid_grid .total {
		text-align: right;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var p_muid_notice;

	pines(function(){
		search_invoices = function(){
			// Submit the form with all of the fields.
			pines.get(<?php echo json_encode(pines_url('com_reports', 'invoicesummary')); ?>, {
				"location": location,
				"descendents": descendents,
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
		var location = "<?php echo (int) $this->location->guid ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var invoices_grid = $("#p_muid_grid").pgrid({
			pgrid_toolbar: true,
			pgrid_sortable: true,
			pgrid_sort_col: 3,
			pgrid_sort_ord: 'desc',
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){invoices_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){invoices_grid.date_form();}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'invoice_summary',
						content: rows
					});
				}}
			]
		});

		invoices_grid.date_form = function(){
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
								search_invoices();
							}
						}
					});
					pines.play();
				}
			});
		};
		invoices_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'locationselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
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
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
								form.dialog('close');
								search_invoices();
							}
						}
					});
					pines.play();
				}
			});
		};
	});
	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Type</th>
			<th>Date</th>
			<th>Location</th>
			<th>Customer</th>
			<th>Employee</th>
			<th>Total</th>
			<?php /* <th>Cost</th>
			<th>Profit</th> */ ?>
			<th>Status</th>
			<?php
			$payment_types = $pines->entity_manager->get_entities(
					array('class' => com_sales_payment_type),
					array('&',
						'tag' => array('com_sales', 'payment_type'),
						'data' => array('enabled', true)
					)
				);
			foreach ($payment_types as $cur_payment_type) { 
				echo '<th>'.$cur_payment_type->name.'</th>';
			} ?>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($this->invoices as $cur_invoice) {
			if ($cur_invoice->status == 'voided')
				$type = 'void';
			elseif ($cur_invoice->has_tag('return'))
				$type = 'return';
			else
				$type = 'sale';
			$total_cost = 0;
			foreach ($cur_invoice->products as $cur_item) {
				foreach ($cur_item['stock_entities'] as $cur_stock)
					$total_cost += $cur_stock->cost;
			}
		?>
		<tr title="<?php echo (int) $cur_invoice->customer->guid; ?>" class="<?php echo $type; ?>">
			<td><a href="<?php echo htmlspecialchars(pines_url('com_sales', ($cur_invoice->has_tag('return') ? 'return/receipt' : 'sale/receipt'), array('id' => $cur_invoice->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_invoice->id); ?></a></td>
			<td><?php echo ucwords($type); ?></td>
			<td><?php echo format_date($cur_invoice->p_cdate, 'full_sort'); ?></td>
			<td><?php echo htmlspecialchars($cur_invoice->group->name); ?></td>
			<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $cur_invoice->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_invoice->customer->name); ?></a></td>
			<td><?php echo htmlspecialchars($cur_invoice->user->name); ?></td>
			<td class="total">$<?php echo number_format($cur_invoice->subtotal, 2, '.', ''); ?></td>
			<?php /* <td class="total">$<?php echo number_format($total_cost, 2, '.', ''); ?></td>
			<td class="total">$<?php echo number_format(($cur_invoice->subtotal - $total_cost), 2, '.', ''); ?></td> */ ?>
			<td><?php echo ucwords($cur_invoice->status); ?></td>
			<?php
			foreach ($payment_types as $cur_payment_type) { 
				echo '<td>$';
				$pmt_total = 0;
				foreach ($cur_invoice->payments as $cur_payment) {
					if ($cur_payment_type->is($cur_payment['entity']))
						$pmt_total += $cur_payment['amount'];
				}
				echo number_format($pmt_total, 2, '.', '');
				echo '</td>';
			}
			?>
		</tr>
		<?php } ?>
	</tbody>
</table>