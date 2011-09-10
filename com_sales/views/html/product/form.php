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
$this->title = (!isset($this->entity->guid)) ? 'Editing New Product' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide product details in this form.';
$pines->editor->load();
$pines->uploader->load();
$pines->com_pgrid->load();
$pines->com_ptags->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
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
	}
	/* ]]> */
</style>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'product/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
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
							vendor_dialog.find("input[name=cur_vendor_sku]").val(cur_vendor.pgrid_get_value(2));
							vendor_dialog.find("input[name=cur_vendor_cost]").val(cur_vendor.pgrid_get_value(3));
							vendor_dialog.find("input[name=cur_vendor_link]").val(cur_vendor.pgrid_get_value(4).replace(/.*?>(.*)?<.*/i, '$1'));
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
						cur_vendor_link = '<a href="'+cur_vendor_link+'" onclick="window.open(this.href); return false;">'+cur_vendor_link+'</a>';
						if (cur_vendor == null) {
							var new_vendor = [{
								key: cur_vendor_entity[0].key,
								values: [
									cur_vendor_entity[0].values[0],
									cur_vendor_sku,
									cur_vendor_cost,
									cur_vendor_link
								]
							}];
							vendors_table.pgrid_add(new_vendor);
						} else {
							cur_vendor.attr('title', cur_vendor_entity[0].key);
							cur_vendor.pgrid_set_value(1, cur_vendor_entity[0].values[0]);
							cur_vendor.pgrid_set_value(2, cur_vendor_sku);
							cur_vendor.pgrid_set_value(3, cur_vendor_cost);
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

			$("#p_muid_product_tabs").tabs();
			update_vendors();
		});
		// ]]>
	</script>
	<div id="p_muid_product_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_categories">Categories</a></li>
			<li><a href="#p_muid_tab_images">Images</a></li>
			<li><a href="#p_muid_tab_purchasing">Purchasing</a></li>
			<li><a href="#p_muid_tab_pricing">Pricing</a></li>
			<li><a href="#p_muid_tab_attributes">Attributes</a></li>
			<?php if ($pines->config->com_sales->com_hrm) { ?>
			<li><a href="#p_muid_tab_commission">Commission</a></li>
			<?php } ?>
			<?php if ($pines->config->com_sales->com_storefront) { ?>
			<li><a href="#p_muid_tab_storefront">Storefront</a></li>
			<?php } ?>
		</ul>
		<div id="p_muid_tab_general">
			<div class="pf-element pf-full-width">
				<label>
					<span class="pf-label">Name</span>
					<span style="display: block;" class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="name" value="<?php echo htmlspecialchars($this->entity->name); ?>" />
					</span>
				</label>
			</div>
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Product SKU</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="sku" size="24" value="<?php echo htmlspecialchars($this->entity->sku); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Manufacturer</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="manufacturer">
						<option value="null">-- None --</option>
						<?php foreach ($this->manufacturers as $cur_manufacturer) { ?>
						<option value="<?php echo $cur_manufacturer->guid; ?>"<?php echo $this->entity->manufacturer->guid == $cur_manufacturer->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_manufacturer->name); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Manufacturer SKU</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="manufacturer_sku" size="24" value="<?php echo htmlspecialchars($this->entity->manufacturer_sku); ?>" /></label>
			</div>
			<div class="pf-element pf-full-width">
				<label><span class="pf-label">Receipt Description</span>
					<span class="pf-note">A short description to be shown on receipts.</span>
					<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="receipt_description"><?php echo $this->entity->receipt_description; ?></textarea></span></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Short Description</span><br />
				<textarea rows="3" cols="35" class="peditor-simple" style="width: 100%;" name="short_description"><?php echo $this->entity->short_description; ?></textarea>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="9" cols="35" class="peditor" style="width: 100%; height: 400px;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_categories">
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					// <![CDATA[
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
						});
					});
					// ]]>
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
						<tr title="<?php echo $cur_category->guid; ?>" class="<?php echo $cur_category->children ? 'parent ' : ''; ?><?php echo isset($cur_category->parent) ? "child ch_{$cur_category->parent->guid} " : ''; ?>">
							<td><?php echo isset($cur_category->parent) ? $cur_category->array_search($cur_category->parent->children) + 1 : '0' ; ?></td>
							<td><input type="checkbox" name="categories[]" value="<?php echo $cur_category->guid; ?>" <?php echo in_array($cur_category->guid, $category_guids) ? 'checked="checked" ' : ''; ?>/></td>
							<td><?php echo htmlspecialchars($cur_category->name); ?></td>
							<td><?php echo count($cur_category->products); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_images">
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					var last_image = "";

					var update_images = function(){
						var images = [];
						$("li", "#p_muid_sortable").each(function(){
							var cur_entry = $(this);
							images.push({
								"file": cur_entry.find("img").attr("src"),
								"alt": cur_entry.find("p").html()
							});
						});
						$("input[name=images]", "#p_muid_tab_images").val(JSON.stringify(images));
					};
					update_images();
					
					var add_image = function(image){
						last_image = image;
						$("<li class=\"ui-state-default ui-corner-all\"><img alt=\""+image.replace(/.*\//, '')+"\" src=\""+image+"\" /><p>Click to edit description...</p></li>").appendTo($("#p_muid_sortable"));
						update_images();
					};
					var auto_add = function(){
						if (last_image == "")
							return;
						var last_file = last_image.replace(/.*\//, "");
						var last_number = parseInt(last_file.match(/\d+/));
						if (isNaN(last_number)) {
							alert("Couldn't detect file pattern. Please name them as sequential numbers, like 1.jpg, 2.jpg, etc.");
							return;
						}
						var new_number = last_number + 1;
						var new_file = last_file.replace(/\d+/, new_number);
						var new_image = last_image.replace(last_file, new_file);
						if (last_image == new_image) {
							alert("Couldn't detect file pattern. Please name them as sequential numbers, like 1.jpg, 2.jpg, etc.");
							return;
						}
						$.ajax({
							type: "GET",
							url: new_image,
							error: function(){
								alert("No more files found.");
							},
							success: function(){
								add_image(new_image);
								auto_add();
							}
						});
					};

					$("#p_muid_image_upload").change(function(){
						add_image($(this).val());
						$(this).val("");
					});
					$("#p_muid_auto_add").click(function(){
						auto_add();
					});
					$("#p_muid_sortable")
					.delegate("li p", "click", function(){
						var cur_alt = $(this);
						var desc = cur_alt.text();
						$("<textarea cols=\"4\" rows=\"3\" style=\"width: 100%\" class=\"ui-widget-content ui-corner-all\">"+desc+"</textarea>")
						.blur(function(){
							cur_alt.insertAfter(this).html($(this).remove().val());
							update_images();
						})
						.insertAfter(cur_alt)
						.focus()
						.select();
						cur_alt.detach();
					})
					.sortable({
						placeholder: 'ui-state-highlight',
						update: function(){update_images();}
					})
					.draggable();
					$("#p_muid_image_trash").droppable({
						drop: function(e, ui){
							ui.draggable.hide("explode", {}, 500, function(){
								ui.draggable.remove();
								update_images();
							});
						}
					});
					//$("#p_muid_sortable").disableSelection();

					$("#p_muid_thumbnail").change(function(){
						$("#p_muid_thumbnail_preview").attr("src", $(this).val());
					});
				});
				// ]]>
			</script>
			<div class="pf-element">
				<span class="pf-label">Add an Image</span>
				<input class="pf-field ui-widget-content ui-corner-all puploader" id="p_muid_image_upload" type="text" value="" />
				<button class="ui-state-default ui-corner-all" type="button" id="p_muid_auto_add">Auto Add Last Directory</button>
			</div>
			<div class="pf-element">
				<span class="pf-label">Images</span>
				<span class="pf-note">The first image will be the default image.</span>
				<div class="pf-note">
					Drag image here to remove:
					<div class="ui-widget-content ui-corner-all" id="p_muid_image_trash" style="width: 32px; height: 32px; padding: 44px;">
						<div class="picon-32 picon-user-trash" style="width: 32px; height: 32px;"></div>
					</div>
				</div>
				<div class="pf-group">
					<ul id="p_muid_sortable" class="pf-field">
						<?php if ($this->entity->images) { foreach ($this->entity->images as $cur_image) { ?>
						<li class="ui-state-default ui-corner-all">
							<img alt="<?php echo htmlspecialchars(basename($cur_image['file'])); ?>" src="<?php echo htmlspecialchars($cur_image['file']); ?>" />
							<p><?php echo empty($cur_image['alt']) ? 'Click to edit description...' : htmlspecialchars(basename($cur_image['alt'])); ?></p>
						</li>
						<?php } } ?>
					</ul>
					<br class="pf-clearing" />
				</div>
			</div>
			<div class="pf-element">
				<span class="pf-label">Thumbnail</span>
				<input class="pf-field ui-widget-content ui-corner-all puploader" id="p_muid_thumbnail" type="text" name="thumbnail" value="<?php echo htmlspecialchars($this->entity->thumbnail); ?>" />
			</div>
			<div class="pf-element">
				<span class="pf-label">Thumbnail Preview</span>
				<div class="pf-group">
					<img class="pf-field" alt="Thumbnail Preview" id="p_muid_thumbnail_preview" src="<?php echo htmlspecialchars($this->entity->thumbnail); ?>" />
				</div>
			</div>
			<input type="hidden" name="images" />
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_purchasing">
			<div class="pf-element">
				<script type="text/javascript">
				// <![CDATA[
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
				// ]]>
				</script>
				<label><span class="pf-label">Stock Type</span>
					<span class="pf-note">Regular stock items cannot be sold without available stock. Stock optional items can be sold without available stock. Non stocked items do not use inventory tracking.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="stock_type">
						<?php foreach (array('regular_stock' => 'Regular Stock', 'stock_optional' => 'Stock Optional', 'non_stocked' => 'Non Stocked') as $cur_stock_key => $cur_stock_type) { ?>
						<option value="<?php echo htmlspecialchars($cur_stock_key); ?>"<?php echo $this->entity->stock_type == $cur_stock_key ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_stock_type); ?></option>
						<?php } ?>
					</select></label>
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
								<tr title="<?php echo $cur_vendor['entity']->guid; ?>">
									<td><?php echo htmlspecialchars($cur_vendor['entity']->name); ?></td>
									<td><?php echo htmlspecialchars($cur_vendor['sku']); ?></td>
									<td><?php echo htmlspecialchars($cur_vendor['cost']); ?></td>
									<td><a href="<?php echo htmlspecialchars($cur_vendor['link']); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_vendor['link']); ?></a></td>
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
							<tr title="<?php echo $cur_vendor->guid; ?>">
								<td><?php echo htmlspecialchars($cur_vendor->name); ?></td>
								<td><?php echo htmlspecialchars($cur_vendor->email); ?></td>
								<td><?php echo format_phone($cur_vendor->phone_work); ?></td>
								<td><?php echo format_phone($cur_vendor->fax); ?></td>
								<td><?php echo htmlspecialchars($cur_vendor->account_number); ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<br class="pf-clearing" />
					<div class="pf-element">
						<label><span class="pf-label">Vendor SKU</span>
							<input type="text" class="pf-field ui-widget-content ui-corner-all" name="cur_vendor_sku" size="15" id="p_muid_cur_vendor_sku" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Cost</span>
							<input type="text" class="pf-field ui-widget-content ui-corner-all" name="cur_vendor_cost" size="8" id="p_muid_cur_vendor_cost" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Link</span>
							<input type="url" class="pf-field ui-widget-content ui-corner-all" name="cur_vendor_link" size="20" id="p_muid_cur_vendor_link" /></label>
					</div>
				</div>
				<br />
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_pricing">
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var pricing_method = $("#p_muid_form [name=pricing_method]");
						var unit_price = $("#p_muid_form [name=unit_price]");
						var margin = $("#p_muid_form [name=margin]");
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
					<select class="pf-field ui-widget-content ui-corner-all" name="pricing_method">
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
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="unit_price" size="24" value="<?php echo htmlspecialchars($this->entity->unit_price); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Margin</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="margin" size="24" value="<?php echo htmlspecialchars($this->entity->margin); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Floor</span>
					<span class="pf-note">The lowest price allowed.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="floor" size="24" value="<?php echo htmlspecialchars($this->entity->floor); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Ceiling</span>
					<span class="pf-note">The highest price allowed.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="ceiling" size="24" value="<?php echo htmlspecialchars($this->entity->ceiling); ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Taxes/Fees</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Tax Exempt</span>
					<input class="pf-field" type="checkbox" name="tax_exempt" value="ON"<?php echo $this->entity->tax_exempt ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Additional Fees</span>
					<span class="pf-note">These fees will be applied in addition to the group's default taxes. If you select a fee/tax applied to a group, it will be applied twice to this product for that group.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="additional_tax_fees[]" size="6" multiple="multiple">
						<?php foreach ($this->tax_fees as $cur_tax_fee) { ?>
						<option value="<?php echo $cur_tax_fee->guid; ?>"<?php echo ($cur_tax_fee->in_array($this->entity->additional_tax_fees)) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_tax_fee->name); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Return Fee Checklists</span>
					<span class="pf-note">These checklists will be used to calculate additional restocking/return fees.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="return_checklists[]" size="6" multiple="multiple">
						<?php foreach ($this->return_checklists as $cur_return_checklist) { ?>
						<option value="<?php echo $cur_return_checklist->guid; ?>"<?php echo ($cur_return_checklist->in_array($this->entity->return_checklists)) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_return_checklist->name); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_attributes">
			<div class="pf-element">
				<label><span class="pf-label">Weight</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="weight" size="10" value="<?php echo htmlspecialchars($this->entity->weight); ?>" /> lbs.</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">RMA Available After</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="rma_after" size="10" value="<?php echo htmlspecialchars($this->entity->rma_after); ?>" /> days.</label>
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
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="additional_barcodes" size="24" value="<?php echo htmlspecialchars(implode(',', $this->entity->additional_barcodes)); ?>" />
					<script type="text/javascript">
						// <![CDATA[
						pines(function(){
							$("#p_muid_form [name=additional_barcodes]").ptags();
						});
						// ]]>
					</script>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Product Actions</span>
					<span class="pf-note">These actions will be executed when an event takes place with this product.</span>
					<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="actions[]" size="6" multiple="multiple">
						<?php foreach ($this->actions as $cur_action) { ?>
						<option value="<?php echo htmlspecialchars($cur_action['name']); ?>" title="<?php echo htmlspecialchars($cur_action['description']); ?>"<?php echo in_array($cur_action['name'], $this->entity->actions) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_action['cname']); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php if ($pines->config->com_sales->com_hrm) { ?>
		<script type="text/javascript">
			// <![CDATA[
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
								commission_dialog.find("select[name=cur_commission_group]").val(rows.pgrid_get_value(1));
								commission_dialog.find("select[name=cur_commission_type]").val(rows.pgrid_get_value(2));
								commission_dialog.find("input[name=cur_commission_amount]").val(rows.pgrid_get_value(3));
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
					width: 500,
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
										cur_commission_group,
										cur_commission_type,
										cur_commission_amount
									]
								}];
								commissions_table.pgrid_add(new_commission);
							} else {
								cur_commission.pgrid_set_value(1, cur_commission_group);
								cur_commission.pgrid_set_value(2, cur_commission_type);
								cur_commission.pgrid_set_value(3, cur_commission_amount);
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
			// ]]>
		</script>
		<div id="p_muid_tab_commission">
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
							<select class="pf-field ui-widget-content ui-corner-all" name="cur_commission_group">
								<?php foreach ($this->groups as $cur_group) {
								?><option value="<?php echo htmlspecialchars("{$cur_group->guid}: {$cur_group->name}"); ?>"><?php echo htmlspecialchars("{$cur_group->name} [{$cur_group->groupname}]"); ?></option><?php
								} ?>
							</select>
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Type</span>
							<select class="pf-field ui-widget-content ui-corner-all" name="cur_commission_type">
								<option value="spiff">Spiff (Fixed Amount)</option>
								<option value="percent_price">% Price (Before Tax)</option>
							</select>
						</label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Amount</span>
							<span class="pf-note">$ or %</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_commission_amount" size="24" onkeyup="this.value=this.value.replace(/[^\d.]/g, '');" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } if ($pines->config->com_sales->com_storefront) { ?>
		<style type="text/css">
			/* <![CDATA[ */
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
			/* ]]> */
		</style>
		<script type="text/javascript">
			// <![CDATA[
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
				category_grid.delegate(":checkbox", "change", function(){
					show_specs();
				});
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
			// ]]>
		</script>
		<div id="p_muid_tab_storefront">
			<div class="pf-element">
				<label><span class="pf-label">Shown in Storefront</span>
					<input class="pf-field" type="checkbox" name="show_in_storefront" value="ON"<?php echo $this->entity->show_in_storefront ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var alias = $("#p_muid_form [name=alias]");
						$("#p_muid_form [name=name]").change(function(){
							if (alias.val() == "")
								alias.val($(this).val().replace(/[^\w\d\s-.]/g, '').replace(/\s/g, '-').toLowerCase());
						}).blur(function(){
							$(this).change();
						}).focus(function(){
							if (alias.val() == $(this).val().replace(/[^\w\d\s-.]/g, '').replace(/\s/g, '-').toLowerCase())
								alias.val("");
						});
					});
					// ]]>
				</script>
				<label>
					<span class="pf-label">Alias</span>
					<span style="display: block;" class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="alias" value="<?php echo htmlspecialchars($this->entity->alias); ?>" onkeyup="this.value=this.value.replace(/[^\w\d-.]/g, '_');" />
					</span>
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Featured Item</span>
					<input class="pf-field" type="checkbox" name="featured" value="ON"<?php echo $this->entity->featured ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Featured Image</span>
					<input class="pf-field ui-widget-content ui-corner-all puploader" id="p_muid_featured_image" type="text" name="featured_image" value="<?php echo htmlspecialchars($this->entity->featured_image); ?>" /></label>
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
				<div class="spec" id="p_muid_specs_<?php echo $cur_category->guid; ?>"<?php echo in_array($cur_category->guid, $category_guids) ? '' : ' style="display: none;"'; ?>>
					<?php if (isset($cur_category->parent)) { ?>
					<div class="parent" style="display: none;"><?php echo $cur_category->parent->guid; ?></div>
					<?php } ?>
					<?php if (!empty($cur_category->specs)) { ?>
					<div class="pf-element pf-heading">
						<h1><?php echo htmlspecialchars($cur_category->name); ?></h1>
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
									?><input class="pf-field ui-widget-content ui-corner-all" type="text" name="<?php echo htmlspecialchars($key); ?>" size="24" value="<?php echo htmlspecialchars($this->entity->specs[$key]); ?>" /><?php
								} else {
									if ($cur_spec['restricted']) {
										?><select class="pf-field ui-widget-content ui-corner-all" name="<?php echo htmlspecialchars($key); ?>">
											<?php foreach ($cur_spec['options'] as $cur_option) {
												?><option value="<?php echo htmlspecialchars($cur_option); ?>"<?php echo $this->entity->specs[$key] == $cur_option ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_option); ?></option><?php
											} ?>
										</select><?php
									} else {
										?><span class="combobox">
											<input class="pf-field ui-widget-content ui-corner-all" type="text" name="<?php echo htmlspecialchars($key); ?>" size="24" value="<?php echo htmlspecialchars($this->entity->specs[$key]); ?>" />
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
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'product/list')); ?>');" value="Cancel" />
	</div>
</form>