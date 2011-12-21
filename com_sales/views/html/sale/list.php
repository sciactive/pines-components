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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales';
$pines->com_pgrid->load();
$pines->com_jstree->load();
if ($pines->config->com_sales->per_item_salesperson && gatekeeper('com_sales/swapsalesrep'))
	$pines->com_hrm->load_employee_select();
if ($pines->config->com_sales->autocomplete_product)
	$pines->com_sales->load_product_select();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_sales/sale/list']);
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var submit_url = <?php echo json_encode(pines_url('com_sales', 'sale/list')); ?>;
		var submit_search = function(){
			// Submit the form with all of the fields.
			pines.get(submit_url, {
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

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){sale_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){sale_grid.date_form();}},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/newsale')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_sales', 'sale/edit')); ?>},
				<?php } if (gatekeeper('com_sales/editsale')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', url: <?php echo json_encode(pines_url('com_sales', 'sale/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				{type: 'button', text: 'Receipt', extra_class: 'picon picon-document-print-preview', double_click: true, url: <?php echo json_encode(pines_url('com_sales', 'sale/receipt', array('id' => '__title__'))); ?>},
				<?php if (gatekeeper('com_sales/newreturnwsale')) { ?>
				{type: 'button', text: 'Return', extra_class: 'picon picon-edit-undo', url: <?php echo json_encode(pines_url('com_sales', 'sale/return', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_sales/swapsale')) { ?>
				{type: 'button', text: 'Swap', extra_class: 'picon picon-document-swap', click: function(e, row){
					sale_grid.swap_form($(row).attr("title"));
				}},
				<?php } if (gatekeeper('com_sales/changeproduct')) { ?>
				{type: 'button', text: 'Change', title: 'Change products on warehouse sales.', extra_class: 'picon picon-package-x-generic', click: function(e, row){
					sale_grid.change_form(row.pgrid_get_value(1), row.attr("title"));
				}},
				<?php } if (gatekeeper('com_sales/voidsale') || gatekeeper('com_sales/voidownsale')) { ?>
				{type: 'button', text: 'Void', extra_class: 'picon picon-edit-delete-shred', confirm: true, url: <?php echo json_encode(pines_url('com_sales', 'sale/void', array('id' => '__title__'))); ?>},
				<?php } if ($pines->config->com_sales->per_item_salesperson && gatekeeper('com_sales/swapsalesrep')) { ?>
				{type: 'button', title: 'Change Salesperson', extra_class: 'picon picon-edit-find-user', click: function(e, row){
					sale_grid.salesrep_form(row.pgrid_get_value(1), row.attr("title"));
				}},
				<?php } if (gatekeeper('com_sales/overrideowner')) { ?>
				{type: 'button', title: 'Override Owner', extra_class: 'picon picon-resource-group', click: function(e, row){
					sale_grid.owner_form($(row).attr("title"));
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletesale')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_sales', 'sale/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_sales/sale/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var sale_grid = $("#p_muid_grid").pgrid(cur_options);

		sale_grid.date_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/dateselect')); ?>,
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
					pines.play();
				}
			});
		};
		sale_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/locationselect')); ?>,
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
							"Update": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
								form.dialog('close');
								submit_search();
							}
						}
					});
					pines.play();
				}
			});
		};
		sale_grid.owner_form = function(sale_id){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/overrideowner')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": sale_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the override form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Override Sale\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: 425,
						modal: true,
						open: function(){
							$(".salesperson_box", form).employeeselect();
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Override": function(){
								form.dialog('close');
								// Submit the override request.
								$.ajax({
									url: <?php echo json_encode(pines_url('com_sales', 'overrideowner')); ?>,
									type: "POST",
									dataType: "html",
									data: {
										"id": sale_id,
										"location": form.find(":input[name=location]").val(),
										"user": form.find(":input[name=user]").val()
									},
									error: function(XMLHttpRequest, textStatus){
										pines.error("An error occured while trying to override the sale:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
									},
									success: function(data){
										if (data == "false")
											alert("Could not override the sale.");
										else {
											alert("The sale has been overridden.");
											pines.get(submit_url, {
												"location": location,
												"descendents": descendents,
												"all_time": all_time,
												"start_date": start_date,
												"end_date": end_date
											});
										}
									}
								});
							}
						}
					});
					pines.play();
				}
			});
		};
		sale_grid.salesrep_form = function(sale_id, guid){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/salesrep')); ?>,
				type: "POST",
				dataType: "html",
				data: {
					"id": guid,
					"type": "sale"
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the salesrep form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Swap Salesperson [Sale: "+pines.safe(sale_id)+"]\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: 425,
						modal: true,
						open: function() {
							$(".salesperson_box", form).employeeselect();
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Update": function(){
								var swap_item = form.find(":input:checked[name=swap_item]").val();
								var salesperson = form.find(":input[name=salesperson]").val();
								if (swap_item == "") {
									alert("Please specify the item(s) you want to swap.");
								} else if (salesperson == "") {
									alert("Please specify the new salesperson.");
								} else {
									form.dialog('close');
									// Submit the salesperson swap request.
									$.ajax({
										url: <?php echo json_encode(pines_url('com_sales', 'swapsalesrep')); ?>,
										type: "POST",
										dataType: "html",
										data: {
											"id": guid,
											"type": "sale",
											"swap_item": swap_item,
											"salesperson": salesperson
										},
										error: function(XMLHttpRequest, textStatus){
											pines.error("An error occured while trying to swap the salesperson:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
										},
										success: function(data){
											if (data == "false")
												alert("Could not change the salesperson.");
											else
												alert("Successfully changed the salesperson.");
										}
									});
								}
							}
						}
					});
					pines.play();
				}
			});
		};
		sale_grid.swap_form = function(sale_id){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/swap')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": sale_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the swap form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Swap Item [Sale: "+pines.safe(sale_id)+"]\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: 425,
						modal: true,
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
									pines.post(<?php echo json_encode(pines_url('com_sales', 'sale/swap')); ?>, {
										"id": sale_id,
										"swap_item": swap_item,
										"new_serial": new_serial.trim()
									});
								}
							}
						}
					});
					pines.play();
				}
			});
		};
		sale_grid.change_form = function(sale_id, guid){
			if (!confirm("\
Are you sure you want to change a product on this sale? Doing so may have some\n\
serious consenquences. Product actions and commissions are not considered when\n\
changing products. Any difference in price and any discounts are also ignored.\n\
\"Customer Required\" and \"One Per Invoice\" restrictions are also ignored.\n\
Only continue if you are fully aware of the results of changing a product."))
				return;
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/changeproduct')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": guid},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the change product form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Change Product [Sale: "+pines.safe(sale_id)+"]\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: 425,
						modal: true,
						open: function() {
							$(".product_box", form).productselect();
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Change Product": function(){
								var product = form.find(":input[name=product]:checked").val();
								var new_product = form.find(":input[name=new_product]").val();
								if (product == "") {
									alert('Please specify the product you want to change.');
								} else if (new_product == "") {
									alert('Please specify the new product.');
								} else {
									form.dialog('close');
									// Submit the product change request.
									pines.post(<?php echo json_encode(pines_url('com_sales', 'sale/changeproduct')); ?>, {
										"id": guid,
										"product": product,
										"new_product": new_product
									});
								}
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
			<th>Date</th>
			<th>Status</th>
			<th>User</th>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<th>Customer</th>
			<?php } ?>
			<th>Products</th>
			<th>Subtotal</th>
			<th>Specials</th>
			<th>Item Fees</th>
			<th>Tax</th>
			<th>Total</th>
			<th>Tendered</th>
			<th>Change Given</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->sales as $sale) { ?>
		<tr title="<?php echo (int) $sale->guid ?>">
			<td><?php echo htmlspecialchars($sale->id); ?></td>
			<td><?php echo htmlspecialchars(format_date($sale->p_cdate)); ?></td>
			<td><?php echo htmlspecialchars(ucwords($sale->status)); ?></td>
			<td><?php echo isset($sale->user->guid) ? htmlspecialchars("{$sale->user->name} [{$sale->user->username}]") : ''; ?></td>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<td><a href="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/edit', array('id' => $sale->customer->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo $sale->customer->guid ? htmlspecialchars("{$sale->customer->guid}: \"{$sale->customer->name}\"") : ''; ?></a></td>
			<?php } ?>
			<td><?php
			$number = 0;
			foreach ($sale->products as $cur_product) {
				$number += (int) $cur_product['quantity'];
			}
			echo $number; ?></td>
			<td><?php echo isset($sale->subtotal) ? '$'.htmlspecialchars(number_format($sale->subtotal, 2)) : ''; ?></td>
			<td><?php echo isset($sale->total_specials) ? '$'.htmlspecialchars(number_format($sale->total_specials, 2)) : ''; ?></td>
			<td><?php echo isset($sale->item_fees) ? '$'.htmlspecialchars(number_format($sale->item_fees, 2)) : ''; ?></td>
			<td><?php echo isset($sale->taxes) ? '$'.htmlspecialchars(number_format($sale->taxes, 2)) : ''; ?></td>
			<td><?php echo isset($sale->total) ? '$'.htmlspecialchars(number_format($sale->total, 2)) : ''; ?></td>
			<td><?php echo isset($sale->amount_tendered) ? '$'.htmlspecialchars(number_format($sale->amount_tendered, 2)) : ''; ?></td>
			<td><?php echo isset($sale->change) ? '$'.htmlspecialchars(number_format($sale->change, 2)) : ''; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>