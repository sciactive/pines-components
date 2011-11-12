<?php
/**
 * Provides a form for the user to edit a transfer.
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
$this->title = (!isset($this->entity->guid)) ? 'Editing New Transfer' : (($this->entity->final) ? 'Viewing ' : 'Editing ').' Transfer ['.htmlspecialchars($this->entity->guid).']';
$this->note = 'Use this form to transfer inventory to another location.';
$pines->com_pgrid->load();
$pines->com_jstree->load();
if ($pines->config->com_sales->autocomplete_product)
	$pines->com_sales->load_product_select();
$read_only = '';
if ($this->entity->final)
	$read_only = 'readonly="readonly"';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'transfer/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		
		pines(function(){
			var products = $("#p_muid_products");
			var products_table = $("#p_muid_products_table");

			<?php if (!$this->entity->final && !$this->entity->shipped) { ?>
			products_table.pgrid({
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
						text: 'Quantity',
						extra_class: 'picon picon-document-multiple',
						confirm: false,
						double_click: true,
						multi_select: false,
						click: function(e, rows){
							var data = products_table.pgrid_get_selected_rows().data("product");
							if (!data) {
								alert("Please select a product.");
								return;
							}
							var qty = prompt("Please enter a quantity:", 1);
							while ((isNaN(parseInt(qty)) || parseInt(qty) != qty) && qty != null)
								qty = prompt("Please enter a quantity:", 1);
							if (qty != null) {
								for (i=0; i<qty-1; i++)
									add_product(data);
								products_table.pgrid_get_selected_rows().pgrid_deselect_rows();
							}
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
			var add_product = function(data){
				products_table.pgrid_add([{key: "", values: [pines.safe(data.guid), pines.safe(data.sku), pines.safe(data.name), (data.serialized) ? "pending" : ""]}], function(){
					var cur_row = $(this);
					cur_row.data("product", data);
				});
				update_products();
			};
			<?php } else { ?>
			products_table.pgrid({
				pgrid_paginate: false
			});
			<?php } ?>

			var update_products = function(){
				var all_rows = products_table.pgrid_get_all_rows().pgrid_export_rows();
				// Save the data into a hidden form element.
				products.val(JSON.stringify(all_rows));
			};

			<?php if (!empty($this->entity->received)) { ?>
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
				pgrid_view_height: "250px"
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
				pgrid_view_height: "250px"
			});
			<?php } ?>

			// Location Tree
			var origin = $("#p_muid_form [name=origin]");
			var destination = $("#p_muid_form [name=destination]");
			$("#p_muid_form .location_tree_origin")
			.bind("select_node.jstree", function(e, data){
				origin.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
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
					"initially_select" : ["<?php echo (int) $this->entity->origin->guid; ?>"]
				}
			});
			$("#p_muid_form .location_tree_destination")
			.bind("select_node.jstree", function(e, data){
				destination.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
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

			update_products();
		});
		// ]]>
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
			<?php echo ($this->entity->final) ? 'Committed' : 'Not Committed'; ?>, <?php echo ($this->entity->shipped) ? 'Shipped' : 'Not Shipped'; ?>, <?php echo ($this->entity->finished) ? 'Received' : (empty($this->entity->received) ? 'Not Received' : 'Partially Received'); ?>
		</span>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Reference #</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="reference_number" size="24" value="<?php echo htmlspecialchars($this->entity->reference_number); ?>" <?php echo $read_only; ?> /></label>
	</div>
	<div class="pf-element" style="width: 49%;">
		<span class="pf-label">Origin</span>
		<div class="pf-group">
			<div class="pf-field location_tree_origin ui-widget-content ui-corner-all" style="height: 180px; width: 200px; overflow: auto;"></div>
		</div>
		<input type="hidden" name="origin" />
	</div>
	<div class="pf-element" style="width: 49%; clear: none;">
		<span class="pf-label">Destination</span>
		<div class="pf-group">
			<div class="pf-field location_tree_destination ui-widget-content ui-corner-all" style="height: 180px; width: 200px; overflow: auto;"></div>
		</div>
		<input type="hidden" name="destination" />
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Shipper</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="shipper" <?php echo $read_only; ?>>
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo (int) $cur_shipper->guid; ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_shipper->name); ?></option>
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
			<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? date('Y-m-d', $this->entity->eta) : ''); ?>" <?php echo $read_only; ?> /></label>
	</div>
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
		<div class="pf-group">
			<div class="pf-field">
				<table id="p_muid_products_table">
					<thead>
						<tr>
							<th>GUID</th>
							<th>SKU</th>
							<th>Product</th>
							<th>Serial</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$this->entity->shipped) {
							foreach ($this->entity->products as $cur_product) {
								if (!isset($cur_product->guid))
									continue;
								?>
						<tr>
							<td><?php echo htmlspecialchars($cur_product->guid); ?></td>
							<td><?php echo htmlspecialchars($cur_product->sku); ?></td>
							<td><?php echo htmlspecialchars($cur_product->name); ?></td>
							<td><?php echo $cur_product->serialized ? 'pending' : ''; ?></td>
						</tr>
						<?php }
						} else {
							foreach ($this->entity->stock as $cur_stock) {
								if (!isset($cur_stock->guid))
									continue;
								if (isset($missing_products[$cur_stock->product->guid])) {
									$missing_products[$cur_stock->product->guid]['quantity']++;
								} else {
									$missing_products[$cur_stock->product->guid] = array('entity' => $cur_stock->product, 'quantity' => 1);
								}
								?>
						<tr>
							<td><?php echo htmlspecialchars($cur_stock->product->guid); ?></td>
							<td><?php echo htmlspecialchars($cur_stock->product->sku); ?></td>
							<td><?php echo htmlspecialchars($cur_stock->product->name); ?></td>
							<td><?php echo htmlspecialchars($cur_stock->serial); ?></td>
						</tr>
						<?php }
						} ?>
					</tbody>
				</table>
			</div>
			<input type="hidden" id="p_muid_products" name="products" />
		</div>
	</div>
	<?php if (!empty($this->entity->received)) { ?>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Received</span>
			<div class="pf-group">
				<div class="pf-field">
					<table id="p_muid_received_table">
						<thead>
							<tr>
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
								<td><?php echo htmlspecialchars($cur_entity->product->sku); ?></td>
								<td><?php echo htmlspecialchars($cur_entity->product->name); ?></td>
								<td><?php echo htmlspecialchars($cur_entity->serial); ?></td>
								<td><?php echo htmlspecialchars("{$cur_entity->user->name} [{$cur_entity->user->username}]"); ?></td>
								<td><?php echo htmlspecialchars("{$cur_entity->group->name} [{$cur_entity->group->groupname}]"); ?></td>
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
								<td><?php echo htmlspecialchars($cur_entry['entity']->name); ?></td>
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
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="comments"><?php echo htmlspecialchars($this->entity->comments); ?></textarea></div>
	</div>
	<br class="pf-clearing" />
	<div class="pf-element pf-buttons">
		<input type="hidden" id="p_muid_save" name="save" value="" />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } if (!$this->entity->final) { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#p_muid_save').val('save');" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Commit" onclick="$('#p_muid_save').val('commit');" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'transfer/list')); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#p_muid_save').val('save');" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'transfer/list')); ?>');" value="Cancel" />
		<?php } ?>
	</div>
</form>