<?php
/**
 * Provides a form for the user to edit a sale.
 * 
 * This file contains probably the most unholy and confusing heap of JavaScript
 * in all of Pines. I highly suggest you don't attempt to customize it.
 * 
 * But if you choose to battle with this JS, I can't stop you. It's dangerous to
 * go alone! Take this.
 * 
 *       ooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 *     ooooooo
 * 888888888888888
 * 88  OOOOOOO  88
 *     8888888
 *     OOOOOOO
 *     8888888
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
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_specials .special {
		float: left;
		margin-top: .5em;
		margin-left: .5em;
		padding: .2em;
	}
	#p_muid_specials .special_name {
		font-weight: bold;
	}
	#p_muid_specials .special_discount {
		text-align: right;
	}
	/* ]]> */
</style>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
		<div>Modified: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
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
			$elig_specials = array();
			foreach ($this->specials as $cur_special) {
				$discounts = array();
				foreach ($cur_special->discounts as $cur_discount) {
					if (isset($cur_discount['qualifier']))
						$discounts[] = array(
							'type' => $cur_discount['type'],
							'qualifier' => $cur_discount['qualifier']->guid,
							'value' => $cur_discount['value']
						);
					else
						$discounts[] = $cur_discount;
				}
				$requirements = array();
				foreach ($cur_special->requirements as $cur_requirement) {
					if (is_object($cur_requirement['value']))
						$requirements[] = array(
							'type' => $cur_requirement['type'],
							'value' => $cur_requirement['value']->guid
						);
					else
						$requirements[] = $cur_requirement;
				}
				$elig_specials[] = array(
					'guid' => $cur_special->guid,
					'name' => $cur_special->name,
					'per_ticket' => $cur_special->per_ticket,
					'before_tax' => $cur_special->before_tax,
					'discounts' => $discounts,
					'requirements' => $requirements,
				);
			}
			$added_specials = array();
			foreach ((array) $this->entity->added_specials as $cur_special) {
				$discounts = array();
				foreach ($cur_special->discounts as $cur_discount) {
					if (isset($cur_discount['qualifier']))
						$discounts[] = array(
							'type' => $cur_discount['type'],
							'qualifier' => $cur_discount['qualifier']->guid,
							'value' => $cur_discount['value']
						);
					else
						$discounts[] = $cur_discount;
				}
				$requirements = array();
				foreach ($cur_special->requirements as $cur_requirement) {
					if (is_object($cur_requirement['value']))
						$requirements[] = array(
							'type' => $cur_requirement['type'],
							'value' => $cur_requirement['value']->guid
						);
					else
						$requirements[] = $cur_requirement;
				}
				$added_specials[] = array(
					'guid' => $cur_special->guid,
					'name' => $cur_special->name,
					'per_ticket' => $cur_special->per_ticket,
					'before_tax' => $cur_special->before_tax,
					'discounts' => $discounts,
					'requirements' => $requirements,
				);
			}
