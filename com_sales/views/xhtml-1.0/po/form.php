<?php
/**
 * Provides a form for the user to edit a PO.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Purchase Order' : (($this->entity->final) ? 'Viewing' : 'Editing').' PO ['.htmlentities($this->entity->po_number).']';
$this->note = 'Provide PO details in this form.';
$pines->com_pgrid->load();
$pines->com_jstree->load();
$read_only = '';
if ($this->entity->final)
	$read_only = 'readonly="readonly"';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_sales', 'po/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var products = $("#p_muid_products");
			var products_table = $("#p_muid_products_table");
			var available_products_table = $("#p_muid_available_products_table");
			var product_dialog = $("#p_muid_product_dialog");
			var cur_vendor = <?php echo ($this->entity->vendor ? $this->entity->vendor->guid : 'null'); ?>;
			// Number of decimal places to round to.
			var dec = <?php echo (int) $pines->config->com_sales->dec; ?>;
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

			pines.com_sales_select_vendor = function(vendor_id, loading) {
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
			};

			var update_products = function(){
				var all_rows = products_table.pgrid_get_all_rows().pgrid_export_rows();
				var total = 0.00;
				available_products_table.pgrid_get_selected_rows().pgrid_deselect_rows();
				$("#p_muid_cur_product_quantity").val("");
				$("#p_muid_cur_product_cost").val("");
				// Save the data into a hidden form element.
				products.val(JSON.stringify(all_rows));
				// Calculate a total based on quantity and cost.
				$.each(all_rows, function(){
					if (typeof this.values[2] != "undefined" && typeof this.values[3] != "undefined")
						total += parseInt(this.values[2]) * parseFloat(this.values[3]);
				});
				//
				total = round_to_dec(total);
				$("#p_muid_total").html(total);
			};

			<?php if (!$this->entity->final && empty($this->entity->received)) { ?>
			products_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add',
						extra_class: 'picon picon-document-new',
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
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							var row_data = products_table.pgrid_export_rows(rows);
							available_products_table.pgrid_select_rows([row_data[0].key]);
							$("#p_muid_cur_product_quantity").val(row_data[0].values[2]);
							$("#p_muid_cur_product_cost").val(row_data[0].values[3]);
							product_dialog.dialog('open');
							rows.pgrid_delete();
						}
					},
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_products();
						}
					}
				],
				pgrid_view_height: "300px"
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
				pgrid_view_height: "300px"
			});

			// Product Dialog
			product_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function(){
						var cur_product_quantity = parseInt($("#p_muid_cur_product_quantity").val());
						var cur_product_cost = parseFloat($("#p_muid_cur_product_cost").val());
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
				close: function(){
					update_products();
				}
			});

			// Location Tree
			var location = $("#p_muid_form [name=destination]");
			$("#p_muid_form .location_tree")
			.bind("select_node.jstree", function(e, data){
				location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
			})
			.bind("before.jstree", function (e, data){
				if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
					data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
			})
			.bind("loaded.jstree", function(e, data){
				var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
				if (!path.length) return;
				data.inst.open_node("#"+path.join(", #"), false, true);
			})
			.jstree({
				"plugins" : [ "themes", "json_data", "ui" ],
				"json_data" : {
					"ajax" : {
						"dataType" : "json",
						"url" : "<?php echo addslashes(pines_url('com_jstree', 'groupjson')); ?>"
					}
				},
				"ui" : {
					"select_limit" : 1,
					"initially_select" : ["p_muid_<?php echo (int) $this->entity->destination->guid; ?>"]
				}
			});

			pines.com_sales_select_vendor(cur_vendor, true);
		});
		// ]]>
	</script>
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
	<div class="pf-element">
		<span class="pf-label">Status</span>
		<span class="pf-field">
			<?php echo ($this->entity->final) ? 'Committed' : 'Not Committed'; ?>, <?php echo ($this->entity->finished) ? 'Received' : (empty($this->entity->received) ? 'Not Received' : 'Partially Received'); ?>
		</span>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">PO #</span>
			<input class="pf-field ui-widget-content" type="text" name="po_number" size="24" value="<?php echo htmlentities($this->entity->po_number); ?>" <?php echo $read_only; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Reference #</span>
			<input class="pf-field ui-widget-content" type="text" name="reference_number" size="24" value="<?php echo htmlentities($this->entity->reference_number); ?>" <?php echo $read_only; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Vendor</span>
			<?php if (!$this->entity->final && empty($this->entity->received)) { ?>
				<span class="pf-note">Changing this will clear selected products!</span>
			<?php } else { ?>
				<span class="pf-note">Vendor can't be changed after PO is committed or received.</span>
			<?php } ?>
			<select class="pf-field ui-widget-content" name="vendor" onchange="pines.com_sales_select_vendor(Number(this.value));"<?php echo (!$this->entity->final && empty($this->entity->received) ? '' : ' disabled="disabled"'); ?> <?php echo $read_only; ?>>
				<option value="null">-- None --</option>
				<?php
				$pines->entity_manager->sort($this->vendors, 'name');
				foreach ($this->vendors as $cur_vendor) { ?>
				<option value="<?php echo $cur_vendor->guid; ?>"<?php echo $this->entity->vendor->guid == $cur_vendor->guid ? ' selected="selected"' : ''; ?>><?php echo htmlentities($cur_vendor->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Location</span>
		<div class="pf-group">
			<div class="pf-field location_tree ui-widget-content ui-corner-all" style="height: 180px; width: 200px; overflow: auto;"></div>
		</div>
		<input type="hidden" name="destination" />
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Shipper</span>
			<select class="pf-field ui-widget-content" name="shipper" <?php echo $read_only; ?>>
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo $cur_shipper->guid; ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo htmlentities($cur_shipper->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element">
		<?php if (!$this->entity->final) { ?>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				$("#p_muid_eta").datepicker({
					dateFormat: "yy-mm-dd",
					showOtherMonths: true,
					selectOtherMonths: true
				});
			});
			// ]]>
		</script>
		<?php } ?>
		<label><span class="pf-label">ETA</span>
			<input class="pf-field ui-widget-content" type="text" id="p_muid_eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? format_date($this->entity->eta, 'date_sort') : ''); ?>" <?php echo $read_only; ?> /></label>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Products</span>
		<div class="pf-group">
			<div class="pf-field">
				<table id="p_muid_products_table">
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
						<?php
						$missing_products = array();
						foreach ($this->entity->products as $cur_product) {
							if (!isset($cur_product['entity']))
								continue;
							if (!isset($missing_products[$cur_product['entity']->guid]))
								$missing_products[$cur_product['entity']->guid] = array('entity' => $cur_product['entity'], 'quantity' => $cur_product['quantity']);
						?>
						<tr title="<?php echo $cur_product['entity']->guid; ?>">
							<td><?php echo htmlentities($cur_product['entity']->sku); ?></td>
							<td><?php echo htmlentities($cur_product['entity']->name); ?></td>
							<td><?php echo htmlentities($cur_product['quantity']); ?></td>
							<td><?php echo htmlentities($cur_product['cost']); ?></td>
							<td><?php echo $pines->com_sales->round((int) $cur_product['quantity'] * (float) $cur_product['cost']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<input type="hidden" id="p_muid_products" name="products" size="24" <?php echo $read_only; ?> />
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Total</span>
		<span class="pf-note">Due to rounding, this may not be exactly the sum of all line totals.</span>
		<span class="pf-field">$<span id="p_muid_total">--</span></span>
	</div>
	<div id="p_muid_product_dialog" title="Add a Product" style="display: none;">
		<table id="p_muid_available_products_table">
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
				<tr><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>
			</tbody>
		</table>
		<br class="pf-clearing" />
		<div style="width: 100%">
			<label>
				<span>Quantity</span>
				<input type="text" name="cur_product_quantity" id="p_muid_cur_product_quantity" <?php echo $read_only; ?> />
			</label>
			<label>
				<span>Cost</span>
				<input type="text" name="cur_product_cost" id="p_muid_cur_product_cost" <?php echo $read_only; ?> />
			</label>
		</div>
	</div>
	<?php if (!empty($this->entity->received)) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Received Inventory</span>
			<?php
			$received = array();
			foreach ($this->entity->received as $cur_entity) {
				if (!isset($received[$cur_entity->product->guid]))
					$received[$cur_entity->product->guid] = array('entity' => $cur_entity->product, 'serials' => array());
				if (isset($missing_products[$cur_entity->product->guid])) {
					$missing_products[$cur_entity->product->guid]['quantity']--;
					if (!$missing_products[$cur_entity->product->guid]['quantity'])
						unset($missing_products[$cur_entity->product->guid]);
				}
				$received[$cur_entity->product->guid]['serials'][] = isset($cur_entity->serial) ? $cur_entity->serial : '';
			}
			?>
			<?php foreach ($received as $cur_entry) { ?>
			<div class="pf-field pf-full-width ui-widget-content ui-corner-all" style="margin-bottom: 5px; padding: .5em;">
				SKU: <?php echo htmlentities($cur_entry['entity']->sku); ?><br />
				Product: <?php echo htmlentities($cur_entry['entity']->name); ?><br />
				Quantity: <?php echo count($cur_entry['serials']); ?>
				<?php if ($cur_entry['entity']->serialized) { ?>
				<br />
				Serials: <?php echo htmlentities(implode(', ', $cur_entry['serials'])); ?>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
		<?php if (!empty($missing_products)) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Missing Inventory</span>
			<?php foreach ($missing_products as $cur_entry) { ?>
			<div class="pf-field pf-full-width ui-widget-content ui-corner-all" style="margin-bottom: 5px; padding: .5em;">
				SKU: <?php echo htmlentities($cur_entry['entity']->sku); ?><br />
				Product: <?php echo htmlentities($cur_entry['entity']->name); ?><br />
				Quantity: <?php echo htmlentities($cur_entry['quantity']); ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	<?php } ?>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } if (!$this->entity->final) { ?>
		<input type="hidden" id="p_muid_save" name="save" value="" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#p_muid_save').val('save');" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Commit" onclick="$('#p_muid_save').val('commit');" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'po/list')); ?>');" value="Cancel" />
		<?php } ?>
	</div>
</form>