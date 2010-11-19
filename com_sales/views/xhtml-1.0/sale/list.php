<?php
/**
 * Lists sales and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales';
$pines->com_pgrid->load();
$pines->com_jstree->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/sale/list'];
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var submit_url = "<?php echo addslashes(pines_url('com_sales', 'sale/list')); ?>";
		var submit_search = function(){
			// Submit the form with all of the fields.
			pines.post(submit_url, {
				"location": location,
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
		
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){sale_grid.location_form();}},
				{type: 'button', text: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){sale_grid.date_form();}},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/newsale')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'sale/edit')); ?>'},
				<?php } if (gatekeeper('com_sales/editsale')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', url: '<?php echo addslashes(pines_url('com_sales', 'sale/edit', array('id' => '__title__'))); ?>'},
				<?php } ?>
				{type: 'button', text: 'Receipt', extra_class: 'picon picon-document-print-preview', double_click: true, url: '<?php echo addslashes(pines_url('com_sales', 'sale/receipt', array('id' => '__title__'))); ?>'},
				<?php if (gatekeeper('com_sales/newreturnwsale')) { ?>
				{type: 'button', text: 'Return', extra_class: 'picon picon-edit-undo', url: '<?php echo addslashes(pines_url('com_sales', 'sale/return', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/swap')) { ?>
				{type: 'button', text: 'Swap', extra_class: 'picon picon-document-swap', click: function(e, row){
					sale_grid.swap_form($(row).attr("title"));
				}},
				<?php } if (gatekeeper('com_sales/voidsale') || gatekeeper('com_sales/voidownsale')) { ?>
				{type: 'button', text: 'Void', extra_class: 'picon picon-edit-delete-shred', confirm: true, url: '<?php echo addslashes(pines_url('com_sales', 'sale/void', array('id' => '__title__'))); ?>'},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletesale')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_sales', 'sale/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'sales',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/sale/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var sale_grid = $("#p_muid_grid").pgrid(cur_options);

		sale_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/dateselect')); ?>",
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
							"Update": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
								submit_search();
							}
						}
					});
				}
			});
		};
		sale_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location},
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
							"Update": function(){
								location = form.find(":input[name=location]").val();
								form.dialog('close');
								submit_search();
							}
						}
					});
				}
			});
		};
		sale_grid.swap_form = function(sale_id){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/swap')); ?>",
				type: "POST",
				dataType: "html",
				data: {"id": sale_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the swap form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Swap Item [Sale: "+sale_id+"]\" />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 400,
						width: 425,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Swap Items": function(){
								var swap_item = form.find(":input[name=swap_item]:checked").val();
								var new_serial = form.find(":input[name=new_serial]").val();
								if (swap_item == "") {
									alert('Please specify the item you want to swap.');
								} else if (new_serial == "") {
									alert('Please specify the new item serial number.');
								} else {
									form.dialog('close');
									// Submit the swap request.
									pines.post("<?php echo addslashes(pines_url('com_sales', 'sale/swap')); ?>", {
										"id": sale_id,
										"swap_item": swap_item,
										"new_serial": new_serial.trim()
									});
								}
							}
						}
					});
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