?>
			var taxes_percent = <?php echo json_encode($taxes_percent); ?>;
			var taxes_flat = <?php echo json_encode($taxes_flat); ?>;
			var drawer_kickers = <?php echo json_encode($drawer_kickers); ?>;
			var elig_specials = <?php echo json_encode($elig_specials); ?>;
			var added_specials = <?php echo json_encode($added_specials); ?>;
			var status = <?php echo json_encode($this->entity->status); ?>;

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
								textbox.autocomplete("close");
								textbox.val("");
								var loader;
								$.ajax({
									url: <?php echo json_encode(pines_url('com_sales', 'product/search')); ?>,
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
										pines.error("An error occured while trying to lookup the product code:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
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
							textbox.productselect({
								open: function(){if (textbox.val() == "") textbox.autocomplete("close");},
								select: function(event, ui){select(ui.item.value); return false;}
							});
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
							serial_box.val(pines.unsafe(serial));
							var buttons = {
								"Done": function(){
									serial = serial_box.val();
									if (serial == "") {
										alert("Please provide a serial number.");
										return;
									}
									rows.pgrid_set_value(3, pines.safe(serial));
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
								rows.pgrid_set_value(7, pines.safe(discount));
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
								// Generate a random hex ID.
								var esp_id = "", hex = "abcdef0123456789";
								while (esp_id.length<13)
									esp_id += hex.charAt(Math.floor(Math.random() * hex.length));

								var insured_item = $(this);
								var insured_guid = insured_item.attr("title");
								if (insured_guid == "<?php echo (int) $esp_product->guid; ?>") {
									alert('This item is an ESP. It does not need to be insured');
									return;
								} else if (insured_item.pgrid_get_value(10) != '') {
									alert('There is already an ESP for this item');
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
									'fees_flat' => $fees_flat,
									'serials' => array()
								);

								foreach ((array) $esp_product->return_checklists as $cur_return_checklist) {
									if (!$cur_return_checklist->enabled)
										continue;
									$json_struct->return_checklists[] = array('guid' => $cur_return_checklist->guid, 'label' => $cur_return_checklist->label, 'conditions' => (array) $cur_return_checklist->conditions);
								}

								// Look up serials in the user's current location to allow them to choose.
								if ($esp_product->serialized && $pines->config->com_sales->add_product_show_serials) {
									$selector = array('&',
											'tag' => array('com_sales', 'stock'),
											'data' => array('available', true),
											'ref' => array(
												array('product', $esp_product)
											)
										);
									if (isset($_SESSION['user']->group->guid))
										$selector['ref'][] = array('location', $_SESSION['user']->group);
									$stock_entries = $pines->entity_manager->get_entities(
											array('class' => com_sales_stock, 'limit' => $pines->config->com_sales->add_product_show_serials),
											$selector
										);
									foreach ($stock_entries as $cur_stock) {
										$json_struct->serials[] = htmlspecialchars($cur_stock->serial);
									}
								}
								?>
								var product_data = <?php echo json_encode($json_struct); ?>;
								product_data.unit_price = esp_price;
								product_data.esp = esp_id;
								add_product(product_data, function(){
									insured_item.pgrid_set_value(10, pines.safe(esp_id));
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
									deserted.splice(deserter, 1);
								else
									deserted.push(cur_id);
							});
							// Remove any deserted ESP/ESP IDs
							if (deserted.length) {
								$.each(products_table.pgrid_get_all_rows(), function(){
									var cur_item = $(this);
									if ($.inArray(cur_item.pgrid_get_value(10), deserted) != -1) {
										if (cur_item.attr('title') == '<?php echo (int) $esp_product->guid; ?>')
											// An insured item has been removed
											cur_item.pgrid_delete();
										else
											// An ESP has been removed
											cur_item.pgrid_set_value(10, "");
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
				var cur_row, del_dia = <?php echo json_encode($pines->config->com_sales->delivery_dialog); ?>;
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
				var show_del_dia = function(){
					if (data.stock_type != "non_stocked" && ((del_dia == "only-non-serialized" && !data.serialized) || del_dia == "all")) {
						products_table.pgrid_get_selected_rows().pgrid_deselect_rows();
						cur_row.pgrid_select_rows();
						delivery_select.find("input[value=in-store]").attr("checked", "true");
						delivery_select.find("input").button("refresh");
						delivery_dialog.dialog("open");
					}
				};
				data.salesperson = <?php echo json_encode($_SESSION['user']->guid.': '.$_SESSION['user']->name); ?>;
				var serial = "";
				if (data.serialized) {
					var buttons = {
						"Done": function(){
							serial = serial_box.val();
							if (serial == "") {
								alert("Please provide a serial number.");
								return;
							}
							products_table.pgrid_add([{key: data.guid, values: [pines.safe(data.sku), pines.safe(data.name), pines.safe(serial), 'in-store', 1, pines.safe(data.unit_price), "", "", "", pines.safe(data.esp), pines.safe(data.salesperson)]}], function(){
								cur_row = $(this);
								cur_row.data("product", data);
							});
							update_products();
							serial_dialog.dialog("close");
							show_del_dia();
							if (success)
								success();
						},
						"Warehouse Item": function(){
							products_table.pgrid_add([{key: data.guid, values: [pines.safe(data.sku), pines.safe(data.name), pines.safe(serial), 'warehouse', 1, pines.safe(data.unit_price), "", "", "", pines.safe(data.esp), pines.safe(data.salesperson)]}], function(){
								cur_row = $(this);
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
					serial_dialog.dialog("option", "title", "Provide Serial for "+pines.safe(data.name))
					.dialog("option", "buttons", buttons)
					.dialog("open");
					serial_box.val("");
					if (data.serials.length) {
						var serial_list = $("#p_muid_available_serials").show().find(".serials").empty();
						serial_list.append("<a href=\"javascript:void(0);\" class=\"serial\">"+$.map(data.serials, pines.safe).join("</a> | <a href=\"javascript:void(0);\" class=\"serial\">")+"</a>");
					}
					return;
				}
				products_table.pgrid_add([{key: data.guid, values: [pines.safe(data.sku), pines.safe(data.name), pines.safe(serial), 'in-store', 1, pines.safe(data.unit_price), "", "", "", pines.safe(data.esp), pines.safe(data.salesperson)]}], function(){
					cur_row = $(this);
					cur_row.data("product", data);
				});
				update_products();
				show_del_dia();
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
						cur_row.pgrid_set_value(4, pines.safe(delivery));
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
						cur_row.pgrid_set_value(3, pines.safe(serial));
						cur_row.pgrid_set_value(4, pines.safe(delivery));
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
			var serial_dialog = $("#p_muid_serial_dialog").delegate(".serials a.serial", "click", function(){
				$("#p_muid_serial_number").val($(this).html());
				serial_dialog.dialog("option", "buttons").Done();
			}).dialog({
				bgiframe: true,
				autoOpen: false,
				width: 450,
				modal: true,
				close: function(){
					$("#p_muid_product_code_box").focus();
					$("#p_muid_available_serials").hide();
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
							url: <?php echo json_encode(pines_url('com_sales', 'category/products')); ?>,
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
								pines.error("An error occured while trying to lookup the products:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
							},
							success: function(data){
								if (!data || !data[0]) {
									alert("No products were returned.");
									return;
								}
								$.each(data, function(){
									var product = this;
									category_products_grid.pgrid_add([{key: this.guid, values: [pines.safe(this.name), pines.safe(this.sku)]}], function(){
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
							salesperson = <?php echo json_encode($_SESSION['user']->guid.': '.$_SESSION['user']->name); ?>;
						} else if (!salesperson.match(/^\d+: .+$/)) {
							alert("Please select a salesperson using the dropdown menu.");
							return;
						}
						row.pgrid_set_value(11, pines.safe(salesperson));
						row.pgrid_deselect_rows();
						salesperson_dialog.dialog('close');
						update_products();
					}
				});
				salesperson_dialog.dialog('open');
			};
			<?php } ?>
			$("#p_muid_special_code").click(function(){
				$("<div title=\"Enter a Special Code\"><input class=\"special_code ui-widget-content ui-corner-all\" type=\"text\" size=\"24\" /></div>").dialog({
					modal: true,
					autoOpen: true,
					buttons: {
						"Continue": function(){
							var code = $(this).find("input.special_code").val();
							if (code == "") {
								alert("Please enter a special code.");
								return;
							}
							$.ajax({
								url: <?php echo json_encode(pines_url('com_sales', 'special/search')); ?>,
								type: "POST",
								dataType: "json",
								data: {"code": code},
								error: function(XMLHttpRequest, textStatus){
									pines.error("An error occured while trying to look up special:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
								},
								success: function(data){
									if (!data) {
										alert("The special code you entered either doesn't exist or is ineligible.");
										return;
									}
									added_specials.push(data);
									update_products();
								}
							});
							$(this).dialog("close").remove();
						}
					}
				}).find("input.special_code").keypress(function(e){
					if (e.keyCode == 13) {
						var dialog = $(this).parent();
						dialog.dialog("option", "buttons").Continue.call(dialog);
					}
				});
			});
			$("#p_muid_special_remove").click(function(){
				if (!added_specials.length) {
					alert("There are no entered special codes to remove.");
					return;
				}
				var form = $("<div title=\"Remove a Special Code\"></div>");
				$.each(added_specials, function(i, cur_special){
					form.append($("<button type=\"button\">"+pines.safe(cur_special.name)+"</button>").button({
						icons: {primary: "ui-icon-circle-minus"}
					}).click(function(){
						added_specials.splice(i, 1);
						update_products();
						form.dialog("close").remove();
					}));
				});
				form.dialog({
					modal: true,
					autoOpen: true
				});
			});
			<?php } ?>

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
					url: <?php echo json_encode(pines_url('com_sales', 'product/search')); ?>,
					type: "POST",
					async: false,
					dataType: "json",
					data: {"code": cur_guid, "useguid": true},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to lookup a product:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
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
									cur_row.pgrid_set_value(2, pines.safe(amount));
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
					url: <?php echo json_encode(pines_url('com_sales', 'forms/payment')); ?>,
					type: "POST",
					dataType: "html",
					data: {"name": payment_data.processing_type, "id": $("#p_muid_form [name=id]").val(), "customer": $("#p_muid_customer").val(), "type": "sale"},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retrieve the data form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (data == "")
							return;
						var form = $("<div title=\"Data for "+pines.safe(row.pgrid_get_value(1))+" Payment\"></div>");
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
										newdata.data = $.merge(newdata.data, olddata.data);
									newdata.data = $.makeArray(newdata.data);
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
						pines.safe(payment_type.name),
						pines.safe(amount),
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
				var table_entry = <?php
				$object = (object) array(
					'key' => $cur_payment['entity']->guid,
					'values' => array(
						$cur_payment['entity']->name,
						$pines->com_sales->round($cur_payment['amount'], true),
						$cur_payment['status']
					)
				);
				echo json_encode($object); ?>;

				payments_table.pgrid_add([table_entry], function(){
					var new_row = $(this);
					<?php if (isset($this->entity->guid)) { // Only save original key if the sale is already in the database. ?>
					new_row.data("orig_key", <?php echo (int) $key; ?>);
					<?php } ?>
					var data = <?php
					$data = array();
					if (!empty($cur_payment['data'])) { 
						foreach ($cur_payment['data'] as $cur_key => $cur_value) {
							$data[] = (object) array('name' => $cur_key, 'value' => $cur_value);
						}
					}
					echo json_encode((object) array(
						'processing_type' => $cur_payment['entity']->processing_type,
						'data' => $data
					)); ?>;
					new_row.data("payment_data", data);
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
				var specials = [];
				var taxes = 0;
				var item_fees = 0;
				var total = 0;
				// How many times to apply a flat tax.
				var tax_qty = 0;
				var taxable_subtotal = 0;
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
						taxable_subtotal += round_to_dec(line_total);
						tax_qty += qty;
					}
					$.each(product.fees_percent, function(){
						cur_item_fees += (this.rate / 100) * line_total;
					});
					$.each(product.fees_flat, function(){
						cur_item_fees += this.rate * qty;
					});
					item_fees += round_to_dec(cur_item_fees);
					subtotal += round_to_dec(line_total);
					cur_row.pgrid_set_value(8, pines.safe(round_to_dec(line_total, true)));
					cur_row.pgrid_set_value(9, pines.safe(round_to_dec(cur_item_fees, true)));
				});
				$("#p_muid_subtotal").html(round_to_dec(subtotal, true));
				// Now that we know the subtotal, we can use it for specials.
				var total_before_tax_specials = 0;
				var total_specials = 0;
				// First build an array of specials that match and their discounts.
				$.each($.merge($.merge([], added_specials), elig_specials), function(i, cur_special){
					var apply_special = true;
					$.each(cur_special.requirements, function(i, cur_req){
						if (!apply_special)
							return;
						switch (cur_req.type) {
							case "subtotal_eq":
								if (round_to_dec(subtotal, true) != round_to_dec(cur_req.value, true))
									apply_special = false;
								break;
							case "subtotal_lt":
								if (round_to_dec(subtotal) >= round_to_dec(cur_req.value))
									apply_special = false;
								break;
							case "subtotal_gt":
								if (round_to_dec(subtotal) <= round_to_dec(cur_req.value))
									apply_special = false;
								break;
							case "has_product":
								if (!rows.filter("[title="+cur_req.value+"]").length)
									apply_special = false;
								break;
							case "has_not_product":
								if (rows.filter("[title="+cur_req.value+"]").length)
									apply_special = false;
								break;
						}
					});
					if (!apply_special)
						return;
					// The special works, now calculate its value.
					var discount = 0;
					$.each(cur_special.discounts, function(i, cur_dis){
						switch (cur_dis.type) {
							case "order_amount":
								discount += cur_dis.value;
								break;
							case "order_percent":
								discount += subtotal * (cur_dis.value / 100);
								break;
							case "product_amount":
								var qty = 0;
								rows.filter("[title="+cur_dis.qualifier+"]").each(function(){
									qty += parseInt($(this).pgrid_get_value(5));
								});
								discount += qty * cur_dis.value;
								break;
							case "product_percent":
								var prod_total = 0;
								rows.filter("[title="+cur_dis.qualifier+"]").each(function(){
									prod_total += parseFloat($(this).pgrid_get_value(8));
								});
								discount += prod_total * (cur_dis.value / 100);
								break;
							case "item_amount":
								if (!rows.filter("[title="+cur_dis.qualifier+"]").length);
									break;
								discount += cur_dis.value;
								break;
							case "item_percent":
								var prod = rows.filter("[title="+cur_dis.qualifier+"]").eq(0);
								if (!prod.length)
									break;
								var prod_price = parseFloat(prod.pgrid_get_value(6));
								discount += prod_price * (cur_dis.value / 100);
								break;
						}
					});
					discount = round_to_dec(discount);
					if (discount <= 0)
						return;
					specials.push($.extend({"discount": discount}, cur_special));
				});
				// Now remove specials with other special requirements.
				specials = $.grep(specials, function(cur_special){
					var apply_special = true;
					$.each(cur_special.requirements, function(i, cur_req){
						if (!apply_special)
							return;
						switch (cur_req.type) {
							case "has_special":
								var matching;
								if (cur_req.value == 'any') {
									// Have to subtract this special.
									matching = specials.length - 1;
								} else {
									matching = $.grep(specials, function(cur_test){
										return (cur_req.value == cur_test.guid);
									}).length;
								}
								if (!matching)
									apply_special = false;
								break;
							case "has_not_special":
								var matching;
								if (cur_req.value == 'any') {
									// Have to subtract this special.
									matching = specials.length - 1;
								} else {
									matching = $.grep(specials, function(cur_test){
										return (cur_req.value == cur_test.guid);
									}).length;
								}
								if (matching)
									apply_special = false;
								break;
						}
					});
					if (!apply_special)
						return false;
					// Add up the total discounts.
					if (cur_special.before_tax)
						total_before_tax_specials += cur_special.discount;
					total_specials += cur_special.discount;
					return true;
				});
				$.each(taxes_percent, function(){
					taxes += (this.rate / 100) * (taxable_subtotal - total_before_tax_specials);
				});
				$.each(taxes_flat, function(){
					taxes += this.rate * tax_qty;
				});
				// Now add all the specials to the specials section.
				var special_box = $("#p_muid_specials").empty();
				$.each(specials, function(i, cur_special){
					special_box.append("<div class=\"special ui-widget-content ui-corner-all\"><div class=\"special_name\">"+pines.safe(cur_special.name)+"</div><div class=\"special_discount\">"+(cur_special.before_tax ? "<small>(before tax)</small> " : "")+"$"+round_to_dec(cur_special.discount, true)+"</div></div>");
				});
				// Update the specials input box.
				$("#p_muid_specials_input").val(JSON.stringify(added_specials));
				$("#p_muid_specials_total").html(round_to_dec(total_specials, true));
				if (round_to_dec(total_specials) > 0)
					$("#p_muid_specials_total_container").show();
				else
					$("#p_muid_specials_total_container").hide();
				$("#p_muid_item_fees").html(round_to_dec(item_fees, true));
				$("#p_muid_taxes").html(round_to_dec(taxes, true));
				total = (round_to_dec(subtotal) - round_to_dec(total_specials)) + round_to_dec(item_fees) + round_to_dec(taxes);
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

			pines.com_sales_run_check = function(use_drawer){
				if (require_customer && !$("#p_muid_customer").val().match(/^\d+/)) {
					alert("One of the products on this sale requires a customer. Please select a customer before continuing.");
					return;
				}
				var product_val = products.val();
				if (product_val == '[]') {
					alert("Please select at least one product first.");
					return;
				}
				<?php if (!$this->entity->removed_stock) { ?>
				var modal = $("<div title=\"Verifying Current Inventory\"><span class=\"picon-32 picon-throbber\" style=\"display: block; float: left; height: 32px; width: 32px;\">&nbsp;</span>Your current inventory is being checked for the selected products. This should only take a few moments.</div>");
				$.ajax({
					url: <?php echo json_encode(pines_url('com_sales', 'sale/checkproducts')); ?>,
					type: "POST",
					dataType: "json",
					data: {"products": product_val},
					beforeSend: function(){
						modal.dialog({
							modal: true,
							width: 500,
							autoOpen: true,
							closeOnEscape: false,
							resizable: false,
							open: function(){
								$(".ui-dialog-titlebar-close", modal.closest('.ui-widget')).hide();
							},
							close: function(){
								modal.dialog("open");
							}
						});
					},
					complete: function(){
						modal.dialog("option", "close", null).dialog("close").remove();
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to check products:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (!data) {
							pines.error("An error occured while trying to check products.");
							return;
						}
						if (!data.result) {
							$.each(data.messages, function(){
								alert(this);
							});
							return;
						}
						if (use_drawer)
							pines.com_sales_run_drawer();
						else
							pines.com_sales_run_submit();
					}
				});
				<?php } else { ?>
				if (use_drawer)
					pines.com_sales_run_drawer();
				else
					pines.com_sales_run_submit();
				<?php } ?>
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
				$(":button, :submit, :reset", "#p_muid_form .pf-buttons").attr("disabled", "disabled").addClass("ui-state-disabled");
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
				<tr title="<?php echo (int) $category->guid; ?>" class="<?php echo $category->children ? 'parent ' : ''; ?><?php echo isset($category->parent) ? htmlspecialchars("child ch_{$category->parent->guid} ") : ''; ?>">
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
				<tr title="<?php echo (int) $cur_product['entity']->guid; ?>">
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
			<div class="pf-element" id="p_muid_available_serials" style="display: none;">
				<span class="pf-label">Some Available</span>
				<span class="pf-note">At your location.</span>
				<div class="pf-group">
					<div class="pf-field serials"></div>
				</div>
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
		<span class="pf-label">Specials</span>
		<?php if ($this->entity->status != 'invoiced' && $this->entity->status != 'paid' && $this->entity->status != 'voided') { ?>
		<span class="pf-field">
			<a href="javascript:void(0);" id="p_muid_special_code">Enter a Special Code</a>
			|
			<a href="javascript:void(0);" id="p_muid_special_remove">Remove a Special Code</a>
		</span>
		<?php } ?>
		<br class="pf-clearing" />
		<div id="p_muid_specials"></div>
		<input type="hidden" name="specials" value="" id="p_muid_specials_input" />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Ticket Totals</span>
		<div class="pf-group">
			<div class="pf-field" style="float: right; font-size: 1.2em; text-align: right;">
				<span class="pf-label">Subtotal</span><span class="pf-field" id="p_muid_subtotal">0.00</span><br />
				<div id="p_muid_specials_total_container" style="display: none;">
					<span class="pf-label">Specials</span><span class="pf-field">(<span id="p_muid_specials_total">0.00</span>)</span><br />
				</div>
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
				<button id="p_muid_payment_<?php echo (int) $cur_payment_type->guid; ?>" class="ui-state-default ui-corner-all payment-button" type="button" style="margin-bottom: 2px;" value="<?php echo htmlspecialchars(json_encode((object) array('guid' => $cur_payment_type->guid, 'name' => $cur_payment_type->name, 'minimum' => $cur_payment_type->minimum, 'maximum' => $cur_payment_type->maximum, 'processing_type' => $cur_payment_type->processing_type))); ?>">
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
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="shipping_address_international"><?php echo htmlspecialchars($this->entity->shipping_address->address_international); ?></textarea></span></label>
				</div>
			</div>
		</div>
		<br />
	</div>
	<div id="p_muid_comments_dialog" title="Comments" style="display: none;">
		<div class="pf-element pf-full-width">
			<textarea class="pf-field pf-full-width ui-widget-content ui-corner-all" style="width: 96%; height: 100%;" rows="3" cols="35" id="p_muid_comments" name="comments"><?php echo htmlspecialchars($this->entity->comments); ?></textarea>
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
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>

		<input type="hidden" id="p_muid_sale_process_type" name="process" value="quote" />

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'paid') { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Tender" onclick="$('#p_muid_sale_process_type').val('tender'); pines.com_sales_run_check(true);" />
		<?php } ?>

		<?php if ( $pines->config->com_sales->allow_invoicing && ($this->entity->status != 'voided' && $this->entity->status != 'paid' && $this->entity->status != 'invoiced') ) { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Invoice" onclick="$('#p_muid_sale_process_type').val('invoice'); pines.com_sales_run_check();" />
		<?php } ?>

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'paid' && $this->entity->status != 'invoiced' && $this->entity->status != 'quoted') { if ($pines->config->com_sales->allow_quoting) { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Quote" onclick="$('#p_muid_sale_process_type').val('quote'); pines.com_sales_run_check();" />
		<?php } } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Save" onclick="$('#p_muid_sale_process_type').val('save'); pines.com_sales_run_check();" />
		<?php } ?>

		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'sale/list')); ?>');" value="Cancel" />
	</div>
</form>