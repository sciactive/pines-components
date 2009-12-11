<?php
/**
 * Provides a form for the user to edit a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'New Sale' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Use this form to process a sale.';
?>
<form class="pform" method="post" id="sale_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
			<?php if (isset($this->entity->uid)) { ?>
		<span>Created By: <span class="date"><?php echo $config->user_manager->get_username($this->entity->uid); ?></span></span>
		<br />
			<?php } ?>
		<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
		<br />
		<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
	</div>
	<?php } ?>
	<script type="text/javascript">
		// <![CDATA[
		var customer_box;
		var customer_search_box;
		var customer_search_button;
		var customer_table;
		var customer_dialog;
		var products;
		var products_table;
		var product_code;
		var require_customer = false;

		// Number of decimal places to round to.
		var dec = <?php echo intval($config->com_sales->dec); ?>;
<?php
		$taxes_percent = array();
		$taxes_flat = array();
		foreach ($this->tax_fees as $cur_tax_fee) {
			if (!$cur_tax_fee->enabled)
				continue;
			foreach($cur_tax_fee->locations as $cur_location) {
				if (!$_SESSION['user']->ingroup($cur_location->guid))
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
?>
		var taxes_percent = JSON.parse("<?php echo addSlashes(json_encode($taxes_percent)) ?>");
		var taxes_flat = JSON.parse("<?php echo addSlashes(json_encode($taxes_flat)) ?>");

		function round_to_dec(value) {
			var rnd = Math.pow(10, dec);
			var mult = value * rnd;
			value = gaussianRound(mult);
			value /= rnd;
			value = value.toFixed(dec);
			return (value);
		}

		function gaussianRound(x) {
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
		}

		$(document).ready(function(){
			customer_box = $("#customer");
			customer_search_box = $("#customer_search");
			customer_search_button = $("#customer_search_button");
			customer_table = $("#customer_table");
			customer_dialog = $("#customer_dialog");
			products = $("#products");
			products_table = $("#products_table");
			product_code = $("#product_code");

			customer_search_box.keydown(function(eventObject){
				if (eventObject.keyCode == 13) {
					customer_search(this.value);
					return false;
				}
			});
			customer_search_button.click(function(){
				customer_search(customer_search_box.val());
			});

			customer_table.pgrid({
				pgrid_paginate: true,
				pgrid_multi_select: false,
				pgrid_double_click: function(){
					customer_dialog.dialog('option', 'buttons').Done();
				}
			});

			customer_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function(){
						var rows = customer_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!rows[0]) {
							alert("Please select a customer.");
							return;
						} else {
							var customer = rows[0];
						}
						customer_box.val(customer.key+": \""+customer.values[0]+"\"");
						customer_search_box.val("");
						customer_dialog.dialog('close');
					}
				}
			});

			products_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'text',
						label: 'Code: ',
						load: function(textbox){
							textbox.keydown(function(e){
								if (e.keyCode == 13) {
									var code = textbox.val();
									if (code == "") {
										alert("Please enter a product code.");
										return;
									}
									textbox.val("");
									var loader;
									$.ajax({
										url: "<?php echo $config->template->url('com_sales', 'productsearch'); ?>",
										type: "POST",
										dataType: "json",
										data: {"code": code},
										beforeSend: function(){
											loader = pines.alert('Retrieving product from server...', 'Product Search', 'icon picon_16x16_animations_throbber', {pnotify_hide: false});
										},
										complete: function(){
											loader.pnotify_remove();
										},
										error: function(XMLHttpRequest, textStatus){
											pines.error("An error occured while trying to lookup the product code:\n"+textStatus);
										},
										success: function(data){
											if (!data) {
												alert("No product was found with the code "+code+".");
												return;
											}
											var serial = "";
											if (data.serialized) {
												while (!serial) {
													serial = prompt("This item is serialized. Please provide the serial:");
												}
											}
											products_table.pgrid_add([{key: data.guid, values: [data.sku, data.name, serial, 1, data.unit_price, "", "", ""]}], function(){
												var cur_row = $(this);
												cur_row.data("product", data);
											});
											update_products();

											// delete this when done testing.
											$("textarea").val((JSON.stringify(data)));
										}
									});
								}
							});
						}
					},
					{type: 'separator'},
					{
						type: 'button',
						text: 'Serial',
						extra_class: 'icon picon_16x16_stock_generic_stock_id',
						double_click: true,
						click: function(e, rows){
							var product = rows.data("product");
							if (!product.serialized)
								return;
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
						text: 'Quantity',
						extra_class: 'icon picon_16x16_stock_data_stock_record-number',
						double_click: true,
						click: function(e, rows){
							var product = rows.data("product");
							if (product.serialized)
								return;
							var qty = rows.pgrid_get_value(4);
							do {
								qty = prompt("Please enter a quantity:", qty);
							} while ((parseInt(qty) < 1 || isNaN(parseInt(qty))) && qty != null);
							if (qty != null) {
								rows.pgrid_set_value(4, parseInt(qty));
								update_products();
							}
						}
					},
					{
						type: 'button',
						text: 'Discount',
						extra_class: 'icon picon_16x16_stock_form_stock_form-currency-field',
						click: function(e, rows){
							var product = rows.data("product");
							if (!product.discountable) {
								alert("The selected product is not discountable.")
								return;
							}
							var discount = rows.pgrid_get_value(6);
							do {
								discount = prompt("Enter an amount($#.##) or a percent (#.##%) to discount each unit:", discount);
							} while ((!discount.match(/^(\$-?\d+(\.\d+)?)|(-?\d+(\.\d+)?%)$/)) && discount != null);
							if (discount != null) {
								rows.pgrid_set_value(6, discount);
								update_products();
							}
						}
					},
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'icon picon_16x16_actions_edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_products();
						}
					}
				]
			});
		});

		function update_products() {
			var rows = products_table.pgrid_get_all_rows();
			if (!rows)
				return;
			var subtotal = 0;
			var taxes = 0;
			var itemfees = 0;
			var total = 0;
			require_customer = false;
			rows.each(function(){
				var cur_row = $(this);
				var product = cur_row.data("product");
				if (product.require_customer) {
					require_customer = true;
				}
				var price = parseFloat(cur_row.pgrid_get_value(5));
				var qty = parseInt(cur_row.pgrid_get_value(4));
				var discount = cur_row.pgrid_get_value(6);
				var cur_itemfees = 0;
				if (isNaN(price))
					price = 0;
				if (isNaN(qty))
					qty = 1;
				if (product.discountable) {
					if (discount.match(/^\$-?\d+(\.\d+)?$/)) {
						discount = parseFloat(discount.replace(/[^0-9.-]/, ''));
						price = price - discount;
					} else if (discount.match(/^-?\d+(\.\d+)?%$/)) {
						discount = parseFloat(discount.replace(/[^0-9.-]/, ''));
						price = price - (price * (discount / 100));
					}
					if (!isNaN(product.floor) && round_to_dec(price) < round_to_dec(product.floor)) {
						alert("The discount lowers the product's price below the limit. The maximum discount possible for this item ["+product.name+"], is $"+round_to_dec(product.unit_price - product.floor)+" or "+round_to_dec((product.unit_price - product.floor) / product.unit_price * 100)+"%.");
						cur_row.pgrid_set_value(6, "");
						update_products();
						return;
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
					cur_itemfees += (this.rate / 100) * line_total;
				});
				$.each(product.fees_flat, function(){
					cur_itemfees += this.rate * qty;
				});
				itemfees += cur_itemfees;
				subtotal += line_total;
				cur_row.pgrid_set_value(7, round_to_dec(line_total));
				cur_row.pgrid_set_value(8, round_to_dec(cur_itemfees));
			});
			total = subtotal + itemfees + taxes;
			$("#subtotal").html(round_to_dec(subtotal));
			$("#itemfees").html(round_to_dec(itemfees));
			$("#taxes").html(round_to_dec(taxes));
			$("#total").html(round_to_dec(total));
		}

		function customer_search(search_string) {
			var loader;
			$.ajax({
				url: "<?php echo $config->template->url("com_sales", "customersearch"); ?>",
				type: "POST",
				dataType: "json",
				data: {"q": search_string},
				beforeSend: function(){
					loader = pines.alert('Searching for customers...', 'Customer Search', 'icon picon_16x16_animations_throbber', {pnotify_hide: false});
					customer_table.pgrid_get_all_rows().pgrid_delete();
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to find customers:\n"+textStatus);
				},
				success: function(data){
					if (!data) {
						alert("No customers were found that matched the query.");
						return;
					}
					customer_dialog.dialog('open');
					customer_table.pgrid_add(data);
				}
			});
		}
		// ]]>
	</script>
	<div class="element">
		<span class="label">Customer</span>
		<span class="note">Enter part of a name, company, email, or phone # to search.</span>
		<div class="group">
			<input class="field" type="text" id="customer" name="customer" size="20" disabled="disabled" value="<?php echo ($this->entity->customer->guid) ? "{$this->entity->customer->guid}: \"{$this->entity->customer->name}\"" : 'No Customer Selected'; ?>" />
			<br />
			<input class="field" type="text" id="customer_search" name="customer_search" size="20" />
			<button type="button" id="customer_search_button"><span class="picon_16x16_actions_system-search" style="padding-left: 16px; background-repeat: no-repeat;">Search</span></button>
		</div>
	</div>
	<div id="customer_dialog" title="Pick a Customer">
		<table id="customer_table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Company</th>
					<th>Job Title</th>
					<th>Address 1</th>
					<th>Address 2</th>
					<th>City</th>
					<th>State</th>
					<th>Zip</th>
					<th>Home Phone</th>
					<th>Work Phone</th>
					<th>Cell Phone</th>
					<th>Fax</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
					<td>----------------------</td>
				</tr>
			</tbody>
		</table>
		<br class="spacer" />
	</div>
	<div class="element">
		<label><span class="label">Delivery Method</span>
			<select class="field" name="shipper">
				<option value="in-store">In Store</option>
				<option value="shipped">Shipped to Customer</option>
			</select></label>
	</div>
	<div class="element full_width">
		<span class="label">Products</span>
		<div class="group">
			<div class="field">
				<table id="products_table">
					<thead>
						<tr>
							<th>SKU</th>
							<th>Product</th>
							<th>Serial</th>
							<th>Quantity</th>
							<th>Price</th>
							<th>Discount</th>
							<th>Line Total</th>
							<th>Fees</th>
						</tr>
					</thead>
					<tbody>
						<?php if (is_array($this->entity->products)) { foreach ($this->entity->products as $cur_product) {
								if (is_null($cur_product['entity']))
									continue;
								?>
						<tr title="<?php echo $cur_product['entity']->guid; ?>">
							<td><?php echo $cur_product['entity']->sku; ?></td>
							<td><?php echo $cur_product['entity']->name; ?></td>
							<td><?php echo $cur_product['serial']; ?></td>
							<td><?php echo $cur_product['quantity']; ?></td>
							<td><?php echo $cur_product['price']; ?></td>
							<td><?php echo $config->run_sales->round(intval($cur_product['quantity']) * floatval($cur_product['price']), $config->com_sales->dec); ?></td>
						</tr>
						<?php } } ?>
					</tbody>
				</table>
			</div>
			<input class="field" type="hidden" id="products" name="products" size="20" />
		</div>
	</div>
	<div class="element full_width">
		<span class="label">Ticket Totals</span>
		<div class="group">
			<div class="field" style="float: right; font-weight: bold; text-align: right;">
				<span class="label">Subtotal</span><span class="field" id="subtotal">0.00</span><br />
				<span class="label">Item Fees</span><span class="field" id="itemfees">0.00</span><br />
				<span class="label">Tax</span><span class="field" id="taxes">0.00</span><br />
				<hr /><br />
				<span class="label">Total</span><span class="field" id="total">0.00</span>
			</div>
			<hr class="field" style="clear: both;" />
		</div>
	</div>
	<div class="element">
		<label><span class="label">Payment Method</span>
			<input class="field" type="text" name="payment_method" size="20" value="<?php echo $this->entity->payment_method; ?>" /></label>
	</div>
	<div class="element full_width">
		<label><span class="label">Comments</span>
			<div class="group">
				<div class="field">
					<textarea rows="3" cols="35" name="comments" style="width: 100%;"><?php echo $this->entity->comments; ?></textarea>
				</div>
			</div></label>
	</div>
	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listsales'); ?>';" value="Cancel" />
	</div>
</form>