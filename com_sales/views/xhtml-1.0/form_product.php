<?php
/**
 * Provides a form for the user to edit a product.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Product' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide product details in this form.';
?>
<form class="pform" method="post" id="product_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(document).ready(function(){

			$("#qualified_vendors_table").pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{type: 'button', text: 'Add Vendor', extra_class: 'icon picon_16x16_actions_list-add', selection_optional: true, click: function(){
							$('#vendor_dialog').dialog('open');
					}},
					{type: 'button', text: 'Remove Vendor', extra_class: 'icon picon_16x16_actions_list-remove', selection_optional: true, click: function(e, rows){
							//rows.remove();
					}}
				]
			});

			// Needs to be gridified before it's hidden.
			$("#available_vendors").pgrid({
				pgrid_multi_select: false,
				pgrid_paginate: false,
				pgrid_height: '400px;'
			});

			// Vendor Dialog
			$("#vendor_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					'Done': function() {
						$(this).dialog('close');
					}
				}
			});

			$("#product_tabs").tabs();
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
				<label><span class="label">Name</span>
					<input class="field" type="text" name="name" size="20" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Enabled</span>
					<input class="field" type="checkbox" name="enabled" size="20" value="ON"<?php echo ($this->entity->enabled || is_null($this->entity->enabled)) ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element">
				<label><span class="label">Product SKU</span>
					<input class="field" type="text" name="sku" size="20" value="<?php echo $this->entity->sku; ?>" /></label>
			</div>
			<div class="element">
				<span class="label">Categories</span>
				<script type="text/javascript">
					// <![CDATA[
					$(document).ready(function(){
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
							selected : <?php echo json_encode(array_map('strval', $config->run_sales->get_product_category_guid_array($this->entity))); ?>,
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
											alert("An error occurred trying to reach the server:\n"+textStatus);
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
											alert("An error occurred trying to reach the server:\n"+textStatus);
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
											alert("An error occurred trying to reach the server:\n"+textStatus);
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
											alert("An error occurred trying to reach the server:\n"+textStatus);
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
											alert("An error occurred trying to reach the server:\n"+textStatus);
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
				<button id="category_button" class="field ui-state-default ui-corner-all" type="button">Pick Categories</button>
				<input id="categories" class="field" type="hidden" name="categories" />
			</div>
			<div id="category_dialog" title="Categories">
				<div id="category_tree" style="border: 1px solid black; float: left; width: 100%;"></div>
				<p style="clear: left;"><a href="#" id="category_tree_new">New Root Category</a></p>
			</div>
			<div class="element full_width">
				<span class="label">Description</span><br />
				<textarea class="peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<div class="element full_width">
				<span class="label">Short Description</span><br />
				<textarea class="peditor_simple" style="width: 100%;" name="short_description"><?php echo $this->entity->short_description; ?></textarea>
			</div>
			<div class="element">
				<label><span class="label">Manufacturer</span>
					<select class="field" name="manufacturer">
						<option value="null">-- None --</option>
						<?php foreach ($this->manufacturers as $cur_manufacturer) { ?>
						<option value="<?php echo $cur_manufacturer->guid; ?>"<?php echo $this->entity->manufacturer == $cur_manufacturer->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_manufacturer->name; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="element">
				<label><span class="label">Manufacturer SKU</span>
					<input class="field" type="text" name="manufacturer_sku" size="20" value="<?php echo $this->entity->manufacturer_sku; ?>" /></label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_images">
			<div class="element">
				<label><span class="label">Upload a New Picture</span>
					<span class="note">Doesn't work yet.</span>
					<input class="field" type="file" name="image_upload" /></label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_purchasing">
			<div class="element heading">
				<p>This doesn't work yet.</p>
			</div>
			<div class="element full_width">
				<span class="label">Qualified Vendors</span>
				<div class="group">
					<table id="qualified_vendors_table">
						<thead>
							<tr>
								<th>Vendor</th>
								<th>Vendor SKU</th>
								<th>Cost</th>
							</tr>
						</thead>
						<tbody>
							<?php if (is_array($this->entity->vendors)) { foreach ($this->entity->vendors as $cur_vendor) { ?>
							<tr title="<?php echo $cur_vendor->guid; ?>">
								<td><?php echo $cur_vendor->name; ?></td>
								<td><?php echo $cur_vendor->sku; ?></td>
								<td><?php echo $cur_vendor->cost; ?></td>
							</tr>
								<?php } } ?>
						</tbody>
					</table>
					<input class="field" type="hidden" name="qualified_vendors" size="20" />
				</div>
			</div>
			<div id="vendor_dialog" title="Add a Vendor">
				<table id="available_vendors">
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
							<td><?php echo $cur_vendor->phone_work; ?></td>
							<td><?php echo $cur_vendor->fax; ?></td>
							<td><?php echo $cur_vendor->account_number; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_pricing">
			<div class="element">
				<label><span class="label">Pricing Method</span>
					<select class="field" name="pricing_method">
						<option value="fixed"<?php echo $this->entity->type == 'fixed' ? ' selected="selected"' : ''; ?>>Fixed Pricing</option>
						<option value="margin"<?php echo $this->entity->type == 'margin' ? ' selected="selected"' : ''; ?>>Margin Pricing</option>
					</select></label>
				<script type="text/javascript">
					// <![CDATA[
					$(document).ready(function(){
						$("#product_details [name=pricing_method]").change(function(){
							if ($(this).val() == "fixed") {
								$("#product_details [name=margin]").attr('disabled', 'disabled');
								$("#product_details [name=unit_price]").removeAttr('disabled');
							} else {
								$("#product_details [name=unit_price]").attr('disabled', 'disabled');
								$("#product_details [name=margin]").removeAttr('disabled');
							}
						});
						$("#product_details [name=pricing_method]").change();
					});
					// ]]>
				</script>
			</div>
			<div class="element heading">
				<h1>Defaults</h1>
			</div>
			<div class="element">
				<label><span class="label">Unit Price</span>
					<input class="field" type="text" name="unit_price" size="20" value="<?php echo $this->entity->unit_price; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Margin</span>
					<input class="field" type="text" name="margin" size="20" value="<?php echo $this->entity->margin; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Floor</span>
					<input class="field" type="text" name="floor" size="20" value="<?php echo $this->entity->floor; ?>" /></label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_attributes">
			<div class="element">
				<label><span class="label">Weight</span>
					<input class="field" type="text" name="weight" size="10" value="<?php echo $this->entity->weight; ?>" /> lbs.</label>
			</div>
			<div class="element">
				<label><span class="label">RMA Available After</span>
					<input class="field" type="text" name="rma_after" size="10" value="<?php echo $this->entity->rma_after; ?>" /> days.</label>
			</div>
			<div class="element">
				<label><span class="label">Discountable</span>
					<input class="field" type="checkbox" name="discountable" size="20" value="ON"<?php echo ($this->entity->discountable || is_null($this->entity->discountable)) ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element">
				<label><span class="label">Hide on Invoice</span>
					<input class="field" type="checkbox" name="hide_on_invoice" size="20" value="ON"<?php echo $this->entity->hide_on_invoice ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element">
				<label><span class="label">Non-Refundable</span>
					<input class="field" type="checkbox" name="non_refundable" size="20" value="ON"<?php echo $this->entity->non_refundable ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element full_width">
				<span class="label">Additional Barcodes</span>
				<div class="group">
					<input class="field" type="text" name="additional_barcodes" size="20" value="<?php echo (is_array($this->entity->additional_barcodes) ? implode(',', $this->entity->additional_barcodes) : ''); ?>" />
					<script type="text/javascript">
						// <![CDATA[
						$(document).ready(function(){
							$("#product_details [name=additional_barcodes]").tagEditor({completeOnBlur: true});
						});
						// ]]>
					</script>
				</div>
			</div>
			<div class="element">
				<label><span class="label">Additional Taxes/Fees</span>
					<span class="note">These taxes will be applied in addition to the group's default taxes. If you select a tax applied to a group, it will be applied twice to this product for that group.</span>
					<span class="note">Hold Ctrl (Command on Mac) to select multiple.</span>
					<select class="field" name="additional_tax_fees[]" size="6" multiple="multiple">
						<?php foreach ($this->tax_fees as $cur_tax_fee) { ?>
						<option value="<?php echo $cur_tax_fee->guid; ?>"<?php echo (is_array($this->entity->additional_tax_fees) && in_array($cur_tax_fee->guid, $this->entity->additional_tax_fees)) ? ' selected="selected"' : ''; ?>><?php echo $cur_tax_fee->name; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_accounting">
			<div class="element">
				<label><span class="label">Nothing here yet...</span></label>
			</div>
			<br class="spacer" />
		</div>
	</div>
	<br />
	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listproducts'); ?>';" value="Cancel" />
	</div>
</form>