<?php
/**
 * Shows a list of all sales, returns and voids.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Invoice Summary ['.htmlspecialchars($this->location->name).']';
if ($this->descendants)
	$this->note = 'Including locations beneath '.htmlspecialchars($this->location->name);
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
$google_drive = false;
if (isset($pines->com_googledrive)) {
    $pines->com_googledrive->export_to_drive('csv');
    $google_drive = true;
} else {
    pines_log("Google Drive is not installed", 'notice');
}
?>
<style type="text/css">
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
	#p_muid_grid .return td {
		font-weight: bold;
	}
	#p_muid_grid .sale td {
		font-weight: normal;
	}
	#p_muid_grid .void td {
		font-weight: normal;
		font-style: italic;
	}
	#p_muid_grid .total {
		text-align: right;
	}
</style>
<script type="text/javascript">
	var p_muid_notice;
	pines(function(){
		search_invoices = function(){
			// Submit the form with all of the fields.
			pines.get(<?php echo json_encode(pines_url('com_reports', 'invoicesummary')); ?>, {
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
				}},
                                <?php // Need to check if Google Drive is installed
                                    if ($google_drive && !empty($pines->config->com_googledrive->client_id)) { ?>
                                        {type: 'button', title: 'Export to Google Drive', extra_class: 'picon drive-icon', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
                                        // First need to set the rows to which we want to export
                                        setRows(rows);
                                        // Then we have to check if we have permission to post to user's google drive
                                        checkAuth();
                                    }},
                                    <?php } elseif ($google_drive && empty($pines->config->com_googledrive->client_id)) { ?>
                                        {type: 'button', title: 'Export to Google Drive', extra_class: 'picon drive-icon', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
                                        // They have com_googledrive installed but didn't set the client id, so alert them on click
                                        alert('You need to set the CLIENT ID before you can export to Google Drive');
                                    }},
                                    <?php } ?>
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
								search_invoices();
							}
						}
					});
					pines.play();
				}
			});
		};
	});
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
		<tr title="<?php echo htmlspecialchars($cur_invoice->customer->guid); ?>" class="<?php echo $type; ?>">
			<td><a data-entity="<?php echo htmlspecialchars($cur_invoice->guid); ?>" data-entity-context="<?php echo $cur_invoice->has_tag('return') ? 'com_sales_return' : 'com_sales_sale'; ?>"><?php echo htmlspecialchars($cur_invoice->id); ?></a></td>
			<td><?php echo ucwords($type); ?></td>
			<td><?php echo htmlspecialchars(format_date($cur_invoice->p_cdate, 'full_sort')); ?></td>
			<td><a data-entity="<?php echo htmlspecialchars($cur_invoice->group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($cur_invoice->group->name); ?></a></td>
			<td><a data-entity="<?php echo htmlspecialchars($cur_invoice->customer->guid); ?>" data-entity-context="com_customer_customer"><?php echo htmlspecialchars($cur_invoice->customer->name); ?></a></td>
			<td><a data-entity="<?php echo htmlspecialchars($cur_invoice->user->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars($cur_invoice->user->name); ?></a></td>
			<td class="total">$<?php echo htmlspecialchars(number_format($cur_invoice->subtotal, 2, '.', '')); ?></td>
			<?php /* <td class="total">$<?php echo htmlspecialchars(number_format($total_cost, 2, '.', '')); ?></td>
			<td class="total">$<?php echo htmlspecialchars(number_format(($cur_invoice->subtotal - $total_cost), 2, '.', '')); ?></td> */ ?>
			<td><?php echo ucwords($cur_invoice->status); ?></td>
			<?php
			foreach ($payment_types as $cur_payment_type) { 
				echo '<td>$';
				$pmt_total = 0;
				foreach ($cur_invoice->payments as $cur_payment) {
					if ($cur_payment_type->is($cur_payment['entity']))
						$pmt_total += $cur_payment['amount'];
				}
				echo htmlspecialchars(number_format($pmt_total, 2, '.', ''));
				echo '</td>';
			}
			?>
		</tr>
		<?php } ?>
	</tbody>
</table>