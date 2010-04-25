<?php
/**
 * Provides a form for the user to edit a product.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Product' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide product details in this form.';
?>
<form class="pf-form" method="post" id="product_details" action="<?php echo htmlentities(pines_url('com_sales', 'saveproduct')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			var vendors = $("#vendors");
			var vendors_table = $("#vendors_table");
			var available_vendors_table = $("#available_vendors_table");
			var vendor_dialog = $("#vendor_dialog");

			vendors_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Vendor',
						extra_class: 'icon picon_16x16_actions_list-add',
						selection_optional: true,
						click: function(){
							vendor_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Vendor',
						extra_class: 'icon picon_16x16_actions_list-remove',
						click: function(e, rows){
							rows.pgrid_delete();
							update_vendors();
						}
					}
				]
			});

			// Needs to be gridified before it's hidden.
			available_vendors_table.pgrid({
				pgrid_multi_select: false,
				pgrid_paginate: false,
				pgrid_height: '400px;'
			});

			// Vendor Dialog
			vendor_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function() {
						var cur_vendor_sku = $("#cur_vendor_sku").val();
						var cur_vendor_cost = $("#cur_vendor_cost").val();
						var cur_vendor = available_vendors_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!cur_vendor[0]) {
							alert("Please select a vendor.");
							return;
						}
						if (cur_vendor_sku == "" || cur_vendor_cost == "") {
							alert("Please provide both a SKU and a cost for this vendor.");
							return;
						}
						var new_vendor = [{
							key: cur_vendor[0].key,
							values: [
								cur_vendor[0].values[0],
								cur_vendor_sku,
								cur_vendor_cost
							]
						}];
						vendors_table.pgrid_add(new_vendor);
						update_vendors();
						$(this).dialog('close');
					}
				}
			});

			function update_vendors() {
				available_vendors_table.pgrid_get_selected_rows().pgrid_deselect_rows();
				$("#cur_vendor_sku").val("");
				$("#cur_vendor_cost").val("");
				vendors.val(JSON.stringify(vendors_table.pgrid_get_all_rows().pgrid_export_rows()));
			}

			$("#product_tabs").tabs();
			update_vendors();
		});
		// ]]>
	</script>
	<div id="product_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_images">Images</a></li>
			<li><a href="#tab_purchasing">Purchasing</a></li>
			<li><a href="#tab_pricing">Pricing</a></li>
			<li><a href="#tab_attributes">Attributes</a></li>
			<li><a href="#tab_accounting">Accounting</a></li>
		</ul>
		<div id="tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo "{$this->entity->user->name} [{$this->entity->user->username}]"; ?></span></div>
				<div>Group: <span class="date"><?php echo "{$this->entity->group->name} [{$this->entity->group->groupname}]"; ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Product SKU</span>
					<input class="pf-field ui-widget-content" type="text" name="sku" size="24" value="<?php echo $this->entity->sku; ?>" /></label>
			</div>
			<div class="pf-element">
				<span class="pf-label">Categories</span>
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						// Category Tree
						var input = $("#categories");
						$("#category_tree").tree({
							rules : {
								multiple : true
							},
							data : {
								type : "json",
								opts : {
									method : "get",
									url : "<?php echo pines_url('com_sales', 'catjson'); ?>"
								}
							},
							selected : <?php echo json_encode(array_map('strval', $pines->com_sales->get_product_category_guid_array($this->entity))); ?>,
							callback : {
								oncreate : function(NODE, REF_NODE, TYPE, TREE_OBJ, RB) {
									var parent;
									var parent_id;
									if (TYPE == 'after' || TYPE == 'before') {
										parent = TREE_OBJ.parent(REF_NODE);
										if (parent === -1) {
											parent_id = "null";
										} else {
											parent_id = parent.attr("id");
										}
									} else {
										parent_id = REF_NODE.id;
									}
									$.ajax({
										type: "POST",
										url: "<?php echo pines_url('com_sales', 'catjson'); ?>",
										dataType: "json",
										data: {
											"do": "new",
											"parent": parent_id
										},
										success: function(data, textStatus) {
											if (!data.status) {
												$.tree.rollback(RB);
												alert("A problem occurred while trying to create the category.");
											} else {
												NODE.id = data.id;
											}
										},
										error: function(XMLHttpRequest, textStatus, errorThrown) {
											$.tree.rollback(RB);
											alert("An error occurred trying to reach the server:\n"+XMLHttpRequest.status+": "+textStatus);
										}
									});
								},
								onrename : function(NODE, TREE_OBJ, RB) {
									$.ajax({
										type: "POST",
										url: "<?php echo pines_url('com_sales', 'catjson'); ?>",
										dataType: "json",
										data: {
											"do": "rename",
											"id": NODE.id,
											"name": TREE_OBJ.get_text(NODE)
										},
										success: function(data, textStatus) {
											if (!data.status) {
												$.tree.rollback(RB);
												alert("A problem occurred while trying to rename the category.");
											}
										},
										error: function(XMLHttpRequest, textStatus, errorThrown) {
											$.tree.rollback(RB);
											alert("An error occurred trying to reach the server:\n"+XMLHttpRequest.status+": "+textStatus);
										}
									});
								},
								ondelete : function(NODE, TREE_OBJ, RB) {
									$.ajax({
										type: "POST",
										url: "<?php echo pines_url('com_sales', 'catjson'); ?>",
										dataType: "json",
										data: {
											"do": "delete",
											"id": NODE.id
										},
										success: function(data, textStatus) {
											if (!data.status) {
												$.tree.rollback(RB);
												alert("A problem occurred while trying to delete the category.");
											}
										},
										error: function(XMLHttpRequest, textStatus, errorThrown) {
											$.tree.rollback(RB);
											alert("An error occurred trying to reach the server:\n"+XMLHttpRequest.status+": "+textStatus);
										}
									});
								},
								onmove : function(NODE, REF_NODE, TYPE, TREE_OBJ, RB) {
									var parent;
									var parent_id;
									if (TYPE == 'after' || TYPE == 'before') {
										parent = TREE_OBJ.parent(REF_NODE);
										if (parent === -1) {
											parent_id = "null";
										} else {
											parent_id = parent.attr("id");
										}
									} else {
										parent_id = REF_NODE.id;
									}
									$.ajax({
										type: "POST",
										url: "<?php echo pines_url('com_sales', 'catjson'); ?>",
										dataType: "json",
										data: {
											"do": "move",
											"id": NODE.id,
											"parent": parent_id
										},
										success: function(data, textStatus) {
											if (!data.status) {
												$.tree.rollback(RB);
												alert("A problem occurred while trying to move the category.");
											}
										},
										error: function(XMLHttpRequest, textStatus, errorThrown) {
											$.tree.rollback(RB);
											alert("An error occurred trying to reach the server:\n"+XMLHttpRequest.status+": "+textStatus);
										}
									});
								},
								oncopy : function(NODE, REF_NODE, TYPE, TREE_OBJ, RB) {
									var parent;
									var parent_id;
									if (TYPE == 'after' || TYPE == 'before') {
										parent = TREE_OBJ.parent(REF_NODE);
										if (parent === -1) {
											parent_id = "null";
										} else {
											parent_id = parent.attr("id");
										}
									} else {
										parent_id = REF_NODE.id;
									}
									$.ajax({
										type: "POST",
										url: "<?php echo pines_url('com_sales', 'catjson'); ?>",
										dataType: "json",
										data: {
											"do": "copy",
											"id": NODE.id,
											"parent": parent_id
										},
										success: function(data, textStatus) {
											if (!data.status) {
												$.tree.rollback(RB);
												alert("A problem occurred while trying to copy the category.");
											}
										},
										error: function(XMLHttpRequest, textStatus, errorThrown) {
											$.tree.rollback(RB);
											alert("An error occurred trying to reach the server:\n"+XMLHttpRequest.status+": "+textStatus);
										}
									});
								},
								oninit : function(TREE_OBJ) {
									$("#category_tree_new").click(function(){
										TREE_OBJ.create(false, -1);
									});
								},
								onchange : function(NODE, TREE_OBJ) {
									input.val("[]");
									$.each(TREE_OBJ.selected_arr, function(){
										input.val(JSON.stringify($.merge(JSON.parse(input.val()), [this.attr("id")])));
									});
								}
							},
							plugins : {
								contextmenu : {}
							}
						});

						// Category Dialog
						$("#category_dialog").dialog({
							bgiframe: true,
							autoOpen: false,
							modal: true,
							buttons: {
								'Done': function() {
									$(this).dialog('close');
								}
							}
						});

						$('#category_button').click(function() {
							$('#category_dialog').dialog('open');
						}).hover(
							function(){
								$(this).addClass("ui-state-hover");
							},
							function(){
								$(this).removeClass("ui-state-hover");
							}
						).mousedown(function(){
							$(this).addClass("ui-state-active");
						}).mouseup(function(){
							$(this).removeClass("ui-state-active");
						});

					});
					// ]]>
				</script>
				<button id="category_button" class="pf-field ui-state-default ui-corner-all" type="button">Pick Categories</button>
				<input id="categories" type="hidden" name="categories" />
			</div>
			<div id="category_dialog" title="Categories">
				<div id="category_tree" style="border: 1px solid black; float: left; width: 100%;"></div>
				<p style="clear: left;"><a href="#" id="category_tree_new">New Root Category</a></p>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="3" cols="35" class="peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Short Description</span><br />
				<textarea rows="3" cols="35" class="peditor_simple" style="width: 100%;" name="short_description"><?php echo $this->entity->short_description; ?></textarea>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Manufacturer</span>
					<select class="pf-field ui-widget-content" name="manufacturer">
						<option value="null">-- None --</option>
						<?php foreach ($this->manufacturers as $cur_manufacturer) { ?>
						<option value="<?php echo $cur_manufacturer->guid; ?>"<?php echo $this->entity->manufacturer->guid == $cur_manufacturer->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_manufacturer->name; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Manufacturer SKU</span>
					<input class="pf-field ui-widget-content" type="text" name="manufacturer_sku" size="24" value="<?php echo $this->entity->manufacturer_sku; ?>" /></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_images">
			<div class="pf-element">
				<label><span class="pf-label">Upload a New Picture</span>
					<span class="pf-note">Doesn't work yet.</span>
					<input class="pf-field ui-widget-content" type="file" name="image_upload" /></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_purchasing">
			<div class="pf-element">
				<script type="text/javascript">
				// <![CDATA[
				$(function(){
					var stock_type = $("#product_details [name=stock_type]");
					var pricing_method = $("#product_details [name=pricing_method]");
					var vendors_field = $("#vendors_field");
					var vendors_hidden = $("#vendors_hidden");
					stock_type.change(function(){
						if (stock_type.val() == "non_stocked") {
							vendors_field.fadeOut(null, function(){
								vendors_field.css("display", "none");
								vendors_hidden.fadeIn();
							});
							pricing_method.children("[value=margin]").attr("disabled", "disabled").end().change();
							if (pricing_method.val() == "margin")
								pricing_method.val("fixed");
						} else {
							vendors_hidden.fadeOut(null, function(){
								vendors_hidden.css("display", "none");
								vendors_field.fadeIn();
							});
							pricing_method.children("[value=margin]").removeAttr("disabled");
						}
					}).change();
				});
				// ]]>
				</script>
				<label><span class="pf-label">Stock Type</span>
					<span class="pf-note">Regular stock items cannot be sold without available stock. Stock optional items can be sold without available stock. Non stocked items do not use inventory tracking.</span>
					<select class="pf-field ui-widget-content" name="stock_type">
						<?php foreach (array('regular_stock' => 'Regular Stock', 'stock_optional' => 'Stock Optional', 'non_stocked' => 'Non Stocked') as $cur_stock_key => $cur_stock_type) { ?>
						<option value="<?php echo $cur_stock_key; ?>"<?php echo $this->entity->stock_type == $cur_stock_key ? ' selected="selected"' : ''; ?>><?php echo $cur_stock_type; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Vendors</span>
				<div class="pf-group">
					<div id="vendors_field" class="pf-field">
						<table id="vendors_table">
							<thead>
								<tr>
									<th>Vendor</th>
									<th>Vendor SKU</th>
									<th>Cost</th>
								</tr>
							</thead>
							<tbody>
								<?php if (is_array($this->entity->vendors)) { foreach ($this->entity->vendors as $cur_vendor) { ?>
								<tr title="<?php echo $cur_vendor['entity']->guid; ?>">
									<td><?php echo $cur_vendor['entity']->name; ?></td>
									<td><?php echo $cur_vendor['sku']; ?></td>
									<td><?php echo $cur_vendor['cost']; ?></td>
								</tr>
								<?php } } ?>
							</tbody>
						</table>
					</div>
					<span id="vendors_hidden" class="pf-field" style="display: none;">Vendors cannot be selected for non stocked items.</span>
					<input type="hidden" id="vendors" name="vendors" size="24" />
				</div>
			</div>
			<div id="vendor_dialog" title="Add a Vendor">
				<table id="available_vendors_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Corporate Phone</th>
							<th>Fax</th>
							<th>Account #</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->vendors as $cur_vendor) { ?>
						<tr title="<?php echo $cur_vendor->guid; ?>">
							<td><?php echo $cur_vendor->name; ?></td>
							<td><?php echo $cur_vendor->email; ?></td>
							<td><?php echo format_phone($cur_vendor->phone_work); ?></td>
							<td><?php echo format_phone($cur_vendor->fax); ?></td>
							<td><?php echo $cur_vendor->account_number; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<br class="pf-clearing" />
				<div style="width: 100%">
					<label>
						<span>Vendor SKU</span>
						<input type="text" name="cur_vendor_sku" id="cur_vendor_sku" />
					</label>
					<label>
						<span>Cost</span>
						<input type="text" name="cur_vendor_cost" id="cur_vendor_cost" />
					</label>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_pricing">
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var pricing_method = $("#product_details [name=pricing_method]");
						var unit_price = $("#product_details [name=unit_price]");
						var margin = $("#product_details [name=margin]");
						pricing_method.change(function(){
							if (pricing_method.val() == "margin") {
								unit_price.attr('disabled', 'disabled').addClass("ui-state-disabled");
								margin.removeAttr('disabled').removeClass("ui-state-disabled");
							} else {
								margin.attr('disabled', 'disabled').addClass("ui-state-disabled");
								unit_price.removeAttr('disabled').removeClass("ui-state-disabled");
							}
						}).change();
					});
					// ]]>
				</script>
				<label><span class="pf-label">Pricing Method</span>
					<select class="pf-field ui-widget-content" name="pricing_method">
						<option value="fixed" title="Only one price will be available."<?php echo $this->entity->pricing_method == 'fixed' ? ' selected="selected"' : ''; ?>>Fixed Pricing</option>
						<option value="variable" title="An employee can increase/decrease the price."<?php echo $this->entity->pricing_method == 'variable' ? ' selected="selected"' : ''; ?>>Variable Pricing</option>
						<option value="margin" title="The price is based on the cost of the item."<?php echo $this->entity->pricing_method == 'margin' ? ' selected="selected"' : ''; ?>>Margin Pricing</option>
					</select></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Defaults</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Unit Price</span>
					<input class="pf-field ui-widget-content" type="text" name="unit_price" size="24" value="<?php echo $this->entity->unit_price; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Margin</span>
					<input class="pf-field ui-widget-content" type="text" name="margin" size="24" value="<?php echo $this->entity->margin; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Floor</span>
					<span class="pf-note">The lowest price allowed.</span>
					<input class="pf-field ui-widget-content" type="text" name="floor" size="24" value="<?php echo $this->entity->floor; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Ceiling</span>
					<span class="pf-note">The highest price allowed.</span>
					<input class="pf-field ui-widget-content" type="text" name="ceiling" size="24" value="<?php echo $this->entity->ceiling; ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Taxes/Fees</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Tax Exempt</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="tax_exempt" size="24" value="ON"<?php echo $this->entity->tax_exempt ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Additional Fees</span>
					<span class="pf-note">These fees will be applied in addition to the group's default taxes. If you select a fee/tax applied to a group, it will be applied twice to this product for that group.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field ui-widget-content" name="additional_tax_fees[]" size="6" multiple="multiple">
						<?php foreach ($this->tax_fees as $cur_tax_fee) { ?>
						<option value="<?php echo $cur_tax_fee->guid; ?>"<?php echo ($cur_tax_fee->in_array($this->entity->additional_tax_fees)) ? ' selected="selected"' : ''; ?>><?php echo $cur_tax_fee->name; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_attributes">
			<div class="pf-element">
				<label><span class="pf-label">Weight</span>
					<input class="pf-field ui-widget-content" type="text" name="weight" size="10" value="<?php echo $this->entity->weight; ?>" /> lbs.</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">RMA Available After</span>
					<input class="pf-field ui-widget-content" type="text" name="rma_after" size="10" value="<?php echo $this->entity->rma_after; ?>" /> days.</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Serialized</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="serialized" size="24" value="ON"<?php echo $this->entity->serialized ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Discountable</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="discountable" size="24" value="ON"<?php echo $this->entity->discountable ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Require Customer</span>
					<span class="pf-note">This means a customer must be selected when selling this item.</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="require_customer" size="24" value="ON"<?php echo $this->entity->require_customer ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">One Per Ticket</span>
					<span class="pf-note">Only allow one of this item on a sales ticket.</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="one_per_ticket" size="24" value="ON"<?php echo $this->entity->one_per_ticket ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Hide on Invoice</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="hide_on_invoice" size="24" value="ON"<?php echo $this->entity->hide_on_invoice ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Non-Refundable</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="non_refundable" size="24" value="ON"<?php echo $this->entity->non_refundable ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Additional Barcodes</span>
				<div class="pf-group">
					<input class="pf-field ui-widget-content" type="text" name="additional_barcodes" size="24" value="<?php echo implode(',', $this->entity->additional_barcodes); ?>" />
					<script type="text/javascript">
						// <![CDATA[
						$(function(){
							$("#product_details [name=additional_barcodes]").ptags();
						});
						// ]]>
					</script>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Product Actions</span>
					<span class="pf-note">These actions will be executed when an event takes place with this product.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field ui-widget-content" name="actions[]" size="6" multiple="multiple">
						<?php foreach ($this->actions as $cur_action) { ?>
						<option value="<?php echo $cur_action['name']; ?>" title="<?php echo $cur_action['description']; ?>"<?php echo in_array($cur_action['name'], $this->entity->actions) ? ' selected="selected"' : ''; ?>><?php echo $cur_action['cname']; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_accounting">
			<div class="pf-element">
				<span class="pf-label">Nothing here yet...</span>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'listproducts')); ?>');" value="Cancel" />
	</div>
</form>