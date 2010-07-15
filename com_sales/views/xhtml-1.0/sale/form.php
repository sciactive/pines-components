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
	$this->title = 'Quoted Sale ['.htmlentities($this->entity->id).']';
} elseif ($this->entity->status == 'invoiced') {
	$this->title = 'Invoiced Sale ['.htmlentities($this->entity->id).']';
} elseif ($this->entity->status == 'paid') {
	$this->title = 'Paid Sale ['.htmlentities($this->entity->id).']';
} elseif ($this->entity->status == 'voided') {
	$this->title = 'Voided Sale ['.htmlentities($this->entity->id).']';
}
$this->note = 'Use this form to edit a sale.';
$pines->com_pgrid->load();
if ($pines->config->com_sales->com_customer)
	$pines->com_customer->load_customer_select();
if ($pines->config->com_sales->autocomplete_product)
	$pines->com_sales->load_product_select();
// TODO: After a sale is invoiced, don't calculate totals, just show what's saved.
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_sales', 'sale/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlentities("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlentities("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<script type="text/javascript">
		// <![CDATA[

		pines(function(){
			var comments = $("#p_muid_comment_saver");
			var comments_box = $("#p_muid_comments");
			var products = $("#p_muid_products");
			var products_table = $("#p_muid_products_table");
			var payments_table = $("#p_muid_payments_table");
			var payments = $("#p_muid_payments");
			<?php if ($pines->config->com_sales->com_customer) { ?>
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

			var round_to_dec = function(value){
				var rnd = Math.pow(10, dec);
				var mult = value * rnd;
				value = gaussianRound(mult);
				value /= rnd;
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
			<?php } ?>

			<?php if ($this->entity->status == 'invoiced' || $this->entity->status == 'paid' || $this->entity->status == 'voided') { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
				pgrid_paginate: false,
				pgrid_toolbar: false
			});
			<?php } else { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
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
							var serial = rows.pgrid_get_value(3);
							do {
								serial = prompt("This item is serialized. Please provide the serial:", serial);
							} while (!serial && serial != null);
							if (serial != null) {
								rows.pgrid_set_value(3, serial);
								update_products();
							}
						}
					},
					{
						type: 'button',
						text: 'Delivery',
						extra_class: 'picon picon-mail-send',
						multi_select: true,
						click: function(e, rows){
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
								alert("The selected product does not allow variable pricing.")
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
					{type: 'separator'},
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'picon picon-edit-delete',
						multi_select: true,
						click: function(e, rows){
							rows.pgrid_delete();
							update_products();
						}
					}
				]
			});
			var add_product = function(data){
				var serial = "";
				if (data.serialized) {
					while (!serial) {
						serial = prompt("This item is serialized. Please provide the serial:");
						if (serial == null)
							return;
					}
				}
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
				products_table.pgrid_add([{key: data.guid, values: [data.sku, data.name, serial, 'in-store', 1, data.unit_price, "", "", ""]}], function(){
					var cur_row = $(this);
					cur_row.data("product", data);
				});
				update_products();
			};
			// Delivery Dialog
			var delivery_select = $("#p_muid_delivery_select").buttonset().delegate("input", "click", function() {
				var rows = products_table.pgrid_get_selected_rows();
				if (!rows)
					return;
				rows.pgrid_set_value(4, $(this).val());
				update_products();
				delivery_dialog.dialog('close');
			});
			var delivery_dialog = $("#p_muid_delivery_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true
			});
			// Category Grid
			var category_grid = $("#p_muid_category_grid").pgrid({
				pgrid_hidden_cols: [1],
				pgrid_sort_col: 1,
				pgrid_sort_ord: "asc",
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
						var form = $("<div title=\"Data for "+row.pgrid_get_value(1)+" Payment\" />");
						form.dialog({
							bgiframe: true,
							autoOpen: true,
							modal: true,
							open: function(){
								form.html(data+"<br />");
								form.find("form").submit(function(){
									form.dialog('option', 'buttons').Done();
									return false;
								});
								if (payment_data.data) {
									$.each(payment_data.data, function(i, val){
										form.find(":input[name="+val.name+"]").val(val.value);
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
				$("<div title=\"Payment Amount\" />").each(function(){
					var amount_dialog = $(this);
					// A button for the current amount due.
					amount_dialog.append($("<button />").addClass("ui-state-default ui-corner-all").hover(function(){
						$(this).addClass("ui-state-hover");
					}, function(){
						$(this).removeClass("ui-state-hover");
					}).html($("#p_muid_amount_due").html()).css({"float": "left", "clear": "both", "min-height": "60px", "width": "100%", "text-align": "center", "margin": "2px"})
					.click(function(){
						amount_dialog.dialog("close");
						callback(round_to_dec($("#p_muid_amount_due").html()));
					}));
					// Buttons for common amounts.
					$.each(["1", "5", "10", "20", "50", "60", "80", "100"], function(){
						var cur_amount = this;
						amount_dialog.append($("<button />").addClass("ui-state-default ui-corner-all").hover(function(){
							$(this).addClass("ui-state-hover");
						}, function(){
							$(this).removeClass("ui-state-hover");
						}).html(String(cur_amount)).css({"float": "left", "min-height": "60px", "min-width": "60px", "text-align": "center", "margin": "2px"})
						.click(function(){
							amount_dialog.dialog("close");
							callback(round_to_dec(cur_amount));
						}));
					});
					// A button for a custom amount.
					amount_dialog.append($("<button />").addClass("ui-state-default ui-corner-all").hover(function(){
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
							callback(round_to_dec(cur_amount));
					}));
				}).dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					close: function(){$(this).remove();}
				});
			};
			<?php } ?>

			
			<?php if (!empty($this->entity->payments)) { foreach ($this->entity->payments as $key => $cur_payment) { ?>
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
					payments_table.pgrid_add([table_entry], function(){
						$(this).data("orig_key", <?php echo (int) $key; ?>).data("payment_data", data);
					});
				<?php } else { ?>
					payments_table.pgrid_add([table_entry], function(){
						$(this).data("orig_key", <?php echo (int) $key; ?>);
					});
				<?php } ?>
			})();
			<?php } } ?>

			$("#p_muid_comments_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function(){
						comments.val(comments_box.val());
						$(this).dialog('close');
					}
				}
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
				// Calculate ticket totals.
				rows.each(function(){
					var cur_row = $(this);
					var product = cur_row.data("product");
					<?php if ($pines->config->com_sales->com_customer) { ?>
					if (product.require_customer)
						require_customer = true;
					<?php } ?>
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
							alert("The discount lowers the product's price below the limit. The maximum discount possible for this item ["+product.name+"], is $"+round_to_dec(product.unit_price - product.floor)+" or "+round_to_dec((product.unit_price - product.floor) / product.unit_price * 100)+"%.");
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
					item_fees += cur_item_fees;
					subtotal += line_total;
					cur_row.pgrid_set_value(8, round_to_dec(line_total));
					cur_row.pgrid_set_value(9, round_to_dec(cur_item_fees));
				});
				total = subtotal + item_fees + taxes;
				$("#p_muid_subtotal").html(round_to_dec(subtotal));
				$("#p_muid_item_fees").html(round_to_dec(item_fees));
				$("#p_muid_taxes").html(round_to_dec(taxes));
				$("#p_muid_total").html(round_to_dec(total));

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
				$("#p_muid_amount_tendered").html(round_to_dec(amount_tendered));
				$("#p_muid_amount_due").html(round_to_dec(amount_due));
				$("#p_muid_change").html(round_to_dec(change));

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
					message += "<br /><div style=\"float: right; clear: right;\">Amount Received: <strong>$"+round_to_dec(total_cash)+"</strong></div>";

				var change = parseFloat($("#p_muid_change").html());
				if (change > 0 || kicked) {
					kicked = true;
					message += "<br /><div style=\"float: right; clear: right;\">Change Due: <strong>$"+round_to_dec(change)+"</strong></div><br style=\"clear: both;\" />";
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
			<input class="pf-field ui-widget-content" type="text" id="p_muid_customer" name="customer" size="24" value="<?php echo $this->entity->customer->guid ? htmlentities("{$this->entity->customer->guid}: \"{$this->entity->customer->name}\"") : ''; ?>" <?php if ($this->entity->status == 'invoiced' || $this->entity->status == 'paid' || $this->entity->status == 'voided') echo 'disabled="disabled" '; ?>/>
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
				<tr title="<?php echo $category->guid; ?>" class="<?php echo $category->children ? 'parent ' : ''; ?><?php echo isset($category->parent) ? "child {$category->parent->guid} " : ''; ?>">
					<td><?php echo isset($category->parent) ? $category->array_search($category->parent->children) + 1 : '0' ; ?></td>
					<td><?php echo htmlentities($category->name); ?></td>
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
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->products as $cur_product) {
						if (!isset($cur_product['entity']))
							continue;
						?>
				<tr title="<?php echo $cur_product['entity']->guid; ?>">
					<td><?php echo htmlentities($cur_product['entity']->sku); ?></td>
					<td><?php echo htmlentities($cur_product['entity']->name); ?></td>
					<td><?php echo htmlentities($cur_product['serial']); ?></td>
					<td><?php echo htmlentities($cur_product['delivery']); ?></td>
					<td><?php echo htmlentities($cur_product['quantity']); ?></td>
					<td><?php echo htmlentities($cur_product['price']); ?></td>
					<td><?php echo htmlentities($cur_product['discount']); ?></td>
					<td><?php echo htmlentities($cur_product['line_total']); ?></td>
					<td><?php echo htmlentities($cur_product['fees']); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<input type="hidden" id="p_muid_products" name="products" size="24" />
	</div>
	<div id="p_muid_delivery_dialog" title="Select Delivery Type" style="display: none;">
		<div id="p_muid_delivery_select" style="text-align: center; padding: 1em;">
			<input type="radio" name="delivery_type" id="p_muid_deliv_rad1" value="in-store" /><label for="p_muid_deliv_rad1">In Store</label>
			<input type="radio" name="delivery_type" id="p_muid_deliv_rad2" value="shipped" /><label for="p_muid_deliv_rad2">Shipped</label>
			<input type="radio" name="delivery_type" id="p_muid_deliv_rad3" value="pick-up" /><label for="p_muid_deliv_rad3">Pick Up</label>
		</div>
	</div>
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
				<button id="p_muid_payment_<?php echo $cur_payment_type->guid; ?>" class="ui-state-default ui-corner-all payment-button" type="button" style="margin-bottom: 2px;" value="<?php echo htmlentities(json_encode((object) array('guid' => $cur_payment_type->guid, 'name' => $cur_payment_type->name, 'minimum' => $cur_payment_type->minimum, 'maximum' => $cur_payment_type->maximum, 'processing_type' => $cur_payment_type->processing_type))); ?>">
					<span class="picon picon-32 picon-view-bank-account" style="display: block; padding-top: 32px; min-width: 50px; background-repeat: no-repeat; background-position: top center;"><?php echo htmlentities($cur_payment_type->name); ?></span>
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
			<input type="hidden" id="p_muid_payments" name="payments" size="24" />
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
	<div class="pf-element">
		<label><span class="pf-label">Comments</span>
			<input class="pf-field ui-widget-content ui-state-default ui-corner-all" type="button" value="Edit" onclick="$('#p_muid_comments_dialog').dialog('open');" /></label>
	</div>
	<div id="p_muid_comments_dialog" title="Comments" style="display: none;">
		<div class="pf-element pf-full-width">
			<textarea class="pf-field pf-full-width ui-widget-content" style="width: 96%; height: 100%;" rows="3" cols="35" id="p_muid_comments" name="comments"><?php echo $this->entity->comments; ?></textarea>
		</div>
	</div>
	<?php if (!empty($this->returns)) { ?>
	<div class="pf-element">
		<span class="pf-label">Associated Return(s)</span>
		<span class="pf-note">This sale has attached returns.</span>
		<div class="pf-group">
		<?php foreach($this->returns as $cur_return) { ?>
		<span class="pf-field">
			Return #<?php echo htmlentities($cur_return->id); ?>:
			<a href="<?php echo htmlentities(pines_url('com_sales', 'return/receipt', array('id' => $cur_return->guid))); ?>" onclick="window.open(this.href); return false;">Receipt</a>
			<?php if (gatekeeper('com_sales/editreturn')) { ?>
			<a href="<?php echo htmlentities(pines_url('com_sales', 'return/edit', array('id' => $cur_return->guid))); ?>" onclick="window.open(this.href); return false;">Edit</a>
			<?php } ?>
		</span><br />
		<?php } ?>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-buttons">
		<input type="hidden" id="p_muid_comment_saver" name="comment_saver" value="<?php echo htmlentities($this->entity->comments); ?>" />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>

		<input type="hidden" id="p_muid_sale_process_type" name="process" value="quote" />

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'paid') { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Tender" onclick="$('#p_muid_sale_process_type').val('tender'); pines.com_sales_run_drawer();" />
		<?php } ?>

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'paid' && $this->entity->status != 'invoiced') { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Invoice" onclick="$('#p_muid_sale_process_type').val('invoice'); pines.com_sales_run_submit();" />
		<?php } ?>

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'paid' && $this->entity->status != 'invoiced' && $this->entity->status != 'quoted') { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Quote" onclick="$('#p_muid_sale_process_type').val('quote'); pines.com_sales_run_submit();" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" value="Save" onclick="$('#p_muid_sale_process_type').val('save'); pines.com_sales_run_submit();" />
		<?php } ?>

		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'sale/list')); ?>');" value="Cancel" />
	</div>
</form>