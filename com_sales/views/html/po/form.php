<?php
/**
 * Provides a form for the user to edit a PO.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Purchase Order' : 'Editing PO ['.htmlspecialchars($this->entity->po_number).']';
$this->note = 'Provide PO details in this form.';
if ($this->entity->final)
	$this->note .= ' Most options cannot be edited after the PO has been committed.';
$pines->com_pgrid->load();
if (!$this->entity->final)
	$pines->com_jstree->load();
$read_only = '';
if ($this->entity->final)
	$read_only = 'readonly="readonly"';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'po/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			var products = $("#p_muid_products"),
				products_table = $("#p_muid_products_table"),
				available_products_table = $("#p_muid_available_products_table"),
				product_dialog = $("#p_muid_product_dialog"),
				cur_vendor = <?php echo ($this->entity->vendor ? (int) $this->entity->vendor->guid : 'null'); ?>,
				dec = <?php echo (int) $pines->config->com_sales->dec; ?>; // Number of decimal places to round to.
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
				available_products_table.pgrid_get_all_rows().pgrid_delete();
				if (!loading)
					products_table.pgrid_get_all_rows().pgrid_delete();
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
				pgrid_toolbar: true,
				pgrid_toolbar_contents: [
					{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
					{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
					{type: 'separator'},
					{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
						pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
							filename: 'PO <?php echo htmlspecialchars($this->entity->po_number); ?>',
							content: rows
						});
					}}
				],
				pgrid_paginate: false
			});
			$("#p_muid_received_table").pgrid({
				pgrid_toolbar: true,
				pgrid_toolbar_contents: [
					{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
					{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
					{type: 'separator'},
					{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
						pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
							filename: 'PO <?php echo htmlspecialchars($this->entity->po_number); ?> - Received',
							content: rows
						});
					}}
				],
				pgrid_paginate: false,
				pgrid_view_height: "150px"
			});
			$("#p_muid_missing_table").pgrid({
				pgrid_toolbar: true,
				pgrid_toolbar_contents: [
					{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
					{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
					{type: 'separator'},
					{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
						pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
							filename: 'PO <?php echo htmlspecialchars($this->entity->po_number); ?> - Not Received',
							content: rows
						});
					}}
				],
				pgrid_paginate: false,
				pgrid_view_height: "150px"
			});
			<?php } ?>
			// Product search function for the pgrid toolbar.
			var product_search_box;
			var submit_search = function(){
				var search_string = product_search_box.val();
				if (search_string == "") {
					alert("Please enter a search string.");
					return;
				}
				var loader;
				$.ajax({
					url: <?php echo json_encode(pines_url('com_sales', 'product/search')); ?>,
					type: "POST",
					dataType: "json",
					data: {"q": search_string, "enabled": "true"},
					beforeSend: function(){
						loader = $.pnotify({
							title: 'Search',
							text: 'Searching the database...',
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
						pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (!data) {
							available_products_table.pgrid_get_all_rows().pgrid_delete();
							alert("No products were found that matched the query.");
							return;
						}
						var struct = [];
						$.each(data, function(){
							if (this.stock_type == "non_stocked")
								return;
							if (typeof this.manufacturer_name != "undefined" && this.manufacturer_name !== null)
								this.manufacturer = '<a data-entity="'+pines.safe(this.manufacturer_guid)+'" data-entity-context="com_sales_manufacturer">'+pines.safe(this.manufacturer_name)+'</a>';
							else
								this.manufacturer = '';
							var sku = "",
								cost = "",
								link = "",
								pass = false;
							$.each(this.vendors, function(i, vendor){
								if (pass || vendor.guid != cur_vendor)
									return;
								sku = vendor.sku;
								cost = vendor.cost;
								if (typeof vendor.link != "undefined" && vendor.link !== null && vendor.link !== "")
									link = '<a href="'+pines.safe(vendor.link)+'" target="_blank">'+pines.safe(vendor.link)+'</a>';
								pass = true;
							});
							if (!pass)
								return;
							struct.push({
								"key": this.guid,
								"values": [
									pines.safe(this.sku),
									'<a data-entity="'+pines.safe(this.guid)+'" data-entity-context="com_sales_product">'+pines.safe(this.name)+'</a>',
									this.manufacturer,
									pines.safe(this.manufacturer_sku),
									pines.safe(sku),
									pines.safe(cost),
									pines.safe('$'+this.price),
									link
								]
							});
						});
						available_products_table.pgrid_get_all_rows().pgrid_delete();
						available_products_table.pgrid_add(struct);
					}
				});
			}
			// Needs to be gridified before it's hidden.
			available_products_table.pgrid({
				pgrid_toolbar: true,
				pgrid_toolbar_contents: [
					{type: 'text', load: function(textbox){
						// Display the current product being searched for.
						textbox.keydown(function(e){
							if (e.keyCode == 13)
								submit_search();
						});
						product_search_box = textbox;
					}},
					{type: 'button', extra_class: 'picon picon-system-search', selection_optional: true, pass_csv_with_headers: true, click: submit_search}
				],
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
								pines.safe(cur_product[0].values[0]),
								pines.safe(cur_product[0].values[1]),
								pines.safe(cur_product_quantity),
								pines.safe(cur_product_cost),
								pines.safe(round_to_dec(cur_product_quantity * cur_product_cost))
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

			<?php if (!$this->entity->final) { ?>
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
						"url" : <?php echo json_encode(pines_url('com_jstree', 'groupjson')); ?>
					}
				},
				"ui" : {
					"select_limit" : 1,
					"initially_select" : ["<?php echo (int) $this->entity->destination->guid; ?>"]
				}
			});
			<?php } ?>

			pines.com_sales_select_vendor(cur_vendor, true);
		});
	</script>
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
	<div class="pf-element">
		<span class="pf-label">Status</span>
		<span class="pf-field">
			<?php echo ($this->entity->final) ? 'Committed' : 'Not Committed'; ?>, <?php echo ($this->entity->finished) ? 'Received' : (empty($this->entity->received) ? 'Not Received' : 'Partially Received'); ?>
		</span>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">PO #</span>
			<span class="pf-note">If left blank, one will be auto-generated.</span>
			<input class="pf-field" type="text" name="po_number" size="24" value="<?php echo htmlspecialchars($this->entity->po_number); ?>" <?php echo $read_only; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Reference #</span>
			<input class="pf-field" type="text" name="reference_number" size="24" value="<?php echo htmlspecialchars($this->entity->reference_number); ?>" /></label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Vendor</span>
			<?php if (!$this->entity->final && empty($this->entity->received)) { ?>
			<span class="pf-note">Changing this will clear selected products!</span>
			<select class="pf-field" name="vendor" onchange="pines.com_sales_select_vendor(Number(this.value));">
				<option value="null">-- None --</option>
				<?php
				$pines->entity_manager->sort($this->vendors, 'name');
				foreach ($this->vendors as $cur_vendor) { ?>
				<option value="<?php echo (int) $cur_vendor->guid; ?>"<?php echo $this->entity->vendor->guid == $cur_vendor->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_vendor->name); ?></option>
				<?php } ?>
			</select>
			<?php } else { ?>
			<span class="pf-note">Vendor can't be changed after PO is committed or received.</span>
			<span class="pf-field">
				<a data-entity="<?php echo htmlspecialchars($this->entity->vendor->guid); ?>" data-entity-context="com_sales_vendor"><?php echo htmlspecialchars($this->entity->vendor->name); ?></a>
			</span>
			<?php } ?>
		</label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Destination</span>
		<?php if (!$this->entity->final && empty($this->entity->received)) { ?>
		<div class="pf-group">
			<div class="pf-field location_tree ui-widget-content ui-corner-all" style="height: 180px; width: 200px; overflow: auto;"></div>
		</div>
		<?php } else { ?>
		<span class="pf-note">Destination can't be changed after PO is committed or received.</span>
		<span class="pf-field">
			<a data-entity="<?php echo htmlspecialchars($this->entity->destination->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($this->entity->destination->guid ? "{$this->entity->destination->name} [{$this->entity->destination->groupname}]" : ''); ?></a>
		</span>
		<?php } ?>
		<input type="hidden" name="destination" />
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Shipper</span>
			<?php if (!$this->entity->final) { ?>
			<select class="pf-field" name="shipper">
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo (int) $cur_shipper->guid; ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_shipper->name); ?></option>
				<?php } ?>
			</select>
			<?php } else { ?>
			<span class="pf-note">Shipper can't be changed after PO is committed.</span>
			<span class="pf-field">
				<a data-entity="<?php echo htmlspecialchars($this->entity->shipper->guid); ?>" data-entity-context="com_sales_shipper"><?php echo htmlspecialchars($this->entity->shipper->name); ?></a>
			</span>
			<?php } ?>
		</label>
	</div>
	<div class="pf-element">
		<script type="text/javascript">
			pines(function(){
				$("#p_muid_eta").datepicker({
					dateFormat: "yy-mm-dd",
					showOtherMonths: true,
					selectOtherMonths: true
				});
			});
		</script>
		<label><span class="pf-label">ETA</span>
			<input class="pf-field" type="text" id="p_muid_eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? htmlspecialchars(format_date($this->entity->eta, 'date_sort')) : ''); ?>" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Tracking Number(s)</span>
			<span class="pf-note">One per line.</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<textarea style="width: 100%;" rows="3" cols="35" name="tracking_numbers"><?php echo isset($this->entity->tracking_numbers) ? htmlspecialchars(implode("\n", $this->entity->tracking_numbers)) : ''; ?></textarea>
				</span>
			</span></label>
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
							<?php if ($this->entity->final) { ?>
							<th>Received</th>
							<?php } ?>
							<th>Unit Cost</th>
							<th>Line Total</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$missing_products = array();
						$all_received = (array) $this->entity->received;
						foreach ($this->entity->products as $cur_product) {
							if (!isset($cur_product['entity']))
								continue;
							if (!isset($missing_products[$cur_product['entity']->guid]))
								$missing_products[$cur_product['entity']->guid] = array('entity' => $cur_product['entity'], 'quantity' => $cur_product['quantity']);
							?>
						<tr title="<?php echo (int) $cur_product['entity']->guid; ?>">
							<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
							<td><?php echo htmlspecialchars($cur_product['entity']->name); ?></td>
							<td><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
							<?php if ($this->entity->final) { ?>
							<td>
								<?php
								$rec_qty = 0;
								foreach ($all_received as $key => $cur_received) {
									if ($rec_qty >= $cur_product['quantity'])
										break;
									if ($cur_product['entity']->is($cur_received->product)) {
										$rec_qty++;
										unset($all_received[$key]);
									}
								}
								echo (int) $rec_qty;
								?>
							</td>
							<?php } ?>
							<td><?php echo htmlspecialchars($cur_product['cost']); ?></td>
							<td><?php echo $pines->com_sales->round((int) $cur_product['quantity'] * (float) $cur_product['cost']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<input type="hidden" id="p_muid_products" name="products" <?php echo $read_only; ?> />
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
					<th>Vendor Cost</th>
					<th>Unit Price</th>
					<th>Link</th>
				</tr>
			</thead>
			<tbody>
				<tr><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td></tr>
			</tbody>
		</table>
		<br class="pf-clearing" />
		<div class="pf-form">
			<div class="pf-element">
				<label><span class="pf-label">Quantity</span>
					<input class="pf-field" type="text" name="cur_product_quantity" size="24" id="p_muid_cur_product_quantity" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Cost</span>
					<input class="pf-field" type="text" name="cur_product_cost" size="24" id="p_muid_cur_product_cost" /></label>
			</div>
		</div>
		<br />
	</div>
	<?php if (!empty($this->entity->received)) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Received</span>
			<div class="pf-group">
				<div class="pf-field">
					<table id="p_muid_received_table">
						<thead>
							<tr>
								<th>GUID</th>
								<th>SKU</th>
								<th>Product</th>
								<th>Serial</th>
								<th>User</th>
								<th>Location</th>
								<th>Time</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($this->entity->received as $cur_entity) {
								if (isset($missing_products[$cur_entity->product->guid])) {
									$missing_products[$cur_entity->product->guid]['quantity']--;
									if (!$missing_products[$cur_entity->product->guid]['quantity'])
										unset($missing_products[$cur_entity->product->guid]);
								}
							?>
							<tr>
								<td><a data-entity="<?php echo htmlspecialchars($cur_entity->guid); ?>" data-entity-context="com_sales_stock"><?php echo htmlspecialchars($cur_entity->guid); ?></a></td>
								<td><?php echo htmlspecialchars($cur_entity->product->sku); ?></td>
								<td><a data-entity="<?php echo htmlspecialchars($cur_entity->product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_entity->product->name); ?></a></td>
								<td><?php echo htmlspecialchars($cur_entity->serial); ?></td>
								<td><a data-entity="<?php echo htmlspecialchars($cur_entity->user->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars("{$cur_entity->user->name} [{$cur_entity->user->username}]"); ?></a></td>
								<td><a data-entity="<?php echo htmlspecialchars($cur_entity->group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars("{$cur_entity->group->name} [{$cur_entity->group->groupname}]"); ?></a></td>
								<td><?php echo htmlspecialchars(format_date($cur_entity->p_cdate, 'full_sort')); ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php if (!empty($missing_products)) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Not Received</span>
			<div class="pf-group">
				<div class="pf-field">
					<table id="p_muid_missing_table">
						<thead>
							<tr>
								<th>SKU</th>
								<th>Product</th>
								<th>Quantity</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($missing_products as $cur_entry) { ?>
							<tr>
								<td><?php echo htmlspecialchars($cur_entry['entity']->sku); ?></td>
								<td><a data-entity="<?php echo htmlspecialchars($cur_entry['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_entry['entity']->name); ?></a></td>
								<td><?php echo htmlspecialchars($cur_entry['quantity']); ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php } ?>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Comments</h3>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-group pf-full-width" style="margin-left: 0;"><textarea style="width: 100%;" rows="3" cols="35" name="comments"><?php echo htmlspecialchars($this->entity->comments); ?></textarea></div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input type="hidden" id="p_muid_save" name="save" value="" />
		<input class="pf-button btn btn-primary" type="submit" name="submit" value="Save" onclick="$('#p_muid_save').val('save');" />
		<?php if (!$this->entity->final) { ?>
		<input class="pf-button btn btn-primary" type="submit" name="submit" value="Commit" onclick="$('#p_muid_save').val('commit');" />
		<?php } ?>
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'po/list'))); ?>);" value="Cancel" />
	</div>
</form>