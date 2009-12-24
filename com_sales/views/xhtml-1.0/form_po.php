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
$this->title = (is_null($this->entity->guid)) ? 'Editing New Purchase Order' : 'Editing ['.htmlentities($this->entity->po_number).']';
$this->note = 'Provide PO details in this form.';
?>
<form class="pform" method="post" id="po_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
	<script type="text/javascript">
		// <![CDATA[
		var products;
		var products_table;
		var available_products_table;
		var product_dialog;
		var cur_vendor = <?php echo ($this->entity->vendor ? $this->entity->vendor->guid : 'null'); ?>;
		// Number of decimal places to round to.
		var dec = <?php echo intval($config->com_sales->dec); ?>;
		var all_products = JSON.parse("<?php
		$products = array();
		foreach ($this->products as $cur_product) {
			$cur_vendor_guids = array();
			foreach($cur_product->vendors as $cur_vendor) {
				$cur_vendor_guids[] = (object) array(
					'guid' => $cur_vendor['entity']->guid,
					'sku' => $cur_vendor['sku']
				);
			}
			$export_product = (object) array(
				'guid' => $cur_product->guid,
				'sku' => $cur_product->sku,
				'name' => $cur_product->name,
				'manufacturer' => $cur_product->manufacturer->name,
				'manufacturer_sku' => $cur_product->manufacturer_sku,
				'unit_price' => $cur_product->unit_price,
				'vendors' => $cur_vendor_guids
			);
			array_push($products, $export_product);
		}
		echo addslashes(json_encode($products));
		?>");

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


		function select_vendor(vendor_id, loading) {
			if (cur_vendor == vendor_id && !loading) return;
			var select_products = [];
			available_products_table.pgrid_get_all_rows().pgrid_delete();
			if (!loading)
				products_table.pgrid_get_all_rows().pgrid_delete();
			$.each(all_products, function(){
				var cur_product = this;
				$.each(cur_product.vendors, function(){
					if (vendor_id == this.guid) {
						$.merge(select_products, [{
								"key": cur_product.guid,
								values: [
									cur_product.sku,
									cur_product.name,
									cur_product.manufacturer,
									cur_product.manufacturer_sku,
									this.sku,
									cur_product.unit_price
								]
							}]);
					}
				});
			});
			available_products_table.pgrid_add(select_products);
			cur_vendor = vendor_id;
			update_products();
		}

		function update_products() {
			var all_rows = products_table.pgrid_get_all_rows().pgrid_export_rows();
			var total = 0.00;
			available_products_table.pgrid_get_selected_rows().pgrid_deselect_rows();
			$("#cur_product_quantity").val("");
			$("#cur_product_cost").val("");
			// Save the data into a hidden form element.
			products.val(JSON.stringify(all_rows));
			// Calculate a total based on quantity and cost.
			$.each(all_rows, function(){
				if (typeof this.values[2] != "undefined" && typeof this.values[3] != "undefined")
					total += parseInt(this.values[2]) * parseFloat(this.values[3]);
			});
			//
			total = round_to_dec(total);
			$("#total").html(total);
		}
		
		$(document).ready(function(){
			products = $("#products");
			products_table = $("#products_table");
			available_products_table = $("#available_products_table");
			product_dialog = $("#product_dialog");

			<?php if (empty($this->entity->received)) { ?>
			products_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add',
						extra_class: 'icon picon_16x16_actions_document-new',
						selection_optional: true,
						click: function(){
							if (!cur_vendor) {
								alert("Please select a vendor.");
								return;
							}
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
							available_products_table.pgrid_select_rows([row_data[0].key]);
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
			<?php } else { ?>
			products_table.pgrid({
				pgrid_paginate: false
			});
			<?php } ?>
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
						var cur_product_quantity = parseInt($("#cur_product_quantity").val());
						var cur_product_cost = parseFloat($("#cur_product_cost").val());
						var cur_product = available_products_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!cur_product[0]) {
							alert("Please select a product.");
							return;
						}
						if (isNaN(cur_product_quantity) || isNaN(cur_product_cost)) {
							alert("Please provide both a quantity and a cost for this product.");
							return;
						}
						var new_product = [{
							key: cur_product[0].key,
							values: [
								cur_product[0].values[0],
								cur_product[0].values[1],
								cur_product_quantity,
								cur_product_cost,
								round_to_dec(cur_product_quantity * cur_product_cost)
							]
						}];
						products_table.pgrid_add(new_product);
						$(this).dialog('close');
					}
				},
				close: function(event, ui) {
					update_products();
				}
			});

			select_vendor(cur_vendor, true);
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
		<label><span class="label">PO #</span>
			<input class="field" type="text" name="po_number" size="24" value="<?php echo $this->entity->po_number; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Reference #</span>
			<input class="field" type="text" name="reference_number" size="24" value="<?php echo $this->entity->reference_number; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Vendor</span>
			<?php if (empty($this->entity->received)) { ?>
				<span class="note">Changing this will clear selected products!</span>
			<?php } else { ?>
				<span class="note">Vendor cannot be changed after items have been received on this PO.</span>
			<?php } ?>
			<select class="field" name="vendor" onchange="void select_vendor(Number(this.value));"<?php echo (empty($this->entity->received) ? '' : ' disabled="disabled"'); ?>>
				<option value="null">-- None --</option>
				<?php foreach ($this->vendors as $cur_vendor) { ?>
				<option value="<?php echo $cur_vendor->guid; ?>"<?php echo $this->entity->vendor->guid == $cur_vendor->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_vendor->name; ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Destination</span>
			<?php if (!empty($this->entity->received)) { ?>
				<span class="note">Destination cannot be changed after items have been received on this transfer.</span>
			<?php } ?>
			<select class="field" name="destination"<?php echo (empty($this->entity->received) ? '' : ' disabled="disabled"'); ?>>
				<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations, $this->entity->destination->guid); ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Shipper</span>
			<select class="field" name="shipper">
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo $cur_shipper->guid; ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_shipper->name; ?></option>
				<?php } ?>
			</select></label>
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
			<input class="field" type="text" id="eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? date('Y-m-d', $this->entity->eta) : ''); ?>" /></label>
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
							<th>Quantity</th>
							<th>Unit Cost</th>
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
							<td><?php echo $cur_product['quantity']; ?></td>
							<td><?php echo $cur_product['cost']; ?></td>
							<td><?php echo $config->run_sales->round(intval($cur_product['quantity']) * floatval($cur_product['cost']), $config->com_sales->dec); ?></td>
						</tr>
						<?php } } ?>
					</tbody>
				</table>
			</div>
			<input class="field" type="hidden" id="products" name="products" size="24" />
		</div>
	</div>
	<div class="element full_width">
		<span class="label">Total</span>
		<span class="note">Due to rounding, this may not be exactly the sum of all line totals.</span>
		<span class="field">$<span id="total">--</span></span>
	</div>
	<div id="product_dialog" title="Add a Product">
		<table id="available_products_table">
			<thead>
				<tr>
					<th>SKU</th>
					<th>Name</th>
					<th>Manufacturer</th>
					<th>Manufacturer SKU</th>
					<th>Vendor SKU</th>
					<th>Unit Price</th>
				</tr>
			</thead>
			<tbody>
				<tr><td>-----------</td><td>-----------</td><td>-----------</td><td>-----------</td><td>-----------</td><td>-----------</td></tr>
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
	<?php if (!empty($this->entity->received)) { ?>
	<div class="element">
		<span class="label">Received Inventory</span>
		<div class="group">
			<?php foreach ($this->entity->received as $cur_entity) { ?>
			<div class="field" style="margin-bottom: 5px;">
				Product: <?php echo $cur_entity->product->name; ?><br />
				Serial: <?php echo $cur_entity->serial; ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<br class="spacer" />
	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listpos'); ?>';" value="Cancel" />
	</div>
</form>