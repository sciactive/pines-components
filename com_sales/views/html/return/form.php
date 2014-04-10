<?php
/**
 * Provides a form for the user to edit a return.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

if (!isset($this->entity->guid))
	$this->title = 'New Return';
elseif ($this->entity->status == 'quoted')
	$this->title = 'Quoted Return ['.htmlspecialchars($this->entity->id).']';
elseif ($this->entity->status == 'processed')
	$this->title = 'Processed Return ['.htmlspecialchars($this->entity->id).']';
elseif ($this->entity->status == 'voided')
	$this->title = 'Voided Return ['.htmlspecialchars($this->entity->id).']';
$this->note = 'Use this form to edit a return.';
$pines->com_pgrid->load();
if ($pines->config->com_sales->com_customer)
	$pines->com_customer->load_customer_select();
if ($pines->config->com_sales->per_item_salesperson)
	$pines->com_hrm->load_employee_select();
if ($pines->config->com_sales->autocomplete_product)
	$pines->com_sales->load_product_select();
?>
<style type="text/css">
	<?php if ($this->entity->specials) { ?>
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
	<?php } ?>
	#p_muid_form .p_muid_payment_data_throbber.hide {
		display: none;
	}
</style>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'return/save')); ?>">
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
		pines(function(){
			var products = $("#p_muid_products"),
				products_table = $("#p_muid_products_table"),
				payments_table = $("#p_muid_payments_table"),
				payments = $("#p_muid_payments");
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
			$added_specials = array();
			foreach ((array) $this->entity->specials as $cur_special) {
				$added_specials[] = array(
					'name' => $cur_special['name'],
					'before_tax' => $cur_special['before_tax'],
					'discount' => $cur_special['discount']
				);
			}
?>
			var taxes_percent = <?php echo json_encode($taxes_percent); ?>,
				taxes_flat = <?php echo json_encode($taxes_flat); ?>,
				drawer_kickers = <?php echo json_encode($drawer_kickers); ?>,
				// Unlike on the sale form, this includes all associated specials.
				added_specials = <?php echo json_encode($added_specials); ?>,
				status = <?php echo json_encode($this->entity->status); ?>;

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

			<?php if ($pines->config->com_sales->com_customer && $this->entity->status != 'processed' && $this->entity->status != 'voided' && !isset($this->entity->sale->guid)) { ?>
			$("#p_muid_customer").customerselect();
			<?php } if ($pines->config->com_sales->per_item_salesperson) { ?>
			$("#p_muid_salesperson").employeeselect();
			<?php } ?>

			<?php if ($this->entity->status == 'processed' || $this->entity->status == 'voided' || $this->entity->specials) { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
				pgrid_hidden_cols: [4],
				pgrid_paginate: false,
				pgrid_toolbar: false
			});
			<?php } else { ?>
			products_table.pgrid({
				pgrid_view_height: "160px",
				pgrid_hidden_cols: [4],
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					<?php if (!isset($this->entity->sale->guid)) { ?>
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
									url: <?php echo json_encode(pines_url('com_sales', 'product/codesearch')); ?>,
									type: "POST",
									dataType: "json",
									data: {"code": code},
									beforeSend: function(){
										loader = $.pnotify({
											title: 'Product Search',
											text: 'Retrieving product from server...',
											icon: 'picon picon-throbber',
											nonblock: true,
											hide: false,
											history: false
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
							<?php } 
                                                        if ($pines->config->com_sales->no_autocomplete_product) { ?>

                                                        textbox.on('product_selected', function(){
                                                           select(textbox.val()); 
                                                        });
                                                        <?php } else { ?>
                                                            textbox.keydown(function(e){
								if (e.keyCode == 13)
									select(textbox.val());
							});
                                                        <?php } ?>
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
							serial_dialog
							.dialog("option", "title", "Provide Serial for "+product.name)
							.dialog("option", "buttons", buttons)
							.dialog("open");
						}
					},
					<?php } if (!isset($this->entity->sale->guid) || gatekeeper('com_sales/newreturnpartial')) { ?>
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
					<?php } ?>
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
					{
						type: 'button',
						title: 'Return Checklist',
						extra_class: 'picon picon-checkbox',
						multi_select: false,
						click: function(e, rows){
							checklist_form(rows);
						}
					},
					<?php if (!isset($this->entity->sale->guid) || gatekeeper('com_sales/newreturnpartial')) { ?>
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
					},
					<?php } ?>
					{}
				]
			});
			var add_product = function(data, success){
				var serial = "";
				data.salesperson = <?php echo json_encode($_SESSION['user']->guid.': '.$_SESSION['user']->name); ?>;
				if (data.serialized) {
					var buttons = {
						"Done": function(){
							serial = serial_box.val();
							if (serial == "") {
								alert("Please provide a serial number.");
								return;
							}
							products_table.pgrid_add([{key: data.guid, values: [pines.safe(data.sku), pines.safe(data.name), pines.safe(serial), '', 1, pines.safe(data.unit_price), "", "", "", "", pines.safe(data.salesperson)]}], function(){
								var cur_row = $(this);
								cur_row.data("product", data);
							});
							update_products();
							serial_dialog.dialog("close");
							if (success)
								success();
						}
					};
					serial_dialog.dialog("option", "title", "Provide Serial for "+data.name)
					.dialog("option", "buttons", buttons)
					.dialog("open");
					serial_box.val("");
					return;
				}
				products_table.pgrid_add([{key: data.guid, values: [pines.safe(data.sku), pines.safe(data.name), pines.safe(serial), "", 1, pines.safe(data.unit_price), "", "", "", "", pines.safe(data.salesperson)]}], function(){
					var cur_row = $(this);
					cur_row.data("product", data);
				});
				update_products();
				if (success)
					success();
			};
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
							url: <?php echo json_encode(pines_url('com_sales', 'category/products')); ?>,
							type: "POST",
							dataType: "json",
							data: {"id": row.attr("title")},
							beforeSend: function(){
								loader = $.pnotify({
									title: 'Product Search',
									text: 'Retrieving products from server...',
									icon: 'picon picon-throbber',
									nonblock: true,
									hide: false,
									history: false
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
			// Checklist Form
			var checklist_dialog = $("#p_muid_checklist_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 450,
				open: function() {
					$("#p_muid_checklist").val("");
				}
			});
			var checklist_form = function(row){
				var checklist_form = $("#p_muid_checklist_form");
				checklist_form.html("");
				var product = row.data("product");
				var checklists = row.data("return_checklists");
				if (!checklists)
					checklists = {};
				var qty = parseInt(row.pgrid_get_value(5));
				var price = parseFloat(row.pgrid_get_value(6));
				if (!product.return_checklists || !product.return_checklists.length) {
					alert("This product has no return checklists.");
					return;
				}
				$.each(product.return_checklists, function(i, cur_checklist){
					checklist_form.append('<div class="pf-element pf-heading"><h3>'+pines.safe(cur_checklist.label)+'</h3></div>');
					$.each(cur_checklist.conditions, function(i, cur_condition){
						var cur_element = $('<div class="pf-element pf-full-width condition"><span class="pf-label">'+pines.safe(cur_condition.condition)+'</span></div>');
						// Make checkboxes.
						if (cur_condition.always)
							cur_element.append('<span class="pf-field">Always Charged</span>')
						else
							cur_element.append('<input class="pf-field" type="checkbox" name="'+pines.safe(cur_condition.condition)+'" value="ON"'+((checklists["G"+cur_checklist.guid] && checklists["G"+cur_checklist.guid]["C"+cur_condition.condition]) ? ' checked="checked"' : '')+' />')
						var amount;
						// Calculate return fee.
						switch (cur_condition.type) {
							case "flat_rate":
							default:
								amount = "$"+pines.safe(cur_condition.amount)+" x "+pines.safe(qty)+" ($"+pines.safe(round_to_dec(cur_condition.amount * qty, true))+")";
								break;
							case "percentage":
								amount = pines.safe(cur_condition.amount)+"% ($"+pines.safe(round_to_dec(round_to_dec(price * (cur_condition.amount / 100)) * qty, true))+")";
								break;
						}
						cur_element.append('<div style="float: right;">'+pines.safe(amount)+'</div>').data("guid", cur_checklist.guid).appendTo(checklist_form);
					});
				});
				checklist_dialog.dialog("option", "buttons", {
					'Done': function(){
						checklists = {};
						checklist_form.children(".condition").each(function(){
							var cur_condition = $(this);
							var guid = cur_condition.data("guid")
							var checkbox = cur_condition.find("input[type=checkbox]");
							var condition = checkbox.attr("name");
							if (!checkbox.length)
								return;
							// Key needs to have a string value.
							if (!checklists["G"+guid])
								checklists["G"+guid] = {};
							checklists["G"+guid]["C"+condition] = checkbox.is(":checked");
						});
						row.data("return_checklists", checklists);
						checklist_dialog.dialog('close');
						update_products();
					}
				});
				checklist_dialog.dialog('open');
			};
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
			<?php }
			} ?>

			<?php if (!$pines->config->com_sales->per_item_salesperson) { ?>
			products_table.pgrid_import_state({pgrid_hidden_cols: [4, 11]});
			<?php } ?>

			// Load the data for any existing products.
			var loader;
			products_table.pgrid_get_all_rows().each(function(){
				if (!loader)
					loader = $.pnotify({
						title: 'Loading Products',
						text: 'Retrieving product information from server...',
						icon: 'picon picon-throbber',
						nonblock: true,
						hide: false,
						history: false
					});
				var cur_row = $(this);
				var cur_export = cur_row.pgrid_export_rows();
				var cur_guid = cur_export[0].key;
				$.ajax({
					url: <?php echo json_encode(pines_url('com_sales', 'product/codesearch')); ?>,
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

			<?php if ($this->entity->status == 'processed' || $this->entity->status == 'voided') { ?>
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
					},
					{
						type: 'label',
						extra_class: 'p_muid_payment_data_throbber picon picon-throbber hide',
						label: '&nbsp;'
					}
				]
			});

			payments_table.data_form = function(row){
				var payment_data = row.data("payment_data");
				$.ajax({
					url: <?php echo json_encode(pines_url('com_sales', 'forms/payment')); ?>,
					type: "POST",
					dataType: "html",
					data: {"name": payment_data.processing_type, "id": $("#p_muid_form [name=id]").val(), "customer": $("#p_muid_customer").val(), "type": "return", "sale_id": $("#p_muid_form [name=sale_id]").val()},
					beforeSend: function(){
						$(":button", "#p_muid_buttons").attr("disabled", "disabled");
						$(".p_muid_payment_data_throbber").removeClass("hide");
					},
					complete: function(){
						$(":button", "#p_muid_buttons").removeAttr("disabled");
						$(".p_muid_payment_data_throbber").addClass("hide");
					},
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

			$("button.payment-button", "#p_muid_form").click(function(){
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
				var amount_due = $("#p_muid_amount_due").html();
				$("<div title=\"Payment Amount\" style=\"text-align: center; font-size: 22px; font-weight: bold;\"></div>").each(function(){
					var amount_dialog = $(this);
					// A button for the current amount due.
					if (parseFloat(amount_due) <= 0 && parseFloat($("#p_muid_change").html()) > 0)
						amount_due = "-"+$("#p_muid_change").html();
					amount_dialog.append($("<button class=\"btn btn-primary\"></button>").html("$"+amount_due).css({"min-height": "70px", "width": "100%", "text-align": "center", "margin": "2px"})
					.click(function(){
						amount_dialog.dialog("close");
						callback(round_to_dec(amount_due, true));
					}));
					// Buttons for common amounts.
					$.each(["1", "5", "10", "20", "30", "40", "50", "60", "70", "80", "90", "100"], function(){
						var cur_amount = this,
						button_style = (parseFloat(cur_amount) > parseFloat(amount_due)) ? "btn-success" : "btn-info";
						amount_dialog.append($("<button class=\"btn "+button_style+"\"></button>").html("$"+String(cur_amount)).css({"min-height": "70px", "min-width": "70px", "text-align": "center", "margin": "2px"})
						.click(function(){
							amount_dialog.dialog("close");
							callback(round_to_dec(cur_amount, true));
						}));
					});
					// A button for a custom amount.
					amount_dialog.append($("<button class=\"btn btn-primary\"></button>").html("Another Amount").css({"min-height": "70px", "width": "100%", "text-align": "center", "margin": "2px"})
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
					width: 400,
					close: function(){$(this).remove();}
				});
			};
			<?php } ?>

			
			<?php if (!empty($this->entity->payments)) { foreach ($this->entity->payments as $key => $cur_payment) { ?>
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
					var new_row = $(this).data("orig_key", <?php echo (int) $key; ?>);
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
			// Put the dialogs back in the form.
			$("#p_muid_form").submit(function(){
				$(this).append($("#p_muid_comments_dialog").hide());
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
				var row_export = [];
				var return_fees = 0;
				<?php if ($pines->config->com_sales->com_customer) { ?>
				require_customer = false;
				<?php } ?>
				rows.each(function(){
					var cur_row = $(this);
					var product = cur_row.data("product");
					var checklists = cur_row.data("return_checklists");
					if (!checklists)
						checklists = {};
					<?php if ($pines->config->com_sales->com_customer) { ?>
					if (product.require_customer)
						require_customer = true;
					<?php } ?>
					// Calculate ticket totals.
					var price = parseFloat(cur_row.pgrid_get_value(6));
					var qty = parseInt(cur_row.pgrid_get_value(5));
					var discount = cur_row.pgrid_get_value(7);
					var cur_item_fees = 0;
					var cur_return_fee = 0;
					if (isNaN(price))
						price = 0;
					if (isNaN(qty))
						qty = 1;
					// Return fee needs to be calculated before discount.
					$.each(product.return_checklists, function(i, cur_checklist){
						$.each(cur_checklist.conditions, function(i, cur_condition){
							// Check if this should be added.
							if (cur_condition.always || (checklists["G"+cur_checklist.guid] && checklists["G"+cur_checklist.guid]["C"+cur_condition.condition])) {
								// Calculate return fee.
								switch (cur_condition.type) {
									case "flat_rate":
									default:
										cur_return_fee += round_to_dec(cur_condition.amount * qty);
										break;
									case "percentage":
										cur_return_fee += round_to_dec(round_to_dec(price * (cur_condition.amount / 100)) * qty);
										break;
								}
							}
						});
					});
					if (product.discountable && discount != "") {
						var discount_price;
						if (discount.match(/^\$-?\d+(\.\d+)?$/)) {
							discount = parseFloat(discount.replace(/[^0-9.\-]/, ''));
							discount_price = price - discount;
						} else if (discount.match(/^-?\d+(\.\d+)?%$/)) {
							discount = parseFloat(discount.replace(/[^0-9.\-]/, ''));
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
					return_fees += round_to_dec(cur_return_fee);
					cur_row.pgrid_set_value(8, pines.safe(round_to_dec(line_total, true)));
					cur_row.pgrid_set_value(9, pines.safe(round_to_dec(cur_item_fees, true)));
					cur_row.pgrid_set_value(10, pines.safe(round_to_dec(cur_return_fee, true)));
					var cur_row_export = cur_row.pgrid_export_rows()[0];
					cur_row_export.return_checklists = checklists;
					row_export.push(cur_row_export);
				});
				$("#p_muid_subtotal").html(round_to_dec(subtotal, true));
				// Now that we know the subtotal, we can use it for specials.
				var total_before_tax_specials = 0;
				var total_specials = 0;
				$.each(added_specials, function(i, cur_special){
					specials.push(cur_special);
					if (cur_special.before_tax)
						total_before_tax_specials += cur_special.discount;
					total_specials += cur_special.discount;
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
					special_box.append("<div class=\"special well\"><div class=\"special_name\">"+pines.safe(cur_special.name)+"</div><div class=\"special_discount\">"+(cur_special.before_tax ? "<small>(before tax)</small> " : "")+"$"+round_to_dec(cur_special.discount, true)+"</div></div>");
				});
				$("#p_muid_specials_total").html(round_to_dec(total_specials, true));
				if (round_to_dec(total_specials) > 0)
					$("#p_muid_specials_total_container").show();
				else
					$("#p_muid_specials_total_container").hide();
				$("#p_muid_item_fees").html(round_to_dec(item_fees, true));
				$("#p_muid_return_fees").html(round_to_dec(return_fees, true));
				$("#p_muid_taxes").html(round_to_dec(taxes, true));
				total = (round_to_dec(subtotal) - round_to_dec(total_specials)) + round_to_dec(item_fees) + round_to_dec(taxes) - round_to_dec(return_fees);
				$("#p_muid_total").html(round_to_dec(total, true));

				// Update the products input element.
				products.val(JSON.stringify(row_export));

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
						var amount = parseFloat(cur_row.pgrid_get_value(2).replace(/[^0-9.\-]/g, ""));
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

				if (round_to_dec(change))
					$("#p_muid_overpaid").show();
				else
					$("#p_muid_overpaid").hide();
			};

			pines.com_sales_run_check = function(use_drawer){
				if (require_customer && !$("#p_muid_customer").val().match(/^\d+/)) {
					alert("One of the products on this return requires a customer. Please select a customer before continuing.");
					return;
				}
				var product_val = products.val();
				if (product_val == '[]') {
					alert("Please select at least one product first.");
					return;
				}
				if (use_drawer)
					pines.com_sales_run_drawer();
				else
					pines.com_sales_run_submit();
			};

			<?php if ($pines->config->com_sales->cash_drawer) { ?>
			pines.com_sales_run_drawer = function(){
				if (!check_return_total()) return false;
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
					message += "<br /><div style=\"float: right; clear: right;\">Change Due: <strong>$"+round_to_dec(total_cash, true)+"</strong></div><br style=\"clear: both;\" />";

				if (kicked)
					pines.drawer_open(keep_checking, message);
				else
					$("#p_muid_form").submit();
			};
			<?php } else { ?>
			pines.com_sales_run_drawer = function(){
				if (!check_return_total()) return false;
				pines.com_sales_run_submit();
			};
			<?php } ?>

			pines.com_sales_run_submit = function(){
				if (!check_return_total()) return false;
				$(":button, :submit, :reset", "#p_muid_form .pf-buttons").attr("disabled", "disabled").addClass("disabled");
				$("#p_muid_form").submit();
			};

			var check_return_total = function(){
				if ($("#p_muid_amount_tendered").html() == $("#p_muid_total").html())
					return true;
				else {
					alert("The return total is not equal to the amount returned. Please make sure all returned payments are correct.");
					return false;
				}
			};

			// Load any initial products.
			update_products();
		});
	</script>
	<?php if ($pines->config->com_sales->com_customer) { ?>
	<div class="pf-element">
		<label>
			<span class="pf-label">Customer</span>
			<?php if ($this->entity->status != 'processed' && $this->entity->status != 'voided' && !isset($this->entity->sale->guid)) { ?>
			<span class="pf-note">Enter part of a name, company, email, or phone # to search.</span>
			<?php } ?>
			<input class="pf-field" type="text" id="p_muid_customer" name="customer" size="24" value="<?php echo $this->entity->customer->guid ? htmlspecialchars("{$this->entity->customer->guid}: \"{$this->entity->customer->name}\"") : ''; ?>" <?php if ($this->entity->status == 'processed' || $this->entity->status == 'voided' || isset($this->entity->sale->guid)) echo 'disabled="disabled" '; ?>/>
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
				<tr title="<?php echo htmlspecialchars($category->guid); ?>" class="<?php echo $category->children ? 'parent ' : ''; ?><?php echo isset($category->parent) ? htmlspecialchars("child ch_{$category->parent->guid} ") : ''; ?>">
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
					<th>Unused</th>
					<th>Quantity</th>
					<th>Price</th>
					<th>Discount</th>
					<th>Line Total</th>
					<th>Fees</th>
					<th>Return Fee</th>
					<th>Salesperson</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->products as $cur_product) {
						if (!isset($cur_product['entity']))
							continue;
						$cur_id = uniqid();
						?>
				<tr id="p_muid_tr_<?php echo htmlspecialchars($cur_id); ?>" title="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>">
					<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
					<td><?php echo htmlspecialchars($cur_product['entity']->name); ?></td>
					<td><?php echo htmlspecialchars($cur_product['serial']); ?></td>
					<td>NA<script type="text/javascript">
						pines(function(){
							$("#p_muid_tr_<?php echo htmlspecialchars($cur_id); ?>").data("return_checklists", <?php echo json_encode((array) $cur_product['return_checklists']); ?>);
						});
					</script></td>
					<td><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['price']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['discount']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['line_total']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['fees']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['return_fee']); ?></td>
					<td><?php echo htmlspecialchars($cur_product['salesperson']->guid.': '.$cur_product['salesperson']->name); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<input type="hidden" id="p_muid_products" name="products" />
	</div>
	<div id="p_muid_serial_dialog" title="Provide Serial" style="display: none;">
		<div class="pf-form">
			<div class="pf-element">
				<label><span class="pf-label">Serial Number</span>
					<input class="pf-field" type="text" id="p_muid_serial_number" name="serial_number" size="24" value="" /></label>
			</div>
		</div>
		<br />
	</div>
	<div id="p_muid_checklist_dialog" title="Return Checklist" style="display: none;">
		<div class="pf-form" id="p_muid_checklist_form">
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
					<input class="pf-field" type="text" id="p_muid_salesperson" name="item_salesperson" size="24" value="" />
				</label>
			</div>
		</div>
		<br />
	</div>
	<?php } if ($this->entity->specials) { ?>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Specials</span>
		<span class="pf-field">Because the sale has specials, you cannot change the products on this return.</span>
		<br class="pf-clearing" />
		<div id="p_muid_specials"></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Return Totals</span>
		<div class="pf-group">
			<div class="pf-field" style="float: right; font-size: 1.2em; text-align: right;">
				<span class="pf-label">Subtotal</span><span class="pf-field" id="p_muid_subtotal">0.00</span><br />
				<div id="p_muid_specials_total_container" style="display: none;">
					<span class="pf-label">Specials</span><span class="pf-field">(<span id="p_muid_specials_total">0.00</span>)</span><br />
				</div>
				<span class="pf-label">Item Fees</span><span class="pf-field" id="p_muid_item_fees">0.00</span><br />
				<span class="pf-label">Tax</span><span class="pf-field" id="p_muid_taxes">0.00</span><br />
				<span class="pf-label">Return Fees</span><span class="pf-field">(<span id="p_muid_return_fees">0.00</span>)</span><br />
				<hr /><br />
				<span class="pf-label">Total</span><span class="pf-field" id="p_muid_total">0.00</span>
			</div>
			<hr class="pf-field" style="clear: both;" />
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Payments Returned</span>
		<?php if ($this->entity->status != 'processed' && $this->entity->status != 'voided') { ?>
		<div class="pf-note">
			<div style="text-align: left;">
				<?php foreach ($this->payment_types as $cur_payment_type) { ?>
				<button id="p_muid_payment_<?php echo htmlspecialchars($cur_payment_type->guid); ?>" class="btn payment-button" type="button" style="margin-bottom: 2px;" value="<?php echo htmlspecialchars(json_encode((object) array('guid' => $cur_payment_type->guid, 'name' => $cur_payment_type->name, 'minimum' => $cur_payment_type->minimum, 'maximum' => $cur_payment_type->maximum, 'processing_type' => $cur_payment_type->processing_type))); ?>">
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
				<small>A payment with a negative amount means the customer will be charged to cover return fees. (Ex -5.00 means a $5.00 charge.)</small>
			</div>
			<input type="hidden" id="p_muid_payments" name="payments" />
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Returned</span>
		<div class="pf-group">
			<div class="pf-field" style="float: right; font-size: 1.2em; text-align: right;">
				<span class="pf-label">Amount Returned</span><span class="pf-field" id="p_muid_amount_tendered">0.00</span><br />
				<span class="pf-label">Amount Remaining</span><span class="pf-field" id="p_muid_amount_due">0.00</span><br />
				<div id="p_muid_overpaid" style="display: none; font-weight: bold;">
					<span class="pf-label">Overpaid</span><span style="font-weight: bold;" class="pf-field alert-error" id="p_muid_change">0.00</span>
				</div>
			</div>
			<hr class="pf-field" style="clear: both;" />
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Return Information</span>
		<div class="pf-group">
			<input class="pf-field btn" type="button" value="Edit Comments" onclick="$('#p_muid_comments_dialog').dialog('open');" />
			<hr class="pf-field" style="clear: both; margin-top: 15px;" />
		</div>
	</div>
	<div id="p_muid_comments_dialog" title="Comments" style="display: none;">
		<div class="pf-element pf-full-width">
			<textarea class="pf-field pf-full-width" style="width: 96%; height: 100%;" rows="3" cols="35" id="p_muid_comments" name="comments"><?php echo htmlspecialchars($this->entity->comments); ?></textarea>
		</div>
	</div>
	<?php if (isset($this->entity->sale->guid)) { ?>
	<div class="pf-element">
		<span class="pf-label">Attached Sale</span>
		<span class="pf-note">This return is attached to a sale.</span>
		<span class="pf-field">
			<a data-entity="<?php echo htmlspecialchars($this->entity->sale->guid); ?>" data-entity-context="com_sales_sale">Sale <?php echo htmlspecialchars($this->entity->sale->id); ?></a>
		</span>
	</div>
	<?php } ?>
	<div class="pf-element pf-buttons" id="p_muid_buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<?php } elseif ( isset($this->entity->sale->guid) ) { ?>
		<input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($this->entity->sale->guid); ?>" />
		<?php } ?>

		<input type="hidden" id="p_muid_return_process_type" name="process" value="quote" />

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'processed') { ?>
		<input class="pf-button btn btn-primary" type="button" value="Process" onclick="$('#p_muid_return_process_type').val('process'); pines.com_sales_run_check(true);" />
		<?php } ?>

		<?php if ($this->entity->status != 'voided' && $this->entity->status != 'processed' && $this->entity->status != 'quoted') { ?>
		<input class="pf-button btn btn-primary" type="button" value="Quote" onclick="$('#p_muid_return_process_type').val('quote'); pines.com_sales_run_check();" />
		<?php } else { ?>
		<input class="pf-button btn btn-primary" type="button" value="Save" onclick="$('#p_muid_return_process_type').val('save'); pines.com_sales_run_check();" />
		<?php } ?>

		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'return/list'))); ?>);" value="Cancel" />
	</div>
</form>