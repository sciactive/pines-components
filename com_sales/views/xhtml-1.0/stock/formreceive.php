<?php
/**
 * Provides a form for the user to receive inventory.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Receive Inventory';
if (!gatekeeper('com_sales/receivelocation'))
	$this->note = 'Only use this form to receive inventory into your <strong>current</strong> location ('.(!isset($_SESSION['user']->group) ? 'No Location' : $_SESSION['user']->group->name).').';
$pines->com_pgrid->load();
$pines->com_jstree->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_sales', 'stock/receive')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		
		pines(function(){
			var products = $("#p_muid_products");
			var products_table = $("#p_muid_products_table");

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
										url: "<?php echo pines_url('com_sales', 'product/search'); ?>",
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
								}
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
							var serial = rows.pgrid_get_value(2);
							do {
								serial = prompt("This item is serialized. Please provide the serial:", serial);
							} while (!serial && serial != null);
							if (serial != null) {
								rows.pgrid_set_value(2, serial);
								update_products();
							}
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
							var qty = rows.pgrid_get_value(3);
							do {
								qty = prompt("Please enter a quantity:", qty);
							} while ((parseInt(qty) < 1 || isNaN(parseInt(qty))) && qty != null);
							if (qty != null) {
								rows.pgrid_set_value(3, parseInt(qty));
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
				],
				pgrid_view_height: "300px"
			});
			var add_product = function(data){
				var serial = "";
				if (data.serialized) {
					while (!serial) {
						serial = prompt("This item is serialized. Please provide the serial:");
						if (serial == null)
							return;
					}
				} else {
					var match = false;
					products_table.pgrid_get_all_rows().each(function(){
						if (match)
							return;
						var cur_row = $(this);
						if (cur_row.pgrid_get_value(1) == data.sku) {
							cur_row.pgrid_set_value(3, parseInt(cur_row.pgrid_get_value(3)) + 1);
							update_products();
							match = true;
						}
					});
					if (match)
						return;
				}
				products_table.pgrid_add([{values: [data.sku, serial, 1]}], function(){
					var cur_row = $(this);
					cur_row.data("product", data);
				});
				update_products();
			};
			// Category Grid
			var category_grid = $("#p_muid_category_grid").pgrid({
				pgrid_hidden_cols: [1],
				pgrid_sort_col: 1,
				pgrid_sort_ord: "asc",
				pgrid_paginate: false,
				pgrid_view_height: "300px",
				pgrid_multi_select: false,
				pgrid_double_click: function(e, row){
					category_products_grid.pgrid_get_all_rows().pgrid_delete();
					var loader;
					$.ajax({
						url: "<?php echo pines_url('com_sales', 'category/products'); ?>",
						type: "POST",
						dataType: "json",
						data: {"id": $(row).attr("title")},
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
			});
			// Category Dialog
			var category_dialog = $("#p_muid_category_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				open: function() {
					category_grid.pgrid_get_selected_rows().pgrid_deselect_rows();
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

			var update_products = function(){
				var all_rows = products_table.pgrid_get_all_rows().pgrid_export_rows();
				products.val(JSON.stringify(all_rows));
			};

			products_table.pgrid_get_all_rows().pgrid_delete();
			update_products();
		});
		// ]]>
	</script>
	<?php if (gatekeeper('com_sales/receivelocation')) { ?>
	<script type='text/javascript'>
		// <![CDATA[
		pines(function(){
			// Location Tree
			var location = $("#p_muid_form [name=location]");
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
						"url" : "<?php echo pines_url('com_jstree', 'groupjson'); ?>"
					}
				},
				"ui" : {
					"select_limit" : 1,
					"initially_select" : ["p_muid_<?php echo (!isset($_SESSION['user']->group) ? '' : $_SESSION['user']->group->guid); ?>"]
				}
			});
		});
		// ]]>
	</script>
	<div class="pf-element">
		<span class="pf-label">Location</span>
		<div class="pf-group">
			<div class="pf-feild location_tree"></div>
		</div>
		<input type="hidden" name="location" value="<?php echo (!isset($_SESSION['user']->group) ? '' : $_SESSION['user']->group->guid); ?>" />
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
					<td><?php echo $category->name; ?></td>
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
							<th>Product Code</th>
							<th>Serial</th>
							<th>Quantity</th>
						</tr>
					</thead>
					<tbody>
						<tr><td>-</td><td>-</td><td>-</td></tr>
					</tbody>
				</table>
			</div>
			<input type="hidden" id="p_muid_products" name="products" size="24" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url()); ?>');" value="Cancel" />
	</div>
</form>