<?php
/**
 * Provides a form for the user to edit a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($this->entity->guid)) {
	$this->title = 'New Sale';
} elseif ($this->entity->status == 'quoted') {
	$this->title = 'Quoted Sale ['.htmlspecialchars($this->entity->id).']';
} elseif ($this->entity->status == 'invoiced') {
	$this->title = 'Invoiced Sale ['.htmlspecialchars($this->entity->id).']';
} elseif ($this->entity->status == 'paid') {
	$this->title = 'Paid Sale ['.htmlspecialchars($this->entity->id).']';
} elseif ($this->entity->status == 'voided') {
	$this->title = 'Voided Sale ['.htmlspecialchars($this->entity->id).']';
}
$this->note = 'Use this form to edit a sale.';
$pines->com_pgrid->load();
if ($pines->config->com_sales->com_customer)
	$pines->com_customer->load_customer_select();
if ($pines->config->com_sales->per_item_salesperson)
	$pines->com_hrm->load_employee_select();
if ($pines->config->com_sales->autocomplete_product)
	$pines->com_sales->load_product_select();
// TODO: After a sale is invoiced, don't calculate totals, just show what's saved.
if ($pines->config->com_sales->com_esp) {
	$esp_product = com_sales_product::factory((int) $pines->config->com_esp->esp_product);
	if (!isset($esp_product->guid))
		$esp_product = null;
}
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<script type="text/javascript">
		// <![CDATA[

		pines(function(){
			var products = $("#p_muid_products");
			var products_table = $("#p_muid_products_table");
			var payments_table = $("#p_muid_payments_table");
			var payments = $("#p_muid_payments");
			<?php if ($pines->config->com_sales->com_esp) { ?>
			var esp_rate = <?php echo (float) $pines->config->com_esp->esp_rate; ?>;
			<?php } if ($pines->config->com_sales->com_customer) { ?>
			var require_customer = false;
			<?php } ?>

			// Number of decimal places to round to.
			var dec = <?php echo (int) $pines->config->com_sales->dec; ?>;
<?php
			$taxes_percent = array();
			$taxes_flat = array();
			foreach ($this->tax_fees as $cur_tax_fee) {
				foreach($cur_tax_fee->locations as $cur_location) {
					if (!$_SESSION['user']->in_group($cur_location))
						continue;
					if ($cur_tax_fee->type == 'percentage') {
						$taxes_percent[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
						break;
					} elseif ($cur_tax_fee->type == 'flat_rate') {
						$taxes_flat[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
						break;
					}
				}
			}
			$drawer_kickers = array();
			foreach ($this->payment_types as $cur_payment_type) {
				if ($cur_payment_type->kick_drawer)
					$drawer_kickers[] = $cur_payment_type->guid;
			}
?>
			var taxes_percent = JSON.parse("<?php echo addslashes(json_encode($taxes_percent)) ?>");
			var taxes_flat = JSON.parse("<?php echo addslashes(json_encode($taxes_flat)) ?>");
			var drawer_kickers = JSON.parse("<?php echo addslashes(json_encode($drawer_kickers)); ?>");
			var status = JSON.parse("<?php echo addslashes(json_encode($this->entity->status)); ?>");

			var round_to_dec = function(value, as_string){
				var rnd = Math.pow(10, dec);
				var mult = value * rnd;
				value = gaussianRound(mult);
				value /= rnd;
				if (as_string)
					value = value.toFixed(dec);
				return (value);
			};

			var gaussianRound = function(x){
				var absolute = Math.abs(x);
				var sign     = x == 0 ? 0 : (x < 0 ? -1 : 1);
				var floored  = Math.floor(absolute);
				if (absolute - floored != 0.5) {
					return Math.round(absolute) * sign;
				}
				if (floored % 2 == 1) {
					// Closest even is up.
					return Math.ceil(absolute) * sign;
				}
				// Closest even is down.
				return floored * sign;
			};

			<?php if ($pines->config->com_sales->com_customer && $this->entity->status != 'invoiced' && $this->entity->status != 'paid' && $this->entity->status != 'voided') { ?>
			$("#p_muid_customer").customerselect();
			<?php } if ($pines->config->com_sales->per_item_salesperson) { ?>
			$("#p_muid_salesperson").employeeselect();
			<?php } ?>

			<?php if ($this->entity->status == 'invoiced' || $this->entity->status == 'paid' || $this->entity->status == 'voided') { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
				pgrid_hidden_cols: [10],
				pgrid_paginate: false,
				pgrid_toolbar: false
			});
			<?php } else { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
				pgrid_hidden_cols: [10],
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: '',
						title: 'Select a Product by Category',
						extra_class: 'picon picon-view-list-tree',
						selection_optional: true,
						click: function(){
							category_dialog.dialog("open");
						}
					},
					{
						type: 'text',
						title: 'Enter a Product SKU or Barcode',
						load: function(textbox){
							textbox.attr("id", "p_muid_product_code_box");
							var select = function(code){
								if (code == "") {
									alert("Please enter a product code.");
									return;
								}
								textbox.val("");
								var loader;
								$.ajax({
									url: "<?php echo addslashes(pines_url('com_sales', 'product/search')); ?>",
									type: "POST",
									dataType: "json",
									data: {"code": code},
									beforeSend: function(){
										loader = $.pnotify({
											pnotify_title: 'Product Search',
											pnotify_text: 'Retrieving product from server...',
											pnotify_notice_icon: 'picon picon-throbber',
											pnotify_nonblock: true,
											pnotify_hide: false,
											pnotify_history: false
										});
									},
									complete: function(){
										loader.pnotify_remove();
									},
									error: function(XMLHttpRequest, textStatus){
										pines.error("An error occured while trying to lookup the product code:\n"+XMLHttpRequest.status+": "+textStatus);
									},
									success: function(data){
										if (!data) {
											alert("No product was found with the code "+code+".");
											return;
										}
										add_product(data);
									}
								});
							};
							<?php if ($pines->config->com_sales->autocomplete_product) { ?>
							textbox.productselect({select: function(event, ui){select(ui.item.value); return false;}});
							<?php } ?>
							textbox.keydown(function(e){
								if (e.keyCode == 13)
									select(textbox.val());
							});
						}
					},
					{type: 'separator'},
					{
						type: 'button',
						text: 'Serial',
						extra_class: 'picon picon-view-barcode',
						double_click: true,
						click: function(e, rows){
							var product = rows.data("product");
							if (!product.serialized) {
								alert("This product isn't serialized.");
								return;
							}
							if (rows.pgrid_get_value(4) == "warehouse") {
								alert("This product is set to warehouse delivery. Warehouse items don't need serials until delivery.");
								return;
							}
							var serial = rows.pgrid_get_value(3);
							serial_box.val(serial);
							var buttons = {
								"Done": function(){
									serial = serial_box.val();
									if (serial == "") {
										alert("Please provide a serial number.");
										return;
									}
									rows.pgrid_set_value(3, serial);
									update_products();
									serial_dialog.dialog("close");
								}
							};
							$("#p_muid_serial_dialog_warehouse").hide();
							serial_dialog
							.dialog("option", "title", "Provide Serial for "+product.name)
							.dialog("option", "buttons", buttons)
							.dialog("open");
						}
					},
					{
						type: 'button',
						text: 'Delivery',
						extra_class: 'picon picon-mail-send',
						multi_select: true,
						click: function(e, rows){
							var any_non_stocked = false;
							rows.each(function(){
								var product = $(this).data("product");
								if (product.stock_type == "non_stocked")
									any_non_stocked = true;
							});
							if (any_non_stocked) {
								alert("Delivery options are not available for non stocked items. Please deselect any non stocked items.");
								return;
							}
							delivery_select.find("input[value="+rows.eq(0).pgrid_get_value(4)+"]").attr("checked", "true");
							delivery_select.find("input").button("refresh");
							delivery_dialog.dialog("open");
						}
					},
					{
						type: 'button',
						text: 'Qty',
						extra_class: 'picon picon-document-multiple',
						double_click: true,
						click: function(e, rows){
							var product = rows.data("product");
							if (product.serialized) {
								alert("This product is serialized.");
								return;
							}
							if (product.one_per_ticket) {
								alert("Only one of this product is allowed per ticket.");
								return;
							}
							var qty = rows.pgrid_get_value(5);
							do {
								qty = prompt("Please enter a quantity:", qty);
							} while ((parseInt(qty) < 1 || isNaN(parseInt(qty))) && qty != null);
							if (qty != null) {
								rows.pgrid_set_value(5, parseInt(qty));
								update_products();
							}
						}
					},
					{
						type: 'button',
						text: 'Price',
						extra_class: 'picon picon-office-chart-line',
						click: function(e, rows){
							var product = rows.data("product");
							if (product.pricing_method != "variable") {
								alert("The selected product does not allow variable pricing.");
								return;
							}
							var price = rows.pgrid_get_value(6);
							price = parseFloat(prompt("Enter a new price:", price));
							if (!isNaN(price)) {
								if (product.floor > 0 && price < product.floor) {
									alert("The minimum price for the selected product is "+product.floor+".");
									return;
								}
								if (product.ceiling > 0 && price > product.ceiling) {
									alert("The maximum price for the selected product is "+product.ceiling+".");
									return;
								}
								rows.pgrid_set_value(6, price);
								update_products();
							}
						}
					},
					<?php if (gatekeeper('com_sales/discountstock')) { ?>
					{
						type: 'button',
						text: 'Discount',
						extra_class: 'picon picon-go-down',
						click: function(e, rows){
							var product = rows.data("product");
							if (!product.discountable) {
								alert("The selected product is not discountable.")
								return;
							}
							var discount = rows.pgrid_get_value(7);
							do {
								discount = prompt("Enter an amount($#.##) or a percent (#.##%) to discount each unit:", discount);
							} while ((!discount.match(/^(\$-?\d+(\.\d+)?)|(-?\d+(\.\d+)?%)$/)) && discount != null);
							if (discount != null) {
								rows.pgrid_set_value(7, discount);
								update_products();
							}
						}
					},
					<?php } if ($pines->config->com_sales->per_item_salesperson) { ?>
					{
						type: 'button',
						title: 'Salesperson',
						extra_class: 'picon picon-edit-find-user',
						multi_select: false,
						click: function(e, rows){
							salesperson_form(rows);
						}
					},
					<?php } ?>
					{type: 'separator'},
					<?php if ($pines->config->com_sales->com_esp) { ?>
					{
						type: 'button',
						text: 'ESP',
						extra_class: 'picon picon-security-high',
						multi_select: false,
						click: function(e, rows){
							<?php if (isset($esp_product->guid)) { ?>
							// Add an ESP item to the product table.
							$.each(rows, function(){
								var esp_id = '<?php echo uniqid(); ?>';
								var insured_item = $(this);
								var insured_guid = insured_item.attr('title');
								if (insured_item.pgrid_get_value(10) != '') {
									alert('There is already an ESP for this item');
									return;
								} else if (insured_guid == <?php echo (int) $esp_product->guid; ?>) {
									alert('This item is an ESP, it does not need to be insured');
									return;
								}
								<?php if ($pines->config->com_esp->round_up) { ?>
								var esp_price = (insured_item.pgrid_get_value(6) * esp_rate).toFixed(2).replace(/\d\.\d{2}/, '9.99');
								<?php } else { ?>
								var esp_price = (insured_item.pgrid_get_value(6) * esp_rate).toFixed(2);
								<?php } if ($pines->config->com_esp->esp_max > 0) { ?>
								esp_price = parseFloat(esp_price);
								esp_price = Math.min(esp_price, <?php echo json_encode((float) $pines->config->com_esp->esp_max); ?>).toFixed(2);
								<?php }
								$fees_percent = array();
								$fees_flat = array();
								foreach ((array) $esp_product->additional_tax_fees as $cur_tax_fee) {
									if (!$cur_tax_fee->enabled)
										continue;
									if ($cur_tax_fee->type == 'percentage') {
										$fees_percent[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
									} elseif ($cur_tax_fee->type == 'flat_rate') {
										$fees_flat[] = array('name' => $cur_tax_fee->name, 'rate' => $cur_tax_fee->rate);
									}
								}

								$json_struct = (object) array(
									'guid' => $esp_product->guid,
									'name' => $esp_product->name,
									'sku' => $esp_product->sku,
									'stock_type' => $esp_product->stock_type,
									'pricing_method' => $esp_product->pricing_method,
									'unit_price' => $esp_product->unit_price,
									'margin' => $esp_product->margin,
									'floor' => $esp_product->floor,
									'ceiling' => $esp_product->ceiling,
									'tax_exempt' => $esp_product->tax_exempt,
									'return_checklists' => array(),
									'serialized' => $esp_product->serialized,
									'discountable' => $esp_product->discountable,
									'require_customer' => $esp_product->require_customer,
									'one_per_ticket' => $esp_product->one_per_ticket,
									'non_refundable' => $esp_product->non_refundable,
									'fees_percent' => $fees_percent,
									'fees_flat' => $fees_flat
								);

								foreach ((array) $esp_product->return_checklists as $cur_return_checklist) {
									if (!$cur_return_checklist->enabled)
										continue;
									$json_struct->return_checklists[] = array('guid' => $cur_return_checklist->guid, 'label' => $cur_return_checklist->label, 'conditions' => (array) $cur_return_checklist->conditions);
								}
								?>
								var product_data = <?php echo json_encode($json_struct); ?>;
								product_data.unit_price = esp_price;
								product_data.esp = esp_id;
								add_product(product_data, function(){
									insured_item.pgrid_set_value(10, esp_id);
									update_products();
								});
							});
							update_products();
							<?php } else { ?>
							alert("No ESP product was found.");
							<?php } ?>
						}
					},
					{type: 'separator'},
					<?php } ?>
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'picon picon-edit-delete',
						multi_select: true,
						click: function(e, rows){
							<?php if ($pines->config->com_sales->com_esp) { ?>
							// Find any deserted ESP/ESP IDs
							var deserted = [];
							$.each(rows, function(){
								var cur_id = $(this).pgrid_get_value(10);
								var deserter = $.inArray(cur_id, deserted);
								if (deserter != -1)
									deserted.remove(deserter);
								else
									deserted.push(cur_id);
							});
							// Remove any deserted ESP/ESP IDs
							if (deserted.length > 0) {
								$.each(products_table.pgrid_get_all_rows(), function(){
									var cur_item = $(this);
									if ($.inArray(cur_item.pgrid_get_value(10), deserted) != -1) {
										if (cur_item.attr('title') == '<?php echo $esp_product->guid; ?>') {
											// An insured item has been removed
											cur_item.pgrid_delete();
										} else {
											// An ESP has been removed
											cur_item.pgrid_set_value(10, '');
										}
									}
								});
							}
							<?php } ?>
							rows.pgrid_delete();
							update_products();
						}
					}
				]
			});
			var add_product = function(data, success){
				if (data.one_per_ticket) {
					var cur_products = products_table.pgrid_get_all_rows().pgrid_export_rows();
					var pass = true;
					$.each(cur_products, function(){
						if (parseInt(this.key) == data.guid) {
							alert("Only one of this product is allowed per ticket.");
							pass = false;
						}
					});
					if (!pass)
						return;
				}
				data.salesperson = "<?php echo addslashes($_SESSION['user']->guid.': '.$_SESSION['user']->name);?>";
				var serial = "";
				if (data.serialized) {
					var buttons = {
						"Done": function(){
							serial = serial_box.val();
							if (serial == "") {
								alert("Please provide a serial number.");
								return;
							}
							products_table.pgrid_add([{key: data.guid, values: [data.sku, data.name, serial, 'in-store', 1, data.unit_price, "", "", "", data.esp, data.salesperson]}], function(){
								var cur_row = $(this);
								cur_row.data("product", data);
							});
							update_products();
							serial_dialog.dialog("close");
							if (success)
								success();
						},
						"Warehouse Item": function(){
							products_table.pgrid_add([{key: data.guid, values: [data.sku, data.name, serial, 'warehouse', 1, data.unit_price, "", "", "", data.esp, data.salesperson]}], function(){
								var cur_row = $(this);
								cur_row.data("product", data);
							});
							update_products();
							serial_dialog.dialog("close");
							if (success)
								success();
						}
					};
					if (data.stock_type == "stock_optional") {
						$("#p_muid_serial_dialog_warehouse").show();
					} else {
						buttons = {"Done": buttons.Done};
						$("#p_muid_serial_dialog_warehouse").hide();
					}
					serial_dialog.dialog("option", "title", "Provide Serial for "+data.name)
					.dialog("option", "buttons", buttons)
					.dialog("open");
					serial_box.val("");
					return;
				}
				products_table.pgrid_add([{key: data.guid, values: [data.sku, data.name, serial, 'in-store', 1, data.unit_price, "", "", "", data.esp, data.salesperson]}], function(){
					var cur_row = $(this);
					cur_row.data("product", data);
				});
				update_products();
				if (success)
					success();
			};
			// Delivery Dialog
			var delivery_select = $("#p_muid_delivery_select").children("div").buttonset().end()
			.delegate("input", "click", function() {
				var rows = products_table.pgrid_get_selected_rows();
				if (!rows)
					return;
				var delivery = $(this).val();
				if (delivery == "warehouse") {
					rows.each(function(){
						var cur_row = $(this);
						var product = cur_row.data("product");
						if (product.stock_type != "stock_optional") {
							alert("Warehouse sales are only allowed on stock optional items, and the item, "+product.name+", is not stock optional.");
							return;
						}
						cur_row.pgrid_set_value(3, "");
						cur_row.pgrid_set_value(4, delivery);
					});
				} else {
					rows.each(function(){
						var cur_row = $(this);
						var product = cur_row.data("product");
						if (product.serialized && cur_row.pgrid_get_value(3) == "") {
							var serial = "";
							while (!serial) {
								serial = prompt("The item, "+product.name+", is serialized. Please provide the serial:");
								if (serial == null)
									return;
							}
						}
						cur_row.pgrid_set_value(3, serial);
						cur_row.pgrid_set_value(4, delivery);
					});
				}
				update_products();
				delivery_dialog.dialog('close');
			});
			var delivery_dialog = $("#p_muid_delivery_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				width: 450,
				modal: true,
				close: function(){
					$("#p_muid_product_code_box").focus();
				}
			});
			var serial_dialog = $("#p_muid_serial_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				width: 450,
				modal: true,
				close: function(){
					$("#p_muid_product_code_box").focus();
					serial_box.val("");
				},
				open: function(){
					serial_box.focus().select();
				}
			});
			var serial_box = $("#p_muid_serial_number").keypress(function(e){
				if (e.keyCode == 13) {
					serial_dialog.dialog("option", "buttons").Done();
					return false;
				}
			});
			// Category Grid
			var category_grid = $("#p_muid_category_grid").pgrid({
				pgrid_hidden_cols: [1],
				pgrid_sort_col: 1,
				pgrid_sort_ord: "asc",
				pgrid_child_prefix: "ch_",
				pgrid_paginate: false,
				pgrid_view_height: "300px",
				pgrid_multi_select: false,
				pgrid_double_click: function(e, row){
					category_dialog.dialog("option", "buttons").Done();
				}
			});
			// Category Dialog
			var category_dialog = $("#p_muid_category_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				open: function() {
					category_grid.pgrid_get_selected_rows().pgrid_deselect_rows();
				},
				buttons: {
					'Done': function() {
						var row = category_grid.pgrid_get_selected_rows();
						if (!row) {
							alert("Please select a category.");
							return;
						}
						category_products_grid.pgrid_get_all_rows().pgrid_delete();
						var loader;
						$.ajax({
							url: "<?php echo addslashes(pines_url('com_sales', 'category/products')); ?>",
							type: "POST",
							dataType: "json",
							data: {"id": row.attr("title")},
							beforeSend: function(){
								loader = $.pnotify({
									pnotify_title: 'Product Search',
									pnotify_text: 'Retrieving products from server...',
									pnotify_notice_icon: 'picon picon-throbber',
									pnotify_nonblock: true,
									pnotify_hide: false,
									pnotify_history: false
								});
							},
							complete: function(){
								loader.pnotify_remove();
							},
							error: function(XMLHttpRequest, textStatus){
								pines.error("An error occured while trying to lookup the products:\n"+XMLHttpRequest.status+": "+textStatus);
							},
							success: function(data){
								if (!data || !data[0]) {
									alert("No products were returned.");
									return;
								}
								$.each(data, function(){
									var product = this;
									category_products_grid.pgrid_add([{key: this.guid, values: [this.name, this.sku]}], function(){
										$(this).data("product", product);
									});
								});
								category_products_dialog.dialog("open");
							}
						});
					}
				}
			});
			// Category Products Grid
			var category_products_grid = $("#p_muid_category_products_grid").pgrid({
				pgrid_sort_col: 1,
				pgrid_sort_ord: "asc",
				pgrid_view_height: "300px",
				pgrid_multi_select: false,
				pgrid_double_click: function(){
					category_products_dialog.dialog("option", "buttons").Done();
				}
			});
			// Category Products Dialog
			var category_products_dialog = $("#p_muid_category_products_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 800,
				open: function() {
					category_products_grid.pgrid_get_selected_rows().pgrid_deselect_rows();
				},
				buttons: {
					'Done': function() {
						var data = category_products_grid.pgrid_get_selected_rows().data("product");
						if (!data) {
							alert("Please select a product.");
							return;
						}
						add_product(data);
						category_products_dialog.dialog('close');
						category_dialog.dialog('close');
					}
				}
			});
			<?php if ($pines->config->com_sales->per_item_salesperson) { ?>
			// Salesperson Form
			var salesperson_dialog = $("#p_muid_salesperson_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 450,
				open: function() {
					$("#p_muid_salesperson").val("");
				}
			});
			var salesperson_form = function(row){
				salesperson_dialog.dialog("option", "buttons", {
					'Done': function(){
						var salesperson = $("#p_muid_salesperson").val();
						if (salesperson == "") {
							salesperson = "<?php echo addslashes($_SESSION['user']->guid.': '.$_SESSION['user']->name);?>";
						} else if (!salesperson.match(/^\d+: .+$/)) {
							alert("Please select a salesperson using the dropdown menu.");
							return;
						}
						row.pgrid_set_value(11, salesperson);
						row.pgrid_deselect_rows();
						salesperson_dialog.dialog('close');
						update_products();
					}
				});
				salesperson_dialog.dialog('open');
			};
			<?php }
			} ?>

			<?php if (!$pines->config->com_sales->per_item_salesperson) { ?>
			products_table.pgrid_import_state({pgrid_hidden_cols: [10, 11]});
			<?php } ?>

			// Load the data for any existing products.
			var loader;
			products_table.pgrid_get_all_rows().each(function(){
				if (!loader)
					loader = $.pnotify({
						pnotify_title: 'Loading Products',
						pnotify_text: 'Retrieving product information from server...',
						pnotify_notice_icon: 'picon picon-throbber',
						pnotify_nonblock: true,
						pnotify_hide: false,
						pnotify_history: false
					});
				var cur_row = $(this);
				var cur_export = cur_row.pgrid_export_rows();
				var cur_guid = cur_export[0].key;
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_sales', 'product/search')); ?>",
					type: "POST",
					async: false,
					dataType: "json",
					data: {"code": cur_guid, "useguid": true},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to lookup a product:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (!data) {
							alert("No product was found with the id "+cur_guid+".");
							return;
						}
						cur_row.data("product", data);
					}
				});
			});
			if (loader)
				loader.pnotify_remove();

			<?php if ($this->entity->status == 'paid' || $this->entity->status == 'voided') { ?>
			payments_table.pgrid({
				pgrid_view_height: "150px",
				pgrid_paginate: false,
				pgrid_footer: false,
				pgrid_toolbar: false
			});
			<?php } else { ?>
			payments_table.pgrid({
				pgrid_view_height: "150px",
				pgrid_paginate: false,
				pgrid_footer: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Data',
						extra_class: 'picon picon-view-form',
						click: function(e, rows){
							payments_table.data_form(rows);
						}
					},
					{
						type: 'button',
						text: 'Amount',
						extra_class: 'picon picon-office-chart-line',
						multi_select: true,
						double_click: true,
						click: function(e, rows){
							// TODO: Minimums, maximums
							get_amount(function(amount){
								rows.each(function(){
									var cur_row = $(this);
									var cur_status = cur_row.pgrid_get_value(3);
									if (cur_status == "approved" || cur_status == "declined" || cur_status == "tendered") {
										alert("Payments cannot be changed if they have been approved, declined, or tendered.");
										return;
									}
									cur_row.pgrid_set_value(2, amount);
								});
								update_payments();
							});
						}
					},
					{type: 'separator'},
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'picon picon-edit-delete',
						multi_select: true,
						click: function(e, rows){
							rows.each(function(){
								var cur_row = $(this);
								var cur_status = cur_row.pgrid_get_value(3);
								if (cur_status == "approved" || cur_status == "declined" || cur_status == "tendered") {
									alert("Payments cannot be removed if they have been approved, declined, or tendered.");
									return;
								}
								cur_row.pgrid_delete();
							});
							update_payments();
						}
					}
				]
			});

			payments_table.data_form = function(row){
				var payment_data = row.data("payment_data");
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_sales', 'forms/payment')); ?>",
					type: "POST",
					dataType: "html",
					data: {"name": payment_data.processing_type, "id": $("#p_muid_form [name=id]").val(), "customer": $("#p_muid_customer").val(), "type": "sale"},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retreive the data form:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (data == "")
							return;
						var form = $("<div title=\"Data for "+row.pgrid_get_value(1)+" Payment\"></div>");
						form.dialog({
							bgiframe: true,
							autoOpen: true,
							modal: true,
							width: 640,
							open: function(){
								form.html('<form method="post" action="">'+data+"</form><br />");
								form.find("form").submit(function(){
									form.dialog('option', 'buttons').Done();
									return false;
								});
								if (payment_data.data) {
									$.each(payment_data.data, function(i, val){
										form.find(":input:not(:radio, :checkbox)[name="+val.name+"]").val(val.value);
										form.find(":input:radio[name="+val.name+"][value="+val.value+"]").attr("checked", "checked");
										if (val.value == "")
											form.find(":input:checkbox[name="+val.name+"]").removeAttr("checked");
										else
											form.find(":input:checkbox[name="+val.name+"][value="+val.value+"]").attr("checked", "checked");
									});
								}
							},
							close: function(){
								form.remove();
							},
							buttons: {
								"Done": function(){
									var olddata = row.data("payment_data");
									var newdata = {processing_type: payment_data.processing_type, data: form.find("form :input").serializeArray()};
									if (olddata && olddata.data)
										newdata.data = $.extend({}, olddata.data, newdata.data);
									row.data("payment_data", newdata);
									update_payments();
									form.dialog('close');
								}
							}
						});
					}
				});
			};

			$("button.payment-button", "#p_muid_form").hover(function(){
				$(this).addClass("ui-state-hover");
			}, function(){
				$(this).removeClass("ui-state-hover");
			}).click(function(){
				var payment_type = JSON.parse(this.value);
				// TODO: Minimums, maximums
				get_amount(function(amount){
					payments_table.pgrid_add([{key: payment_type.guid, values: [
						payment_type.name,
						amount,
						"pending"
					]}], function(){
						var row = $(this);
						row.data("payment_data", payment_type);
						payments_table.data_form(row);
					});
					update_payments();
				});
			});

			var get_amount = function(callback){
				// TODO: Minimums, maximums
				$("<div title=\"Payment Amount\"></div>").each(function(){
					var amount_dialog = $(this);
					// A button for the current amount due.
					amount_dialog.append($("<button></button>").addClass("ui-state-default ui-corner-all").hover(function(){
						$(this).addClass("ui-state-hover");
					}, function(){
						$(this).removeClass("ui-state-hover");
					}).html($("#p_muid_amount_due").html()).css({"float": "left", "clear": "both", "min-height": "60px", "width": "100%", "text-align": "center", "margin": "2px"})
					.click(function(){
						amount_dialog.dialog("close");
						callback(round_to_dec($("#p_muid_amount_due").html(), true));
					}));
					// Buttons for common amounts.
					$.each(["1", "5", "10", "20", "50", "60", "80", "100"], function(){
						var cur_amount = this;
						amount_dialog.append($("<button></button>").addClass("ui-state-default ui-corner-all").hover(function(){
							$(this).addClass("ui-state-hover");
						}, function(){
							$(this).removeClass("ui-state-hover");
						}).html(String(cur_amount)).css({"float": "left", "min-height": "60px", "min-width": "60px", "text-align": "center", "margin": "2px"})
						.click(function(){
							amount_dialog.dialog("close");
							callback(round_to_dec(cur_amount, true));
						}));
					});
					// A button for a custom amount.
					amount_dialog.append($("<button></button>").addClass("ui-state-default ui-corner-all").hover(function(){
						$(this).addClass("ui-state-hover");
					}, function(){
						$(this).removeClass("ui-state-hover");
					}).html("Another Amount").css({"float": "left", "clear": "both", "min-height": "60px", "width": "100%", "text-align": "center", "margin": "2px"})
					.click(function(){
						var cur_amount = null;
						do {
							cur_amount = prompt("Amount in dollars:", cur_amount);
						} while (isNaN(parseInt(cur_amount)) && cur_amount != null);
						amount_dialog.dialog("close");
						if (cur_amount != null)
							callback(round_to_dec(cur_amount, true));
					}));
				}).dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					close: function(){$(this).remove();}
				});
			};
			<?php } if (!empty($this->entity->payments)) { foreach ($this->entity->payments as $key => $cur_payment) { ?>
			(function(){
				var table_entry = JSON.parse("<?php
				$object = (object) array(
					'key' => $cur_payment['entity']->guid,
					'values' => array(
						$cur_payment['entity']->name,
						$pines->com_sales->round($cur_payment['amount'], true),
						$cur_payment['status']
					)
				);
				echo addslashes(json_encode($object)); ?>");

				payments_table.pgrid_add([table_entry], function(){
					var new_row = $(this).data("orig_key", <?php echo (int) $key; ?>);
					<?php if (!empty($cur_payment['data'])) { ?>
					var data = JSON.parse("<?php
					$data = array();
					foreach ($cur_payment['data'] as $cur_key => $cur_value) {
						$data[] = (object) array('name' => $cur_key, 'value' => $cur_value);
					}
					echo addslashes(json_encode((object) array(
						'processing_type' => $cur_payment['entity']->processing_type,
						'data' => $data
					))); ?>");
					new_row.data("payment_data", data);
					<?php } ?>
				});
			})();
			<?php } } ?>

			$("#p_muid_comments_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function(){
						$(this).dialog('close');
					}
				}
			});
			$("#p_muid_shipping_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function(){
						$(this).dialog('close');
					}
				}
			});
			// Put the dialogs back in the form.
			$("#p_muid_form").submit(function(){
				$(this).append($("#p_muid_comments_dialog").hide()).append($("#p_muid_shipping_dialog").hide());
			});

			var update_products = function(){
				var rows = products_table.pgrid_get_all_rows();
				if (!rows)
					return;
				var subtotal = 0;
				var taxes = 0;
				var item_fees = 0;
				var total = 0;
				<?php if ($pines->config->com_sales->com_customer) { ?>
				require_customer = false;
				<?php } ?>
				rows.each(function(){
					var cur_row = $(this);
					var product = cur_row.data("product");
					<?php if ($pines->config->com_sales->com_customer) { ?>
					if (product.require_customer)
						require_customer = true;
					<?php } ?>
					// Calculate ticket totals.
					var price = parseFloat(cur_row.pgrid_get_value(6));
					var qty = parseInt(cur_row.pgrid_get_value(5));
					var discount = cur_row.pgrid_get_value(7);
					var cur_item_fees = 0;
					if (isNaN(price))
						price = 0;
					if (isNaN(qty))
						qty = 1;
					if (product.discountable && discount != "") {
						var discount_price;
						if (discount.match(/^\$-?\d+(\.\d+)?$/)) {
							discount = parseFloat(discount.replace(/[^0-9.-]/, ''));
							discount_price = price - discount;
						} else if (discount.match(/^-?\d+(\.\d+)?%$/)) {
							discount = parseFloat(discount.replace(/[^0-9.-]/, ''));
							discount_price = price - (price * (discount / 100));
						}
						if (!isNaN(product.floor) && round_to_dec(discount_price) < round_to_dec(product.floor)) {
							alert("The discount lowers the product's price below the limit. The maximum discount possible for this item ["+product.name+"], is $"+round_to_dec(product.unit_price - product.floor, true)+" or "+round_to_dec((product.unit_price - product.floor) / product.unit_price * 100, true)+"%.");
							cur_row.pgrid_set_value(7, "");
						} else {
							price = discount_price;
						}
					}
					var line_total = price * qty;
					if (!product.tax_exempt) {
						$.each(taxes_percent, function(){
							taxes += (this.rate / 100) * line_total;
						});
						$.each(taxes_flat, function(){
							taxes += this.rate * qty;
						});
					}
					$.each(product.fees_percent, function(){
						cur_item_fees += (this.rate / 100) * line_total;
					});
					$.each(product.fees_flat, function(){
						cur_item_fees += this.rate * qty;
					});
					item_fees += round_to_dec(cur_item_fees);
					subtotal += round_to_dec(line_total);
					cur_row.pgrid_set_value(8, round_to_dec(line_total, true));
					cur_row.pgrid_set_value(9, round_to_dec(cur_item_fees, true));
				});
				$("#p_muid_subtotal").html(round_to_dec(subtotal, true));
				$("#p_muid_item_fees").html(round_to_dec(item_fees, true));
				$("#p_muid_taxes").html(round_to_dec(taxes, true));
				total = round_to_dec(subtotal) + round_to_dec(item_fees) + round_to_dec(taxes);
				$("#p_muid_total").html(round_to_dec(total, true));

				// Update the products input element.
				products.val(JSON.stringify(rows.pgrid_export_rows()));

				update_payments();
			};

			var update_payments = function(){
				var rows = payments_table.pgrid_get_all_rows();
				var total = parseFloat($("#p_muid_total").html());
				var amount_tendered = 0;
				var amount_due = 0;
				var change = 0;
				if (isNaN(total))
					return;
				var submit_val = rows.pgrid_export_rows();
				// Calculate the total payments.
				rows.each(function(i){
					var cur_row = $(this);
					if (cur_row.pgrid_get_value(3) != "declined") {
						var amount = parseFloat(cur_row.pgrid_get_value(2).replace(/[^0-9.-]/g, ""));
						if (isNaN(amount))
							amount = 0;
						amount_tendered += amount;
					}
					submit_val[i].data = cur_row.data("payment_data");
					submit_val[i].orig_key = cur_row.data("orig_key");
				});
				amount_due = total - amount_tendered;
				if (amount_due < 0) {
					change = Math.abs(amount_due);
					amount_due = 0;
				}
				$("#p_muid_amount_tendered").html(round_to_dec(amount_tendered, true));
				$("#p_muid_amount_due").html(round_to_dec(amount_due, true));
				$("#p_muid_change").html(round_to_dec(change, true));

				payments.val(JSON.stringify(submit_val));
			};

			<?php if ($pines->config->com_sales->cash_drawer) { ?>
			pines.com_sales_run_drawer = function(){
				var keep_checking = function(status){
					switch (status) {
						case "is_open":
							break;
						case "is_closed":
							pines.com_sales_run_submit();
							return;
							break;
						case "not_supported":
							alert("The drawer program does not support the correct return codes.");
							break;
						case "error":
							alert("There was an error with the drawer.");
							break;
						case "not_found":
							alert("The drawer was not found. Make sure it is plugged in.");
							break;
						case "misconfigured":
							alert("The drawer program is misconfigured or not installed.");
							break;
					}
					setTimeout(function(){
						pines.drawer_check(keep_checking);
					}, 500);
				};

				var kicked = false;
				var total_cash = 0;
				var message = "Please close the cash drawer when you are finished.<br />";
				$.each(payments_table.pgrid_get_all_rows().pgrid_export_rows(), function(){
					if (this.values[2] != "pending")
						return;
					if ($.inArray(parseInt(this.key), drawer_kickers) > -1) {
						kicked = true;
						// Remember how much cash.
						total_cash += parseFloat(this.values[1]);
					}
				});
				if (kicked)
					message += "<br /><div style=\"float: right; clear: right;\">Amount Received: <strong>$"+round_to_dec(total_cash, true)+"</strong></div>";

				var change = parseFloat($("#p_muid_change").html());
				if (change > 0 || kicked) {
					kicked = true;
					message += "<br /><div style=\"float: right; clear: right;\">Change Due: <strong>$"+round_to_dec(change, true)+"</strong></div><br style=\"clear: both;\" />";
				}

				if (kicked)
					pines.drawer_open(keep_checking, message);
				else
					$("#p_muid_form").submit();
			};
			<?php } else { ?>
			pines.com_sales_run_drawer = function(){
				pines.com_sales_run_submit();
			};
			<?php } ?>

			pines.com_sales_run_submit = function(){
				$("#p_muid_form").submit();
			};

			// Load any initial products.
			update_products();
		});
		// ]]>
	</script>
	<?php if ($pines->config->com_sales->com_customer) { ?>
	<div class="pf-element">
		<label>
			<span class="pf-label">Customer</span>
			<?php if ($this->entity->status != 'invoiced' && $this->entity->status != 'paid' && $this->entity->status != 'voided') { ?>
			<span class="pf-note">Enter part of a name, company, email, or phone # to search.</span>
			<?php } ?>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_customer" name="customer" size="24" value="<?php echo $this->entity->customer->guid ? htmlspecialchars("{$this->entity->customer->guid}: \"{$this->entity->customer->name}\"") : ''; ?>" <?php if ($this->entity->status == 'invoiced' || $this->entity->status == 'paid' || $this->entity->status == 'voided') echo 'disabled="disabled" '; ?>/>
		</label>
	</div>
	<?php } ?>
	<div id="p_muid_category_dialog" title="Categories" style="display: none;">
		<table id="p_muid_category_grid">
			<thead>
				<tr>
					<th>Order</th>
					<th>Name</th>
					<th>Products</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($this->categories as $category) { ?>
				<tr title="<?php echo $category->guid; ?>" class="<?php echo $category->children ? 'parent ' : ''; ?><?php echo isset($category->parent) ? "child ch_{$category->parent->guid} " : ''; ?>">
					<td><?php echo isset($category->parent) ? $category->array_search($category->parent->children) + 1 : '0' ; ?></td>
					<td><?php echo htmlspecialchars($category->name); ?></td>
					<td><?php echo count($category->products); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<br class="pf-clearing" />
	</div>
	<div id="p_muid_category_products_dialog" title="Products" style="display: none;">
		<table id="p_muid_category_products_grid">
			<thead>
				<tr>
					<th>Name</th>
					<th>SKU</th>
				</tr>
			</thead>
			<tbody>
				<tr><td>-</td><td>-</td></tr>
			</tbody>
		</table>
		<br class="pf-clearing" />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Products</span>
		<br class="pf-clearing" />
		<table id="p_muid_products_table">
			<thead>
				<tr>
					<th>SKU</th>
					<th>Product</th>
					<th>Serial</th>
					<th>Delivery</th>
					<th>Quantity</th>
					<th>Price</th>
					<th>Discount</th>
					<th>Line Total</th>
					<th>Fees</th>
					<?php if ($pines->config->com_sales->com_esp) { ?>
					<th>ESP</th>
					<?php } else { ?>
					<th>Unused</th>
					<?php } ?>
					<th>Salesperson</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->products as $cur_product) {
						if (!isset($cur_product['entity']))
							continue;
						?>
				<tr title="<?php echo $cur_product['entity']->guid; ?>">
					<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
					<td><?php echo htmlspecialchars($cur_product['entity']->name); ?></td>
					<td><?php echo htmlspecialchars($cur_product['serial']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['delivery']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['price']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['discount']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['line_total']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['fees']); ?></td>
					<?php if ($pines->config->com_sales->com_esp) { ?>
					<td><?php echo htmlspecialchars($cur_product['esp']); ?></td>
					<?php } else { ?>
					<td>NA</td>
					<?php } ?>
					<td><?php echo htmlspecialchars($cur_product['salesperson']->guid.': '.$cur_product['salesperson']->name); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<input type="hidden" id="p_muid_products" name="products" />
	</div>
	<div id="p_muid_delivery_dialog" title="Select Delivery Type" style="display: none;">
		<div id="p_muid_delivery_select">
			The item is being taken from current inventory:
			<div style="padding: 1em;">
				<input type="radio" name="delivery_type" id="p_muid_deliv_rad1" value="in-store" /><label for="p_muid_deliv_rad1" title="The customer is receiving the item right now.">In Store</label>
				<input type="radio" name="delivery_type" id="p_muid_deliv_rad2" value="shipped" /><label for="p_muid_deliv_rad2" title="The item is being shipped to the customer.">Ship to Customer</label>
				<input type="radio" name="delivery_type" id="p_muid_deliv_rad3" value="pick-up" /><label for="p_muid_deliv_rad3" title="The customer will pick up the item later.">Pick Up</label>
			</div>
			The item needs to be ordered:
			<div style="padding: 1em;">
				<input type="radio" name="delivery_type" id="p_muid_deliv_rad4" value="warehouse" /><label for="p_muid_deliv_rad4" title="The item should be ordered.">Warehouse</label>
			</div>
		</div>
	</div>
	<div id="p_muid_serial_dialog" title="Provide Serial" style="display: none;">
		<div class="pf-form">
			<div class="pf-element">
				<label><span class="pf-label">Serial Number</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_serial_number" name="serial_number" size="24" value="" /></label>
			</div>
			<div class="pf-element" id="p_muid_serial_dialog_warehouse">
				<strong>Or</strong> you can make this item a warehouse item.
			</div>
		</div>
		<br />
	</div>
	<?php if ($pines->config->com_sales->per_item_salesperson) { ?>
	<div id="p_muid_salesperson_dialog" title="Select Salesperson" style="display: none;">
		<div class="pf-form">
			<div class="pf-element">
				<label>
					<span class="pf-label">Employee</span>
					<span class="pf-note">Enter part of a name, title, email, or phone # to search.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_salesperson" name="item_salesperson" size="24" value="" />
				</label>
			</div>
		</div>
		<br />
	</div>
	<?php } ?>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Ticket Totals</span>
		<div class="pf-group">
			<div class="pf-field" style="float: right; font-size: 1.2em; text-align: right;">
				<span class="pf-label">Subtotal</span><span class="pf-field" id="p_muid_subtotal">0.00</span><br />
				<span class="pf-label">Item Fees</span><span class="pf-field" id="p_muid_item_fees">0.00</span><br />
				<span class="pf-label">Tax</span><span class="pf-field" id="p_muid_taxes">0.00</span><br />
				<hr /><br />
				<span class="pf-label">Total</span><span class="pf-field" id="p_muid_total">0.00</span>
			</div>
			<hr class="pf-field" style="clear: both;" />
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Payments</span>
		<?php if ($this->entity->status != 'paid' && $this->entity->status != 'voided') { ?>
		<div class="pf-note">
			<div style="text-align: left;">
				<?php foreach ($this->payment_types as $cur_payment_type) { ?>
				<button id="p_muid_payment_<?php echo $cur_payment_type->guid; ?>" class="ui-state-default ui-corner-all payment-button" type="button" style="margin-bottom: 2px;" value="<?php echo htmlspecialchars(json_encode((object) array('guid' => $cur_payment_type->guid, 'name' => $cur_payment_type->name, 'minimum' => $cur_payment_type->minimum, 'maximum' => $cur_payment_type->maximum, 'processing_type' => $cur_payment_type->processing_type))); ?>">
					<span class="picon picon-32 picon-view-financial-payment-mode" style="display: block; padding-top: 32px; min-width: 50px; background-repeat: no-repeat; background-position: top center;"><?php echo htmlspecialchars($cur_payment_type->name); ?></span>
				</button>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
		<div style="margin-top: 5px;" class="pf-group">
			<div class="pf-field">
				<table id="p_muid_payments_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Amount</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			<input type="hidden" id="p_muid_payments" name="payments" />
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Tendered</span>
		<div class="pf-group">
			<div class="pf-field" style="float: right; font-size: 1.2em; text-align: right;">
				<span class="pf-label">Amount Tendered</span><span class="pf-field" id="p_muid_amount_tendered">0.00</span><br />
				<span class="pf-label">Amount Due</span><span style="font-weight: bold;" class="pf-field" id="p_muid_amount_due">0.00</span><br />
				<hr /><br />
				<span class="pf-label">Change</span><span style="font-weight: bold;" class="pf-field" id="p_muid_change">0.00</span>
			</div>
			<hr class="pf-field" style="clear: both;" />
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Sale Information</span>
		<div class="pf-group">
			<input class="pf-field ui-state-default ui-corner-all" type="button" value="Edit Shipping Address" onclick="$('#p_muid_shipping_dialog').dialog('open');" />
			<input class="pf-field ui-state-default ui-corner-all" type="button" value="Edit Comments" onclick="$('#p_muid_comments_dialog').dialog('open');" />
			<hr class="pf-field" style="clear: both; margin-top: 15px;" />
		</div>
	</div>
	<div id="p_muid_shipping_dialog" title="Shipping Address" style="display: none;">
		<div class="pf-form">
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Use Customer Info</span>
					<input class="pf-field" type="checkbox" name="shipping_use_customer" value="ON"<?php echo $this->entity->shipping_use_customer ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="shipping_name" size="24" value="<?php echo htmlspecialchars($this->entity->shipping_address->name); ?>" /></label>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var address_us = $("#p_muid_address_us");
						var address_international = $("#p_muid_address_international");
						$("#p_muid_shipping_dialog [name=shipping_address_type]").change(function(){
							var address_type = $(this);
							if (address_type.is(":checked") && address_type.val() == "us") {
								address_us.show();
								address_international.hide();
							} else if (address_type.is(":checked") && address_type.val() == "international") {
								address_international.show();
								address_us.hide();
							}
						}).change();
					});
					// ]]>
				</script>
				<span class="pf-label">Address Type</span>
				<label><input class="pf-field" type="radio" name="shipping_address_type" value="us"<?php echo (!isset($this->entity->shipping_address->address_type) || $this->entity->shipping_address->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="pf-field" type="radio" name="shipping_address_type" value="international"<?php echo $this->entity->shipping_address->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="p_muid_address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="shipping_address_1" size="24" value="<?php echo htmlspecialchars($this->entity->shipping_address->address_1); ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="shipping_address_2" size="24" value="<?php echo htmlspecialchars($this->entity->shipping_address->address_2); ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="shipping_city" size="15" value="<?php echo htmlspecialchars($this->entity->shipping_address->city); ?>" />
					<select class="pf-field ui-widget-content ui-corner-all" name="shipping_state">
						<option value="">None</option>
						<?php foreach (array(
								'AL' => 'Alabama',
								'AK' => 'Alaska',
								'AZ' => 'Arizona',
								'AR' => 'Arkansas',
								'CA' => 'California',
								'CO' => 'Colorado',
								'CT' => 'Connecticut',
								'DE' => 'Delaware',
								'DC' => 'DC',
								'FL' => 'Florida',
								'GA' => 'Georgia',
								'HI' => 'Hawaii',
								'ID' => 'Idaho',
								'IL' => 'Illinois',
								'IN' => 'Indiana',
								'IA' => 'Iowa',
								'KS' => 'Kansas',
								'KY' => 'Kentucky',
								'LA' => 'Louisiana',
								'ME' => 'Maine',
								'MD' => 'Maryland',
								'MA' => 'Massachusetts',
								'MI' => 'Michigan',
								'MN' => 'Minnesota',
								'MS' => 'Mississippi',
								'MO' => 'Missouri',
								'MT' => 'Montana',
								'NE' => 'Nebraska',
								'NV' => 'Nevada',
								'NH' => 'New Hampshire',
								'NJ' => 'New Jersey',
								'NM' => 'New Mexico',
								'NY' => 'New York',
								'NC' => 'North Carolina',
								'ND' => 'North Dakota',
								'OH' => 'Ohio',
								'OK' => 'Oklahoma',
								'OR' => 'Oregon',
								'PA' => 'Pennsylvania',
								'RI' => 'Rhode Island',
								'SC' => 'South Carolina',
								'SD' => 'South Dakota',
								'TN' => 'Tennessee',
								'TX' => 'Texas',
								'UT' => 'Utah',
								'VT' => 'Vermont',
								'VA' => 'Virginia',
								'WA' => 'Washington',
								'WV' => 'West Virginia',
								'WI' => 'Wisconsin',
								'WY' => 'Wyoming',
								'AA' => 'Armed Forces (AA)',
								'AE' => 'Armed Forces (AE)',
								'AP' => 'Armed Forces (AP)'
							) as $key => $cur_state) {
						?><option value="<?php echo $key; ?>"<?php echo $this->entity->shipping_address->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option><?php
						} ?>
					</select>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Zip</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="shipping_zip" size="24" value="<?php echo htmlspecialchars($this->entity->shipping_address->zip); ?>" /></label>
				</div>
			</div>
			<div id="p_muid_address_international" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="shipping_address_international"><?php echo $this->entity->shipping_address->address_international; ?></textarea></span></label>
				</div>
			</div>
		</div>
		<br />
	</div>
	<div id="p_muid_comments_dialog" title="Comments" style="display: none;">
		<div class="pf-element pf-full-width">
			<textarea class="pf-field pf-full-width ui-widget-content ui-corner-all" style="width: 96%; height: 100%;" rows="3" cols="35" id="p_muid_comments" name="comments"><?php echo $this->entity->comments; ?></textarea>
		</div>
	</div>
	<?php if (!empty($this->returns)) { ?>
	<div class="pf-element">
		<span class="pf-label">Associated Return(s)</span>
		<span class="pf-note">This sale has attached returns.</span>
		<div class="pf-group">
		<?php foreach($this->returns as $cur_return) { ?>
		<span class="pf-field">
			Return #<?php echo htmlspecialchars($cur_return->id); ?>:
			<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'return/receipt', array('id' => $cur_return->guid))); ?>" onclick="window.open(this.href); return false;">Receipt</a>
			<?php if (gatekeeper('com_sales/editreturn')) { ?>
			<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'return/edit', array('id' => $cur_return->guid))); ?>" onclick="window.open(this.href); return false;">Edit</a>
			<?php } ?>
		</span><br />
		<?php } ?>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>

		<input type="hidden" id="p_muid_sale_process_type" name="process" value="quote" />

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'paid') { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Tender" onclick="$('#p_muid_sale_process_type').val('tender'); pines.com_sales_run_drawer();" />
		<?php } ?>

		<?php if ( $pines->config->com_sales->allow_invoicing && ($this->entity->status != 'voided' && $this->entity->status != 'paid' && $this->entity->status != 'invoiced') ) { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Invoice" onclick="$('#p_muid_sale_process_type').val('invoice'); pines.com_sales_run_submit();" />
		<?php } ?>

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'paid' && $this->entity->status != 'invoiced' && $this->entity->status != 'quoted') { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Quote" onclick="$('#p_muid_sale_process_type').val('quote'); pines.com_sales_run_submit();" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Save" onclick="$('#p_muid_sale_process_type').val('save'); pines.com_sales_run_submit();" />
		<?php } ?>

		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'sale/list')); ?>');" value="Cancel" />
	</div>
</form>