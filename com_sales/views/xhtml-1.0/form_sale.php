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
				pgrid_multi_select: false
			});

			customer_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function() {
						var rows = customer_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!rows[0]) {
							alert("Please select a customer.");
							return;
						} else {
							var customer = rows[0];
						}
						customer_box.val(customer.key+": \""+customer.values[0]+"\"");
						$(this).dialog('close');
					}
				}
			});

			products_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'text',
						label: 'Code',
						selection_optional: true,
						keydown: function(eventObject){
							if (eventObject.keyCode == 13) {
								alert('Adding product: '+this.value);
							}
						}
					},
					{
						type: 'button',
						text: 'Add',
						extra_class: 'icon picon_16x16_actions_document-new',
						selection_optional: true,
						click: function(){
							product_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit',
						extra_class: 'icon picon_16x16_actions_document-open',
						double_click: true,
						click: function(e, rows){
							var row_data = products_table.pgrid_export_rows(rows);
							$("#cur_product_quantity").val(row_data[0].values[2]);
							$("#cur_product_cost").val(row_data[0].values[3]);
							product_dialog.dialog('open');
							rows.pgrid_delete();
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

		function customer_search(search_string) {
			customer_table.pgrid_get_all_rows().pgrid_delete();
			customer_dialog.dialog('open');
			$("#customer_dialog .complete").hide();
			$("#customer_dialog .loading").show();
			$.getJSON(
				"<?php echo $config->template->url("com_sales", "customersearch"); ?>",
				{q: search_string},
				function(data, textStatus){
					customer_table.pgrid_add(data);
					$("#customer_dialog .loading").hide();
					$("#customer_dialog .complete").show();
				}
			);
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
			<button type="button" id="customer_search_button"><span class="picon_16x16_actions_system-search" style="height: 16px; width: 16px; float: left"></span>Search</button>
		</div>
	</div>
	<div id="customer_dialog" title="Pick a Customer">
		<div class="loading">
			<p>Loading...</p>
		</div>
		<div class="complete">
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
		</div>
		<br class="spacer" />
	</div>
	<div class="element">
		<label><span class="label">Delivery Method</span>
			<select class="field" name="shipper">
				<option value="in-store">In Store</option>
				<option value="shipped">Shipped to Customer</option>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Payment Method</span>
			<input class="field" type="text" name="payment_method" size="20" value="<?php echo $this->entity->payment_method; ?>" /></label>
	</div>
	<div class="element full_width">
		<span class="label">Products</span>
		<div class="group">
			<div class="field">
				Code: <input type="text" id="product_code" name="product_code" size="20" />
			</div>
			<div class="field">
				<table id="products_table">
					<thead>
						<tr>
							<th>SKU</th>
							<th>Product</th>
							<th>Serial</th>
							<th>Quantity</th>
							<th>Price</th>
							<th>Line Total</th>
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