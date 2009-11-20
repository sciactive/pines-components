<?php
/**
 * Provides a form for the user to edit a PO.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Purchase Order' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide PO details in this form.';
?>
<form class="pform" method="post" id="po_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(document).ready(function(){
			var products = $("#products");
			var products_table = $("#products_table");
			var available_products_table = $("#available_products_table");
			var product_dialog = $("#product_dialog");

			products_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Product',
						extra_class: 'icon picon_16x16_actions_list-add',
						selection_optional: true,
						click: function(){
							product_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Product',
						extra_class: 'icon picon_16x16_actions_list-remove',
						click: function(e, rows){
							rows.pgrid_delete();
							update_products();
						}
					}
				]
			});

			// Needs to be gridified before it's hidden.
			available_products_table.pgrid({
				pgrid_multi_select: false,
				pgrid_paginate: false,
				pgrid_height: '400px;'
			});

			// Product Dialog
			product_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function() {
						var cur_product_quantity = $("#cur_product_quantity").val();
						var cur_product_cost = $("#cur_product_cost").val();
						var cur_product = available_products_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!cur_product[0]) {
							$("<div title=\"Alert\">Please select a product.</div>").dialog({
								bgiframe: true,
								modal: true,
								buttons: {
									Ok: function(){
										$(this).dialog("close");
									}
								}
							});
							return;
						}
						if (cur_product_quantity == "" || cur_product_cost == "") {
							$("<div title=\"Alert\">Please provide both a quantity and a cost for this product.</div>").dialog({
								bgiframe: true,
								modal: true,
								buttons: {
									Ok: function(){
										$(this).dialog("close");
									}
								}
							});
							return;
						}
						var new_product = [{
							key: cur_product[0].key,
							values: [
								cur_product[0].values[0],
								cur_product[0].values[1],
								cur_product_quantity,
								cur_product_cost
							]
						}];
						products_table.pgrid_add(new_product);
						update_products();
						$(this).dialog('close');
					}
				}
			});

			function update_products() {
				available_products_table.pgrid_get_selected_rows().pgrid_deselect_rows();
				$("#cur_product_quantity").val("");
				$("#cur_product_cost").val("");
				products.val(JSON.stringify(products_table.pgrid_get_all_rows().pgrid_export_rows()));
			}
		});
		// ]]>
	</script>
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
	<div class="element">
		<label><span class="label">Vendor</span>
			<select class="field" name="vendor">
				<option value="null">-- None --</option>
				<?php foreach ($this->vendors as $cur_vendor) { ?>
				<option value="<?php echo $cur_vendor->guid; ?>"<?php echo $this->entity->vendor == $cur_vendor->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_vendor->name; ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Shipper</span>
			<select class="field" name="shipper">
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo $cur_shipper->guid; ?>"<?php echo $this->entity->shipper == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_shipper->name; ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">PO #</span>
			<input class="field" type="text" name="po_number" size="20" value="<?php echo $this->entity->po_number; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Reference #</span>
			<input class="field" type="text" name="reference_number" size="20" value="<?php echo $this->entity->reference_number; ?>" /></label>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(document).ready(function(){
				$("#eta").datepicker({
					dateFormat: "yy-mm-dd"
				});
			});
			// ]]>
		</script>
		<label><span class="label">ETA</span>
			<input class="field" type="text" id="eta" name="eta" size="20" value="<?php echo $this->entity->eta; ?>" /></label>
	</div>
	<div class="element full_width">
		<span class="label">Products</span>
		<div class="group">
			<table id="products_table">
				<thead>
					<tr>
						<th>SKU</th>
						<th>Product</th>
						<th>Quantity</th>
						<th>Cost</th>
					</tr>
				</thead>
				<tbody>
					<?php if (is_array($this->entity->products)) { foreach ($this->entity->products as $cur_product) { ?>
					<tr title="<?php echo $cur_product->key; ?>">
						<td><?php echo $cur_product->values[0]; ?></td>
						<td><?php echo $cur_product->values[1]; ?></td>
						<td><?php echo $cur_product->values[2]; ?></td>
					</tr>
					<?php } } ?>
				</tbody>
			</table>
			<input class="field" type="hidden" id="products" name="products" size="20" />
		</div>
	</div>
	<div id="product_dialog" title="Add a Product">
		<table id="available_products_table">
			<thead>
				<tr>
					<th>SKU</th>
					<th>Name</th>
					<th>Manufacturer</th>
					<th>Manufacturer SKU</th>
					<th>Unit Price</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->products as $cur_product) { ?>
				<tr title="<?php echo $cur_product->guid; ?>">
					<td><?php echo $cur_product->sku; ?></td>
					<td><?php echo $cur_product->name; ?></td>
					<td><?php echo $cur_product->Manufacturer; ?></td>
					<td><?php echo $cur_product->manufacturer_sku; ?></td>
					<td><?php echo $cur_product->unit_price; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<br class="spacer" />
		<div style="width: 100%">
			<label>
				<span>Quantity</span>
				<input type="text" name="cur_product_quantity" id="cur_product_quantity" />
			</label>
			<label>
				<span>Cost</span>
				<input type="text" name="cur_product_cost" id="cur_product_cost" />
			</label>
		</div>
	</div>
	<br class="spacer" />
	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listpos'); ?>';" value="Cancel" />
	</div>
</form>