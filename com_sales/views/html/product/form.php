<?php
/**
 * Provides a form for the user to edit a product.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Product' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide product details in this form.';
$pines->editor->load();
$pines->uploader->load();
$pines->com_pgrid->load();
$pines->com_ptags->load();
$pines->com_sales->load_jcrop();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'product/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			var vendors = $("#p_muid_vendors");
			var vendors_table = $("#p_muid_vendors_table");
			var available_vendors_table = $("#p_muid_available_vendors_table");
			var vendor_dialog = $("#p_muid_vendor_dialog");
			var cur_vendor = null;

			vendors_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Vendor',
						extra_class: 'picon picon-list-add',
						selection_optional: true,
						click: function(){
							cur_vendor = null;
							vendor_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit Vendor',
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							cur_vendor = rows;
							available_vendors_table.pgrid_get_all_rows().filter('[title='+cur_vendor.attr('title')+']').pgrid_select_rows();
							vendor_dialog.find("input[name=cur_vendor_sku]").val(pines.unsafe(cur_vendor.pgrid_get_value(2)));
							vendor_dialog.find("input[name=cur_vendor_cost]").val(pines.unsafe(cur_vendor.pgrid_get_value(3)));
							vendor_dialog.find("input[name=cur_vendor_link]").val(pines.unsafe(cur_vendor.pgrid_get_value(4).replace(/.*?>(.*)?<.*/i, '$1')));
							vendor_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Vendor',
						extra_class: 'picon picon-list-remove',
						click: function(e, rows){
							rows.pgrid_delete();
							update_vendors();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// Needs to be gridified before it's hidden.
			available_vendors_table.pgrid({
				pgrid_multi_select: false,
				pgrid_paginate: false,
				pgrid_view_height: "300px"
			});

			// Vendor Dialog
			vendor_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function(){
						var cur_vendor_sku = $("#p_muid_cur_vendor_sku").val();
						var cur_vendor_cost = $("#p_muid_cur_vendor_cost").val();
						var cur_vendor_link = $("#p_muid_cur_vendor_link").val();
						var cur_vendor_entity = available_vendors_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!cur_vendor_entity[0]) {
							alert("Please select a vendor.");
							return;
						}
						if (cur_vendor_sku == "" || cur_vendor_cost == "") {
							alert("Please provide both a SKU and a cost for this vendor.");
							return;
						}
						cur_vendor_link = '<a href="'+pines.safe(cur_vendor_link)+'" target="_blank">'+pines.safe(cur_vendor_link)+'</a>';
						if (cur_vendor == null) {
							var new_vendor = [{
								key: cur_vendor_entity[0].key,
								values: [
									pines.safe(cur_vendor_entity[0].values[0]),
									pines.safe(cur_vendor_sku),
									pines.safe(cur_vendor_cost),
									cur_vendor_link
								]
							}];
							vendors_table.pgrid_add(new_vendor);
						} else {
							cur_vendor.attr('title', cur_vendor_entity[0].key);
							cur_vendor.pgrid_set_value(1, pines.safe(cur_vendor_entity[0].values[0]));
							cur_vendor.pgrid_set_value(2, pines.safe(cur_vendor_sku));
							cur_vendor.pgrid_set_value(3, pines.safe(cur_vendor_cost));
							cur_vendor.pgrid_set_value(4, cur_vendor_link);
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					cur_vendor = null;
					update_vendors();
				}
			});

			var update_vendors = function(){
				available_vendors_table.pgrid_get_selected_rows().pgrid_deselect_rows();
				$("#p_muid_cur_vendor_sku").val("");
				$("#p_muid_cur_vendor_cost").val("");
				$("#p_muid_cur_vendor_link").val("");
				vendors.val(JSON.stringify(vendors_table.pgrid_get_all_rows().pgrid_export_rows()));
			};
			update_vendors();
		});
	</script>
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_general" data-toggle="tab">General</a></li>
		<li><a href="#p_muid_tab_categories" data-toggle="tab">Categories</a></li>
		<li><a href="#p_muid_tab_purchasing" data-toggle="tab">Purchasing</a></li>
		<li><a href="#p_muid_tab_pricing" data-toggle="tab">Pricing</a></li>
		<li><a href="#p_muid_tab_attributes" data-toggle="tab">Attributes</a></li>
		<?php if ($pines->config->com_sales->com_hrm) { ?>
		<li><a href="#p_muid_tab_commission" data-toggle="tab">Commission</a></li>
		<?php } ?>
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);">Appearance <b class="caret"></b></a>
			<ul class="dropdown-menu">
				<li><a href="#p_muid_tab_images" data-toggle="tab">Images</a></li>
				<?php if ($pines->config->com_sales->com_storefront) { ?>
				<li class="divider"></li>
				<li><a href="#p_muid_tab_storefront" data-toggle="tab">Storefront</a></li>
				<li><a href="#p_muid_tab_head" data-toggle="tab">Page Head</a></li>
				<?php } ?>
			</ul>
		</li>
	</ul>
	<div id="p_muid_product_tabs" class="tab-content">
		<div class="tab-pane active" id="p_muid_tab_general">
			<div class="pf-element pf-full-width">
				<label>
					<span class="pf-label">Name</span>
					<span class="pf-group pf-full-width">
						<span class="pf-field" style="display: block;">
							<input style="width: 100%;" type="text" name="name" value="<?php echo htmlspecialchars($this->entity->name); ?>" />
						</span>
					</span>
				</label>
			</div>
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
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Product SKU</span>
					<input class="pf-field" type="text" name="sku" size="24" value="<?php echo htmlspecialchars($this->entity->sku); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Manufacturer</span>
					<select class="pf-field" name="manufacturer">
						<option value="null">-- None --</option>
						<?php foreach ($this->manufacturers as $cur_manufacturer) { ?>
						<option value="<?php echo htmlspecialchars($cur_manufacturer->guid); ?>"<?php echo $this->entity->manufacturer->guid == $cur_manufacturer->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_manufacturer->name); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Manufacturer SKU</span>
					<input class="pf-field" type="text" name="manufacturer_sku" size="24" value="<?php echo htmlspecialchars($this->entity->manufacturer_sku); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Receipt Description</span>
					<span class="pf-note">A short description to be shown on receipts.</span>
					<input class="pf-field" type="text" name="receipt_description" size="40" value="<?php echo htmlspecialchars($this->entity->receipt_description); ?>" /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Short Description</span><br />
				<textarea rows="3" cols="35" class="peditor-simple" style="width: 100%;" name="short_description"><?php echo htmlspecialchars($this->entity->short_description); ?></textarea>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="9" cols="35" class="peditor" style="width: 100%; height: 400px;" name="description"><?php echo htmlspecialchars($this->entity->description); ?></textarea>
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_categories">
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					pines(function(){
						// Category Grid
						$("#p_muid_category_grid").pgrid({
							pgrid_toolbar: true,
							pgrid_toolbar_contents: [
								{type: 'button', text: 'Expand', title: 'Expand All', extra_class: 'picon picon-arrow-down', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_expand_rows();
								}},
								{type: 'button', text: 'Collapse', title: 'Collapse All', extra_class: 'picon picon-arrow-right', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_collapse_rows();
								}},
								{type: 'separator'},
								{type: 'button', text: 'All', title: 'Check All', extra_class: 'picon picon-checkbox', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).attr("checked", "true").change();
								}},
								{type: 'button', text: 'None', title: 'Check None', extra_class: 'picon picon-dialog-cancel', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).removeAttr("checked").change();
								}}
							],
							pgrid_hidden_cols: [1],
							pgrid_sort_col: 1,
							pgrid_sort_ord: "asc",
							pgrid_child_prefix: "ch_",
							pgrid_paginate: false,
							pgrid_view_height: "300px"
						}).on("change", ":checkbox", function(e){
							var box = $(this),
								row = box.closest("tr"),
								desc = row.pgrid_add_descendant_rows().not(row);
							if (box.is(":checked") && desc.find(":checkbox:checked").length && !confirm("You have already selected a descendant of this category, which puts the product\nin this category in the storefront. Are you sure you want to add this category\ntoo?")) {
								box.removeAttr("checked");
								e.preventDefault();
								e.stopPropagation();
								e.stopImmediatePropagation();
							}
						});
					});
				</script>
				<table id="p_muid_category_grid">
					<thead>
						<tr>
							<th>Order</th>
							<th>In</th>
							<th>Name</th>
							<th>Products</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$category_guids = $this->entity->get_categories_guid();
						foreach($this->categories as $cur_category) { ?>
						<tr title="<?php echo htmlspecialchars($cur_category->guid); ?>" class="<?php echo $cur_category->children ? 'parent ' : ''; ?><?php echo isset($cur_category->parent) ? htmlspecialchars("child ch_{$cur_category->parent->guid} ") : ''; ?>">
							<td><?php echo isset($cur_category->parent) ? $cur_category->array_search($cur_category->parent->children) + 1 : '0' ; ?></td>
							<td><input type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($cur_category->guid); ?>" <?php echo in_array($cur_category->guid, $category_guids) ? 'checked="checked" ' : ''; ?>/></td>
							<td><a data-entity="<?php echo htmlspecialchars($cur_category->guid); ?>" data-entity-context="com_sales_category"><?php echo htmlspecialchars($cur_category->name); ?></a></td>
							<td><?php echo count($cur_category->products); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_purchasing">
			<div class="pf-element">
				<script type="text/javascript">
				pines(function(){
					var stock_type = $("#p_muid_form [name=stock_type]");
					var pricing_method = $("#p_muid_form [name=pricing_method]");
					var vendors_field = $("#p_muid_vendors_field");
					var vendors_hidden = $("#p_muid_vendors_hidden");
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
				</script>
				<label><span class="pf-label">Stock Type</span>
					<span class="pf-note">Regular stock items cannot be sold without available stock. Stock optional items can be sold without available stock. Non stocked items do not use inventory tracking.</span>
					<select class="pf-field" name="stock_type">
						<?php foreach (array('regular_stock' => 'Regular Stock', 'stock_optional' => 'Stock Optional', 'non_stocked' => 'Non Stocked') as $cur_stock_key => $cur_stock_type) { ?>
						<option value="<?php echo htmlspecialchars($cur_stock_key); ?>"<?php echo $this->entity->stock_type == $cur_stock_key ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_stock_type); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					pines(function(){
						$("#p_muid_form").on("change", "input[name=custom_item], input[name=show_in_storefront]", function(){
							var custom_item = $("#p_muid_form input[name=custom_item]"),
								show_in_storefront = $("#p_muid_form input[name=show_in_storefront]");
							if (custom_item.is(":checked") && show_in_storefront.is(":checked")) {
								$(this).removeAttr("checked");
								var notice = $.pnotify('The product cannot be both a custom item and shown in the storefront.<br/><br/><button class="btn" type="button">Swap Options</button>').on("click", "button", function(){
									custom_item.add(show_in_storefront).each(function(){
										var cur_box = $(this);
										if (cur_box.is(":checked"))
											cur_box.removeAttr("checked");
										else
											cur_box.attr("checked", "checked");
									});
									notice.pnotify_remove();
								});
							}
						});
					});
				</script>
				<label><span class="pf-label">Custom Item</span>
					<span class="pf-note">Custom items aren't shown in the storefront.</span>
					<input class="pf-field" type="checkbox" name="custom_item" value="ON"<?php echo $this->entity->custom_item ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Vendors</span>
				<div id="p_muid_vendors_field" class="pf-group">
					<div class="pf-field">
						<table id="p_muid_vendors_table">
							<thead>
								<tr>
									<th>Vendor</th>
									<th>Vendor SKU</th>
									<th>Cost</th>
									<th>Link</th>
								</tr>
							</thead>
							<tbody>
								<?php if (is_array($this->entity->vendors)) { foreach ($this->entity->vendors as $cur_vendor) { ?>
								<tr title="<?php echo htmlspecialchars($cur_vendor['entity']->guid); ?>">
									<td><a data-entity="<?php echo htmlspecialchars($cur_vendor['entity']->guid); ?>" data-entity-context="com_sales_vendor"><?php echo htmlspecialchars($cur_vendor['entity']->name); ?></a></td>
									<td><?php echo htmlspecialchars($cur_vendor['sku']); ?></td>
									<td><?php echo htmlspecialchars($cur_vendor['cost']); ?></td>
									<td><a href="<?php echo htmlspecialchars($cur_vendor['link']); ?>" target="_blank"><?php echo htmlspecialchars($cur_vendor['link']); ?></a></td>
								</tr>
								<?php } } ?>
							</tbody>
						</table>
					</div>
					<input type="hidden" id="p_muid_vendors" name="vendors" />
				</div>
				<span id="p_muid_vendors_hidden" class="pf-field" style="display: none;">Vendors cannot be selected for non stocked items.</span>
			</div>
			<div id="p_muid_vendor_dialog" title="Add a Vendor">
				<div class="pf-form">
					<table id="p_muid_available_vendors_table">
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
							<tr title="<?php echo htmlspecialchars($cur_vendor->guid); ?>">
								<td><a data-entity="<?php echo htmlspecialchars($cur_vendor->guid); ?>" data-entity-context="com_sales_vendor"><?php echo htmlspecialchars($cur_vendor->name); ?></a></td>
								<td><?php echo htmlspecialchars($cur_vendor->email); ?></td>
								<td><?php echo htmlspecialchars(format_phone($cur_vendor->phone_work)); ?></td>
								<td><?php echo htmlspecialchars(format_phone($cur_vendor->fax)); ?></td>
								<td><?php echo htmlspecialchars($cur_vendor->account_number); ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<br class="pf-clearing" />
					<div class="pf-element">
						<label><span class="pf-label">Vendor SKU</span>
							<input type="text" class="pf-field" name="cur_vendor_sku" size="15" id="p_muid_cur_vendor_sku" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Cost</span>
							<input type="text" class="pf-field" name="cur_vendor_cost" size="8" id="p_muid_cur_vendor_cost" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Link</span>
							<input type="url" class="pf-field" name="cur_vendor_link" size="20" id="p_muid_cur_vendor_link" /></label>
					</div>
				</div>
				<br />
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_pricing">
			<div class="pf-element">
				<script type="text/javascript">
					pines(function(){
						var pricing_method = $("#p_muid_form [name=pricing_method]");
						var unit_price = $("#p_muid_form [name=unit_price]");
						var margin = $("#p_muid_form [name=margin]");
						pricing_method.change(function(){
							if (pricing_method.val() == "margin") {
								unit_price.attr('disabled', 'disabled');
								margin.removeAttr('disabled');
							} else {
								margin.attr('disabled', 'disabled');
								unit_price.removeAttr('disabled');
							}
						}).change();
					});
				</script>
				<label><span class="pf-label">Pricing Method</span>
					<select class="pf-field" name="pricing_method">
						<option value="fixed" title="Only one price will be available."<?php echo $this->entity->pricing_method == 'fixed' ? ' selected="selected"' : ''; ?>>Fixed Pricing</option>
						<option value="variable" title="An employee can increase/decrease the price."<?php echo $this->entity->pricing_method == 'variable' ? ' selected="selected"' : ''; ?>>Variable Pricing</option>
						<option value="margin" title="The price is based on the cost of the item."<?php echo $this->entity->pricing_method == 'margin' ? ' selected="selected"' : ''; ?>>Margin Pricing</option>
					</select></label>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					pines(function(){
						$("#p_muid_product_exp").datepicker({
							dateFormat: "yy-mm-dd",
							showOtherMonths: true,
							selectOtherMonths: true,
							minDate: '0'
						});
					});
				</script>
				<label><span class="pf-label">Product Expiration Date
						<?php if ($pines->config->com_sales->require_expiration) { ?>
						<span class="pf-required">*</span>
						<?php } ?>
					</span>
					<span class="pf-note">Only informational. Doesn't affect product availability.</span>
					<input class="pf-field" type="text" id="p_muid_product_exp" name="product_exp" size="24" value="<?php echo ($this->entity->product_exp ? htmlspecialchars(format_date($this->entity->product_exp, 'date_sort')) : ''); ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h3>Defaults</h3>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Unit Price</span>
					<input class="pf-field" type="text" name="unit_price" size="24" value="<?php echo htmlspecialchars($this->entity->unit_price); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Margin</span>
					<input class="pf-field" type="text" name="margin" size="24" value="<?php echo htmlspecialchars($this->entity->margin); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Floor</span>
					<span class="pf-note">The lowest price allowed.</span>
					<input class="pf-field" type="text" name="floor" size="24" value="<?php echo htmlspecialchars($this->entity->floor); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Ceiling</span>
					<span class="pf-note">The highest price allowed.</span>
					<input class="pf-field" type="text" name="ceiling" size="24" value="<?php echo htmlspecialchars($this->entity->ceiling); ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h3>Taxes/Fees</h3>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Tax Exempt</span>
					<input class="pf-field" type="checkbox" name="tax_exempt" value="ON"<?php echo $this->entity->tax_exempt ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Additional Fees</span>
					<span class="pf-note">These fees will be applied in addition to the group's default taxes. If you select a fee/tax applied to a group, it will be applied twice to this product for that group.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field" name="additional_tax_fees[]" size="6" multiple="multiple">
						<?php foreach ($this->tax_fees as $cur_tax_fee) { ?>
						<option value="<?php echo htmlspecialchars($cur_tax_fee->guid); ?>"<?php echo ($cur_tax_fee->in_array($this->entity->additional_tax_fees)) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_tax_fee->name); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Return Fee Checklists</span>
					<span class="pf-note">These checklists will be used to calculate additional restocking/return fees.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field" name="return_checklists[]" size="6" multiple="multiple">
						<?php foreach ($this->return_checklists as $cur_return_checklist) { ?>
						<option value="<?php echo htmlspecialchars($cur_return_checklist->guid); ?>"<?php echo ($cur_return_checklist->in_array($this->entity->return_checklists)) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_return_checklist->name); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_attributes">
			<div class="pf-element">
				<label><span class="pf-label">Weight</span>
					<input class="pf-field" type="text" name="weight" size="10" value="<?php echo htmlspecialchars($this->entity->weight); ?>" /> lbs.</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">RMA Available After</span>
					<input class="pf-field" type="text" name="rma_after" size="10" value="<?php echo htmlspecialchars($this->entity->rma_after); ?>" /> days.</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Serialized</span>
					<input class="pf-field" type="checkbox" name="serialized" value="ON"<?php echo $this->entity->serialized ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Discountable</span>
					<input class="pf-field" type="checkbox" name="discountable" value="ON"<?php echo $this->entity->discountable ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if ($pines->config->com_sales->com_customer) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Require Customer</span>
					<span class="pf-note">This means a customer must be selected when selling this item.</span>
					<input class="pf-field" type="checkbox" name="require_customer" value="ON"<?php echo $this->entity->require_customer ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">One Per Ticket</span>
					<span class="pf-note">Only allow one of this item on a sales ticket.</span>
					<input class="pf-field" type="checkbox" name="one_per_ticket" value="ON"<?php echo $this->entity->one_per_ticket ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Hide on Invoice</span>
					<input class="pf-field" type="checkbox" name="hide_on_invoice" value="ON"<?php echo $this->entity->hide_on_invoice ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Non-Refundable</span>
					<input class="pf-field" type="checkbox" name="non_refundable" value="ON"<?php echo $this->entity->non_refundable ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Hide from Autocomplete</span>
					<span class="pf-note">Exclude this product from autocomplete searches.</span>
					<input class="pf-field" type="checkbox" name="autocomplete_hide" value="ON"<?php echo $this->entity->autocomplete_hide ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Additional Barcodes</span>
				<div class="pf-group">
					<input class="pf-field" type="text" name="additional_barcodes" size="24" value="<?php echo htmlspecialchars(implode(',', $this->entity->additional_barcodes)); ?>" />
					<script type="text/javascript">
						pines(function(){
							$("#p_muid_form [name=additional_barcodes]").ptags({
								ptags_sortable: {
									tolerance: 'pointer',
									handle: '.ui-ptags-tag-text'
								}
							});
						});
					</script>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Product Actions</span>
					<span class="pf-note">These actions will be executed when an event takes place with this product.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field" name="actions[]" size="6" multiple="multiple">
						<?php foreach ($this->actions as $cur_action) { ?>
						<option value="<?php echo htmlspecialchars($cur_action['name']); ?>" title="<?php echo htmlspecialchars($cur_action['description']); ?>"<?php echo in_array($cur_action['name'], $this->entity->actions) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_action['cname']); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php if ($pines->config->com_sales->com_hrm) { ?>
		<script type="text/javascript">
			pines(function(){
				// Commissions
				var commissions = $("#p_muid_form [name=commissions]");
				var commissions_table = $("#p_muid_form .commissions_table");
				var commission_dialog = $("#p_muid_form .commission_dialog");
				var cur_commission = null;

				commissions_table.pgrid({
					pgrid_paginate: false,
					pgrid_toolbar: true,
					pgrid_toolbar_contents : [
						{
							type: 'button',
							text: 'Add Commission',
							extra_class: 'picon picon-document-new',
							selection_optional: true,
							click: function(){
								cur_commission = null;
								commission_dialog.dialog('open');
							}
						},
						{
							type: 'button',
							text: 'Edit Commission',
							extra_class: 'picon picon-document-edit',
							double_click: true,
							click: function(e, rows){
								cur_commission = rows;
								commission_dialog.find("select[name=cur_commission_group]").val(pines.unsafe(rows.pgrid_get_value(1)));
								commission_dialog.find("select[name=cur_commission_type]").val(pines.unsafe(rows.pgrid_get_value(2)));
								commission_dialog.find("input[name=cur_commission_amount]").val(pines.unsafe(rows.pgrid_get_value(3)));
								commission_dialog.dialog('open');
							}
						},
						{
							type: 'button',
							text: 'Remove Commission',
							extra_class: 'picon picon-edit-delete',
							click: function(e, rows){
								rows.pgrid_delete();
								update_commissions();
							}
						}
					],
					pgrid_view_height: "300px"
				});

				// Commission Dialog
				commission_dialog.dialog({
					bgiframe: true,
					autoOpen: false,
					modal: true,
					width: 600,
					buttons: {
						"Done": function(){
							var cur_commission_group = commission_dialog.find("select[name=cur_commission_group]").val();
							var cur_commission_type = commission_dialog.find("select[name=cur_commission_type]").val();
							var cur_commission_amount = commission_dialog.find("input[name=cur_commission_amount]").val();
							if (cur_commission_group == "" || cur_commission_type == "" || cur_commission_amount == "") {
								alert("Please provide both a type and an amount for this commission.");
								return;
							}
							if (cur_commission == null) {
								var new_commission = [{
									key: null,
									values: [
										pines.safe(cur_commission_group),
										pines.safe(cur_commission_type),
										pines.safe(cur_commission_amount)
									]
								}];
								commissions_table.pgrid_add(new_commission);
							} else {
								cur_commission.pgrid_set_value(1, pines.safe(cur_commission_group));
								cur_commission.pgrid_set_value(2, pines.safe(cur_commission_type));
								cur_commission.pgrid_set_value(3, pines.safe(cur_commission_amount));
							}
							$(this).dialog('close');
						}
					},
					close: function(){
						update_commissions();
					}
				});

				var update_commissions = function(){
					commission_dialog.find("select[name=cur_commission_group]").val("");
					commission_dialog.find("select[name=cur_commission_type]").val("");
					commission_dialog.find("input[name=cur_commission_amount]").val("");
					commissions.val(JSON.stringify(commissions_table.pgrid_get_all_rows().pgrid_export_rows()));
				};

				update_commissions();
			});
		</script>
		<div class="tab-pane" id="p_muid_tab_commission">
			<div class="pf-element pf-full-width">
				<table class="commissions_table">
					<thead>
						<tr>
							<th>Group</th>
							<th>Type</th>
							<th>Amount</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->commissions)) foreach ($this->entity->commissions as $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars("{$cur_value['group']->guid}: {$cur_value['group']->name}"); ?></td>
							<td><?php echo htmlspecialchars($cur_value['type']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['amount']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="commissions" />
			</div>
			<div class="commission_dialog" style="display: none;" title="Add a Commission">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Group</span>
							<select class="pf-field" name="cur_commission_group">
								<?php foreach ($this->groups as $cur_group) {
								?><option value="<?php echo htmlspecialchars("{$cur_group->guid}: {$cur_group->name}"); ?>"><?php echo htmlspecialchars("{$cur_group->name} [{$cur_group->groupname}]"); ?></option><?php
								} ?>
							</select>
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Type</span>
							<select class="pf-field" name="cur_commission_type">
								<option value="spiff">Spiff (Fixed Amount)</option>
								<option value="percent_price">% Price (Before Tax, After Specials)</option>
								<option value="percent_line_total">% Line Total (Before Tax, Before Specials)</option>
							</select>
						</label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Amount</span>
							<span class="pf-note">$ or %</span>
							<input class="pf-field" type="text" name="cur_commission_amount" size="24" onkeyup="this.value=this.value.replace(/[^\d.]/g, '');" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
		<div class="tab-pane" id="p_muid_tab_images">
			<style type="text/css" scoped="scoped">
				#p_muid_sortable {
					list-style-type: none;
					margin: 0;
					padding: 0;
				}
				#p_muid_sortable li {
					margin: 0.1em;
					padding: 5px;
					float: left;
					width: 120px;
					min-height: 120px;
					text-align: center;
					background-image: none;
					position: relative;
				}
				#p_muid_sortable .remove {
					position: absolute;
					top: .5em;
					right: .5em;
				}
				#p_muid_sortable img {
					width: 110px;
					height: auto;
					max-height: 110px;
					vertical-align: middle;
					margin: 0;
					padding: 0;
				}
				#p_muid_sortable p, #p_muid_sortable textarea {
					text-align: left;
					margin: .4em 0 0;
					padding: 0;
					border: 0 none;
					box-shadow: none;
					-moz-box-shadow: none;
					-webkit-box-shadow: none;
					outline: none;
					font-size: 1em;
					line-height: 1;
					white-space: pre-wrap;
					height: 3em;
					overflow: auto;
					resize: none;
				}
			</style>
			<script type="text/javascript">
				pines(function(){
					var tmp_url = <?php echo json_encode(pines_url('com_sales', 'product/temp_image', array('image' => '__image__', 'type' => '__type__', 'source' => '__source__', 'options' => '__options__'))); ?>;
					var update_images = function(){
						var images = [];
						$("li", "#p_muid_sortable").each(function(){
							var cur_entry = $(this),
								alt = cur_entry.find("p").html(),
								options = cur_entry.attr("data-options");
							if (alt == 'Click to add description...')
								alt = '';
							var struct = {
								"file": cur_entry.attr("data-image"),
								"thumbnail": cur_entry.attr("data-thumbnail"),
								"source": cur_entry.attr("data-source"),
								"alt": alt
							};
							if (options && JSON.parse(options))
								struct.options = JSON.parse(options);
							images.push(struct);
						});
						$("input[name=images]", "#p_muid_tab_images").val(JSON.stringify(images));
					};
					update_images();

					var add_image = function(image){
						$('<li class="thumbnail" data-source="temp"><button type="button" style="display: none;" class="remove btn btn-mini btn-danger"><i class="icon-remove"></i></button><a href="'+pines.safe(image.img_url)+'" target="_blank"><img alt="'+pines.safe(image.img.replace(/.*\//, ''))+'" src="'+pines.safe(image.tmb_url)+'" /></a><p>Click to add description...</p></li>')
						.attr("data-image", image.img).attr("data-thumbnail", image.tmb).appendTo("#p_muid_sortable");
					};

					$("#p_muid_image_upload").change(function(){
						var input = $(this);
						if (input.val() == "")
							return;
						var images = input.val().split('//');
						$.each(images, function(i, v){
							add_image({
								"img": v,
								"img_url": tmp_url.replace('__image__', escape(v)).replace('__type__', 'prod_img').replace('__source__', 'temp').replace('__options__', ''),
								"tmb": v,
								"tmb_url": tmp_url.replace('__image__', escape(v)).replace('__type__', 'prod_tmb').replace('__source__', 'temp').replace('__options__', '')
							});
						});
						input.val("").change();
						update_images();
					});
					$("#p_muid_sortable").on("click", "li p", function(){
						var cur_alt = $(this),
							desc = cur_alt.html(),
							ta = $('<textarea cols="4" rows="3" style="width: 100%">'+pines.safe(desc)+'</textarea>')
						.insertAfter(cur_alt)
						.focusin(function(){
							$(this).focusout(function(){
								cur_alt.insertAfter(this).html(pines.safe($(this).remove().val()));
								update_images();
							});
						});
						cur_alt.detach();
						setTimeout(function(){
							ta.focus().select();
						}, 1);
					}).on("click", "li a", function(e){
						// Simple image editing...
						var link = $(this),
							li = link.closest("li"),
							cur_href = link.attr("href"),
							cur_source = li.attr("data-source"),
							cur_img = li.attr("data-image"),
							options = {};
						if (li.attr("data-options") && JSON.parse(li.attr("data-options")))
							options = JSON.parse(li.attr("data-options"));
						var dialog = $('<div title="Crop Image and Thumbnail">You can crop the image to a smaller size:</div>').dialog({
							bgiframe: true,
							autoOpen: false,
							modal: true,
							width: 800,
							position: "top",
							buttons: {
								"Save": function(){
									li.attr("data-options", JSON.stringify(options));
									$(this).dialog('close');
									li.find("img").attr("src", tmp_url.replace('__image__', escape(cur_img)).replace('__type__', 'prod_tmb').replace('__source__', cur_source).replace('__options__', escape(JSON.stringify(options))));
									update_images();
								},
								"Cancel": function(){
									$(this).dialog('close');
								}
							},
							close: function(){
								$(this).dialog('destroy').remove();
							}
						});
						var canvas = $('<div class="well" style="overflow: auto; text-align: center;"><div style="display: inline-block;" class="thumbnail"></div></div>').appendTo(dialog);
						var cropper = $('<img />', {"src": cur_href}).appendTo(canvas.children()).Jcrop({
							onSelect: function(c){
								thumbnails.find("a[data-method=crop] img").attr("src", tmp_url.replace('__image__', escape(cur_img)).replace('__type__', 'prod_tmb').replace('__source__', cur_source).replace('__options__', escape(JSON.stringify($.extend({}, c, {"tmb_method":"crop"})))))
								.end().find("a[data-method=fit] img").attr("src", tmp_url.replace('__image__', escape(cur_img)).replace('__type__', 'prod_tmb').replace('__source__', cur_source).replace('__options__', escape(JSON.stringify($.extend({}, c, {"tmb_method":"fit"})))));
								$.extend(options, c);
							},
							onRelease: function(){
								thumbnails.find("a[data-method=crop] img").attr("src", tmp_url.replace('__image__', escape(cur_img)).replace('__type__', 'prod_tmb').replace('__source__', cur_source).replace('__options__', escape('{"tmb_method":"crop"}')))
								.end().find("a[data-method=fit] img").attr("src", tmp_url.replace('__image__', escape(cur_img)).replace('__type__', 'prod_tmb').replace('__source__', cur_source).replace('__options__', escape('{"tmb_method":"fit"}')));
								delete options.x; delete options.y; delete options.x2; delete options.y2; delete options.w; delete options.h;
							}
						});

						var thumbnails = $('<div>You can choose the best thumbnail:<ul class="thumbnails"><li>'+
						'<a href="javascript:void(0);" class="thumbnail" data-method="crop"><img src="" alt="Crop method." />'+
						'<span style="display: block; width: <?php echo (int) $pines->config->com_sales->product_images_tmb_width; ?>px; text-align: center;" class="caption">Crop the scaled image to fit.</span></a></li>'+
						'<li><a href="javascript:void(0);" class="thumbnail" data-method="fit"><img src="" alt="Fit method." />'+
						'<span style="display: block; width: <?php echo (int) $pines->config->com_sales->product_images_tmb_width; ?>px; text-align: center;" class="caption">Pad the image to fit it all.</span></a></li></ul></div>')
						.find("a[data-method=crop] img").attr("src", tmp_url.replace('__image__', escape(cur_img)).replace('__type__', 'prod_tmb').replace('__source__', cur_source).replace('__options__', escape('{"tmb_method":"crop"}'))).end()
						.find("a[data-method=fit] img").attr("src", tmp_url.replace('__image__', escape(cur_img)).replace('__type__', 'prod_tmb').replace('__source__', cur_source).replace('__options__', escape('{"tmb_method":"fit"}'))).end()
						.on("click", "a", function(){
							var a = $(this);
							a.toggleClass("alert-success").closest("ul").find("a").not(this).removeClass("alert-success");
							if (a.hasClass("alert-success"))
								$.extend(options, {"tmb_method":a.attr("data-method")});
							else if (options.tmb_method)
								delete options.tmb_method;
						})
						.appendTo(dialog);

						if (options.x && options.y && options.x2 && options.y2)
							cropper.Jcrop({setSelect: [options.x, options.y, options.x2, options.y2]});
						if (options.tmb_method)
							thumbnails.find("a[data-method="+options.tmb_method+"]").addClass("alert-success");

						dialog.dialog("open");
						e.preventDefault();
					}).on("mouseenter", "li", function(){
						$(".remove", this).show();
					}).on("mouseleave", "li", function(){
						$(".remove", this).hide();
					}).on("click", ".remove", function(){
						$(this).closest("li").fadeOut(300, function(){
							$(this).remove();
							update_images();
						});
					}).sortable({
						placeholder: 'ui-state-highlight',
						distance: 20,
						update: function(){update_images();}
					});
					$("#p_muid_clear").click(function(){
						$("li", "#p_muid_sortable").fadeOut(300, function(){
							$("li", "#p_muid_sortable").remove();
							update_images();
						});
					});

					$("#p_muid_thumbnail").change(function(){
						$("#p_muid_thumbnail_preview").attr("src", tmp_url.replace('__image__', escape($(this).val())).replace('__type__', 'thumbnail').replace('__source__', 'temp').replace('__options__', ''));
					});
				});
			</script>
			<div class="pf-element">
				<span class="pf-label">Add Image(s)</span>
				<input class="pf-field puploader puploader-temp puploader-multiple" id="p_muid_image_upload" type="text" value="" />
			</div>
			<div class="pf-element">
				<span class="pf-label">Images</span>
				<span class="pf-note">The first image will be the default image.</span>
				<div class="pf-note">
					<button type="button" class="btn btn-danger" id="p_muid_clear"><i class="icon-trash"></i> Clear</button>
				</div>
				<div class="pf-group">
					<ul id="p_muid_sortable" class="pf-field">
						<?php if ($this->entity->images) { foreach ($this->entity->images as $cur_image) { ?>
						<li data-source="file" data-image="<?php echo htmlspecialchars($cur_image['file']); ?>" data-thumbnail="<?php echo htmlspecialchars($cur_image['thumbnail']); ?>" class="thumbnail">
							<button type="button" style="display: none;" class="remove btn btn-mini btn-danger"><i class="icon-remove"></i></button>
							<a href="<?php echo htmlspecialchars($pines->config->location.$cur_image['file']); ?>" target="_blank"><img alt="<?php echo htmlspecialchars(basename($cur_image['file'])); ?>" src="<?php echo htmlspecialchars($pines->config->location.$cur_image['thumbnail']); ?>" /></a>
							<p><?php echo empty($cur_image['alt']) ? 'Click to add description...' : htmlspecialchars(basename($cur_image['alt'])); ?></p>
						</li>
						<?php } } ?>
					</ul>
					<br class="pf-clearing" />
				</div>
			</div>
			<div class="pf-element">
				<span class="pf-label">Thumbnail</span>
				<input class="pf-field puploader puploader-temp" id="p_muid_thumbnail" type="text" name="thumbnail" value="<?php echo htmlspecialchars($this->entity->thumbnail); ?>" />
			</div>
			<div class="pf-element">
				<span class="pf-label">Thumbnail Preview</span>
				<div class="pf-group">
					<div class="pf-field">
						<div class="thumbnail">
							<img alt="Thumbnail Preview" id="p_muid_thumbnail_preview" src="<?php echo htmlspecialchars($pines->config->location.$this->entity->thumbnail); ?>" />
						</div>
					</div>
				</div>
			</div>
			<input type="hidden" name="images" />
			<br class="pf-clearing" />
		</div>
		<?php if ($pines->config->com_sales->com_storefront) { ?>
		<style type="text/css">
			#p_muid_tab_storefront .combobox {
				position: relative;
			}
			#p_muid_tab_storefront .combobox input {
				padding-right: 32px;
			}
			#p_muid_tab_storefront .combobox a {
				display: block;
				position: absolute;
				right: 8px;
				top: 50%;
				margin-top: -8px;
			}
		</style>
		<script type="text/javascript">
			pines(function(){
				var category_grid = $("#p_muid_category_grid");
				var show_specs = function(){
					$("div.spec", "#p_muid_tab_storefront").hide();
					category_grid.find(":checkbox:checked").each(function(){
						var guid = $(this).val();
						var cur_spec;
						do {
							cur_spec = $("#p_muid_specs_"+guid).show();
							guid = cur_spec.children("div.parent").text();
						} while (cur_spec.length);
					});
				};
				category_grid.on("change", ":checkbox", show_specs);
				show_specs();

				$(".combobox", "#p_muid_tab_storefront").each(function(){
					var box = $(this);
					var autobox = box.children("input").autocomplete({
						minLength: 0,
						source: $.map(box.children("select").children(), function(elem){
							return $(elem).attr("value");
						})
					});
					box.children("a").hover(function(){
						$(this).addClass("ui-icon-circle-triangle-s").removeClass("ui-icon-triangle-1-s");
					}, function(){
						$(this).addClass("ui-icon-triangle-1-s").removeClass("ui-icon-circle-triangle-s");
					}).click(function(){
						autobox.focus().autocomplete("search", "");
					});
				});

				$("#p_muid_featured_image").change(function(){
					$("#p_muid_featured_image_preview").attr("src", $(this).val());
				});
			});
		</script>
		<div class="tab-pane" id="p_muid_tab_storefront">
			<div class="pf-element">
				<label><span class="pf-label">Shown in Storefront</span>
					<input class="pf-field" type="checkbox" name="show_in_storefront" value="ON"<?php echo $this->entity->show_in_storefront ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					pines(function(){
						var alias = $("#p_muid_form [name=alias]");
						$("#p_muid_form [name=name]").change(function(){
							if (alias.val() == "")
								alias.val($(this).val().replace(/[^\w\d\s\-.]/g, '').replace(/\s/g, '-').toLowerCase());
						}).blur(function(){
							$(this).change();
						}).focus(function(){
							if (alias.val() == $(this).val().replace(/[^\w\d\s\-.]/g, '').replace(/\s/g, '-').toLowerCase())
								alias.val("");
						});
					});
				</script>
				<label>
					<span class="pf-label">Alias</span>
					<span class="pf-group pf-full-width">
						<span class="pf-field" style="display: block;">
							<input style="width: 100%;" type="text" name="alias" value="<?php echo htmlspecialchars($this->entity->alias); ?>" onkeyup="this.value=this.value.replace(/[^\w\d-.]/g, '_');" />
						</span>
					</span>
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Featured Item</span>
					<input class="pf-field" type="checkbox" name="featured" value="ON"<?php echo $this->entity->featured ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Featured Image</span>
					<input class="pf-field puploader" id="p_muid_featured_image" type="text" name="featured_image" value="<?php echo htmlspecialchars($this->entity->featured_image); ?>" /></label>
			</div>
			<div class="pf-element">
				<span class="pf-label">Featured Image Preview</span>
				<div class="pf-group">
					<img class="pf-field" alt="Featured Image Preview" id="p_muid_featured_image_preview" src="<?php echo htmlspecialchars($this->entity->featured_image); ?>" />
				</div>
			</div>
			<fieldset class="pf-group">
				<legend>Category Specs</legend>
				<?php foreach($this->categories as $cur_category) { ?>
				<div class="spec" id="p_muid_specs_<?php echo htmlspecialchars($cur_category->guid); ?>"<?php echo in_array($cur_category->guid, $category_guids) ? '' : ' style="display: none;"'; ?>>
					<?php if (isset($cur_category->parent)) { ?>
					<div class="parent" style="display: none;"><?php echo htmlspecialchars($cur_category->parent->guid); ?></div>
					<?php } ?>
					<?php if (!empty($cur_category->specs)) { ?>
					<div class="pf-element pf-heading">
						<h3><?php echo htmlspecialchars($cur_category->name); ?></h3>
					</div>
					<?php foreach ($cur_category->specs as $key => $cur_spec) { ?>
					<div class="pf-element">
						<span class="pf-label<?php echo $cur_spec['type'] == 'heading' ? ' ui-priority-primary': ''; ?>"><?php echo htmlspecialchars($cur_spec['name']); ?></span>
						<?php
						switch ($cur_spec['type']) {
							case 'bool':
								?><input class="pf-field" type="checkbox" name="<?php echo htmlspecialchars($key); ?>" value="ON"<?php echo $this->entity->specs[$key] ? ' checked="checked"' : ''; ?> /><?php
								break;
							case 'string':
							case 'float':
								if (empty($cur_spec['options'])) {
									?><input class="pf-field" type="text" name="<?php echo htmlspecialchars($key); ?>" size="24" value="<?php echo htmlspecialchars($this->entity->specs[$key]); ?>" /><?php
								} else {
									if ($cur_spec['restricted']) {
										?><select class="pf-field" name="<?php echo htmlspecialchars($key); ?>">
											<?php foreach ($cur_spec['options'] as $cur_option) {
												?><option value="<?php echo htmlspecialchars($cur_option); ?>"<?php echo $this->entity->specs[$key] == $cur_option ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_option); ?></option><?php
											} ?>
										</select><?php
									} else {
										?><span class="combobox">
											<input class="pf-field" type="text" name="<?php echo htmlspecialchars($key); ?>" size="24" value="<?php echo htmlspecialchars($this->entity->specs[$key]); ?>" />
											<a href="javascript:void(0);" class="ui-icon ui-icon-triangle-1-s"></a>
											<select style="display: none;">
												<?php foreach ($cur_spec['options'] as $cur_option) {
													?><option value="<?php echo htmlspecialchars($cur_option); ?>"><?php echo htmlspecialchars($cur_option); ?></option><?php
												} ?>
											</select>
										</span><?php
									}
								}
								break;
							default:
								break;
						}
						?>
					</div>
					<?php } } ?>
				</div>
				<?php } ?>
				<div class="pf-element">
					Add this product to any category with specs to see them here.
				</div>
			</fieldset>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_head">
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					pines(function(){
						$("#p_muid_use_name").change(function(){
							if ($(this).is(":checked"))
								$("#p_muid_title").attr("disabled", "disabled");
							else
								$("#p_muid_title").removeAttr("disabled");
						}).change();
					});
				</script>
				<span class="pf-label">Page Title</span>
				<div class="pf-group pf-full-width">
					<label><input class="pf-field" type="checkbox" id="p_muid_use_name" name="title_use_name" value="ON"<?php echo $this->entity->title_use_name ? ' checked="checked"' : ''; ?> /> Use name as title.</label><br />
					<span class="pf-field" style="display: block;">
						<input style="width: 100%;" type="text" id="p_muid_title" name="title" value="<?php echo htmlspecialchars($this->entity->title); ?>" />
					</span>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Title Position</span>
					<select class="pf-field" name="title_position">
						<option value="prepend"<?php echo $this->entity->title_position === 'prepend' ? ' selected="selected"' : ''; ?>>Prepend to Site Title</option>
						<option value="append"<?php echo $this->entity->title_position === 'append' ? ' selected="selected"' : ''; ?>>Append to Site Title</option>
						<option value="replace"<?php echo $this->entity->title_position === 'replace' ? ' selected="selected"' : ''; ?>>Replace Site Title</option>
					</select></label>
			</div>
			<div class="pf-element pf-heading">
				<h3>Meta Tags</h3>
			</div>
			<script type="text/javascript">
				pines(function(){
					// Meta Tags
					var meta_tags = $("#p_muid_form [name=meta_tags]");
					var meta_tags_table = $("#p_muid_form .meta_tags_table");
					var meta_tag_dialog = $("#p_muid_form .meta_tag_dialog");
					var cur_meta_tag = null;

					meta_tags_table.pgrid({
						pgrid_paginate: false,
						pgrid_toolbar: true,
						pgrid_toolbar_contents : [
							{
								type: 'button',
								text: 'Add Meta Tag',
								extra_class: 'picon picon-document-new',
								selection_optional: true,
								click: function(){
									cur_meta_tag = null;
									meta_tag_dialog.dialog('open');
								}
							},
							{
								type: 'button',
								text: 'Edit Meta Tag',
								extra_class: 'picon picon-document-edit',
								double_click: true,
								click: function(e, rows){
									cur_meta_tag = rows;
									meta_tag_dialog.find("input[name=cur_meta_tag_name]").val(pines.unsafe(rows.pgrid_get_value(1)));
									meta_tag_dialog.find("input[name=cur_meta_tag_value]").val(pines.unsafe(rows.pgrid_get_value(2)));
									meta_tag_dialog.dialog('open');
								}
							},
							{
								type: 'button',
								text: 'Remove Meta Tag',
								extra_class: 'picon picon-edit-delete',
								click: function(e, rows){
									rows.pgrid_delete();
									update_meta_tags();
								}
							}
						],
						pgrid_view_height: "200px"
					});

					// Meta Tag Dialog
					meta_tag_dialog.dialog({
						bgiframe: true,
						autoOpen: false,
						modal: true,
						width: 500,
						buttons: {
							"Done": function(){
								var cur_meta_tag_name = meta_tag_dialog.find("input[name=cur_meta_tag_name]").val();
								var cur_meta_tag_value = meta_tag_dialog.find("input[name=cur_meta_tag_value]").val();
								if (cur_meta_tag_name == "") {
									alert("Please provide a name for this meta_tag.");
									return;
								}
								if (cur_meta_tag == null) {
									var new_meta_tag = [{
										key: null,
										values: [
											pines.safe(cur_meta_tag_name),
											pines.safe(cur_meta_tag_value)
										]
									}];
									meta_tags_table.pgrid_add(new_meta_tag);
								} else {
									cur_meta_tag.pgrid_set_value(1, pines.safe(cur_meta_tag_name));
									cur_meta_tag.pgrid_set_value(2, pines.safe(cur_meta_tag_value));
								}
								$(this).dialog('close');
							}
						},
						close: function(){
							update_meta_tags();
						}
					});

					var update_meta_tags = function(){
						meta_tag_dialog.find("input[name=cur_meta_tag_name]").val("");
						meta_tag_dialog.find("input[name=cur_meta_tag_value]").val("");
						meta_tags.val(JSON.stringify(meta_tags_table.pgrid_get_all_rows().pgrid_export_rows()));
					};

					update_meta_tags();

					meta_tag_dialog.find("input[name=cur_meta_tag_name]").autocomplete({
						"source": <?php echo (string) json_encode(array('description', 'author', 'keywords', 'robots', 'rating', 'distribution')); ?>
					});
				});
			</script>
			<div class="pf-element pf-full-width">
				<table class="meta_tags_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Content</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->meta_tags)) foreach ($this->entity->meta_tags as $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_value['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['content']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="meta_tags" />
			</div>
			<div class="meta_tag_dialog" style="display: none;" title="Add a Meta Tag">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Common Meta Tags</span>
						<span class="pf-note">These tags are commonly used on pages.</span>
						<div class="pf-group">
							<div class="pf-field"><em><?php
							$name_links = array();
							foreach (array('description', 'keywords', 'robots', 'rating', 'distribution') as $cur_name) {
								$name_html = htmlspecialchars($cur_name);
								$name_js = htmlspecialchars(json_encode($cur_name));
								$name_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_meta_tag_name').val($name_js);\">$name_html</a>";
							}
							echo implode(', ', $name_links);
							?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Name</span>
							<input class="pf-field" type="text" name="cur_meta_tag_name" id="p_muid_cur_meta_tag_name" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Content</span>
							<input class="pf-field" type="text" name="cur_meta_tag_value" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'product/list'))); ?>);" value="Cancel" />
	</div>
</form>