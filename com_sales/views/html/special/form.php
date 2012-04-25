<?php
/**
 * Provides a form for the user to edit a special.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Special' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide special details in this form.';
$pines->uploader->load();
$pines->com_pgrid->load();
if ($pines->config->com_sales->com_customer)
	$pines->com_customer->load_customer_select();
$pines->com_sales->load_product_select();

//$categories = $pines->entity_manager->get_entities(
//		array('class' => com_sales_category),
//		array('&',
//			'tag' => array('com_sales', 'category'),
//			'strict' => array('enabled', true)
//		)
//	);
//$pines->entity_manager->hsort($categories, 'name', 'parent');

$specials = $pines->entity_manager->get_entities(
		array('class' => com_sales_special),
		array('&',
			'tag' => array('com_sales', 'special')
		),
		array('!&',
			'guid' => array($this->entity->guid)
		)
	);
$pines->entity_manager->sort($specials, 'name');
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'special/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			$(".p_muid_product_select", "#p_muid_form").productselect();
			$(".p_muid_date_select", "#p_muid_form").datepicker({
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true
			});

			// Discounts
			var discounts = $("#p_muid_form [name=discounts]");
			var discounts_table = $("#p_muid_form .discounts_table");
			var discount_dialog = $("#p_muid_form .discount_dialog");
			var cur_discount = null;

			discounts_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Discount',
						extra_class: 'picon picon-document-new',
						selection_optional: true,
						click: function(){
							cur_discount = null;
							discount_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit Discount',
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							cur_discount = rows;
							discount_dialog.find("select[name=cur_discount_type]").val(pines.unsafe(rows.pgrid_get_value(1))).change();
							discount_dialog.find("input[name=cur_discount_value]").val(pines.unsafe(rows.pgrid_get_value(3))).change();
							discount_dialog.dialog('open');
							// This has to come after the dialog opens.
							discount_dialog.find("#p_muid_dis_forms .dis_form:visible :input").val(pines.unsafe(rows.pgrid_get_value(2)));
						}
					},
					{
						type: 'button',
						text: 'Remove Discount',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_discounts();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// Discount Dialog
			discount_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_discount_type = discount_dialog.find("select[name=cur_discount_type]").val();
						var cur_discount_qualifier = discount_dialog.find("#p_muid_dis_forms .dis_form:visible :input").val();
						var cur_discount_value = parseFloat(discount_dialog.find("input[name=cur_discount_value]").val());
						if (cur_discount_type == "") {
							alert("Please provide a type for this discount.");
							return;
						}
						if (isNaN(cur_discount_value)) {
							alert("Please provide a value for this discount.");
							return;
						}
						if (cur_discount == null) {
							var new_discount = [{
								key: null,
								values: [
									pines.safe(cur_discount_type),
									pines.safe(cur_discount_qualifier),
									pines.safe(cur_discount_value)
								]
							}];
							discounts_table.pgrid_add(new_discount);
						} else {
							cur_discount.pgrid_set_value(1, pines.safe(cur_discount_type));
							cur_discount.pgrid_set_value(2, pines.safe(cur_discount_qualifier));
							cur_discount.pgrid_set_value(3, pines.safe(cur_discount_value));
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_discounts();
				}
			});

			$("#p_muid_cur_discount_type").change(function(){
				$("#p_muid_dis_forms > .dis_form").hide().filter("."+$(this).val()).show();
			}).change();

			var update_discounts = function(){
				discount_dialog.find("select[name=cur_discount_type]").val("order_amount").change();
				discount_dialog.find("input[name=cur_discount_value]").val("0.00");
				discount_dialog.find("#p_muid_dis_forms :input").val("");
				discounts.val(JSON.stringify(discounts_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_discounts();


			// Conditions
			var conditions = $("#p_muid_form [name=conditions]");
			var conditions_table = $("#p_muid_form .conditions_table");
			var condition_dialog = $("#p_muid_form .condition_dialog");
			var cur_condition = null;

			conditions_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Condition',
						extra_class: 'picon picon-document-new',
						selection_optional: true,
						click: function(){
							cur_condition = null;
							condition_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit Condition',
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							cur_condition = rows;
							condition_dialog.find("input[name=cur_condition_type]").val(pines.unsafe(rows.pgrid_get_value(1)));
							condition_dialog.find("input[name=cur_condition_value]").val(pines.unsafe(rows.pgrid_get_value(2)));
							condition_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Condition',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_conditions();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// Condition Dialog
			condition_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val();
						var cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
						if (cur_condition_type == "") {
							alert("Please provide a type for this condition.");
							return;
						}
						if (cur_condition == null) {
							// Is this a duplicate type?
							var dupe = false;
							conditions_table.pgrid_get_all_rows().each(function(){
								if (dupe) return;
								if ($(this).pgrid_get_value(1) == cur_condition_type)
									dupe = true;
							});
							if (dupe) {
								pines.notice('There is already a condition of that type.');
								return;
							}
							var new_condition = [{
								key: null,
								values: [
									pines.safe(cur_condition_type),
									pines.safe(cur_condition_value)
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, pines.safe(cur_condition_type));
							cur_condition.pgrid_set_value(2, pines.safe(cur_condition_value));
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_conditions();
				}
			});

			var update_conditions = function(){
				condition_dialog.find("input[name=cur_condition_type]").val("");
				condition_dialog.find("input[name=cur_condition_value]").val("");
				conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_conditions();

			condition_dialog.find("input[name=cur_condition_type]").autocomplete({
				"source": <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>
			});


			// Requirements
			var requirements = $("#p_muid_form [name=requirements]");
			var requirements_table = $("#p_muid_form .requirements_table");
			var requirement_dialog = $("#p_muid_form .requirement_dialog");
			var cur_requirement = null;

			requirements_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Requirement',
						extra_class: 'picon picon-document-new',
						selection_optional: true,
						click: function(){
							cur_requirement = null;
							requirement_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit Requirement',
						extra_class: 'picon picon-document-edit',
						double_click: true,
						click: function(e, rows){
							cur_requirement = rows;
							requirement_dialog.find("select[name=cur_requirement_type]").val(pines.unsafe(rows.pgrid_get_value(1))).change();
							requirement_dialog.dialog('open');
							// This has to come after the dialog opens.
							requirement_dialog.find("#p_muid_req_forms .req_form:visible :input").val(pines.unsafe(rows.pgrid_get_value(2)));
						}
					},
					{
						type: 'button',
						text: 'Remove Requirement',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_requirements();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// Requirement Dialog
			requirement_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_requirement_type = requirement_dialog.find("select[name=cur_requirement_type]").val();
						var cur_requirement_value = requirement_dialog.find("#p_muid_req_forms .req_form:visible :input").val();
						if (cur_requirement_type == "") {
							alert("Please provide a type for this requirement.");
							return;
						}
						if (cur_requirement == null) {
							var new_requirement = [{
								key: null,
								values: [
									pines.safe(cur_requirement_type),
									pines.safe(cur_requirement_value)
								]
							}];
							requirements_table.pgrid_add(new_requirement);
						} else {
							cur_requirement.pgrid_set_value(1, pines.safe(cur_requirement_type));
							cur_requirement.pgrid_set_value(2, pines.safe(cur_requirement_value));
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_requirements();
				}
			});

			$("#p_muid_cur_requirement_type").change(function(){
				$("#p_muid_req_forms > .req_form").hide().filter("."+$(this).val()).show();
			}).change();

			var update_requirements = function(){
				requirement_dialog.find("select[name=cur_requirement_type]").val("subtotal_eq").change();
				requirement_dialog.find("#p_muid_req_forms :input").val("");
				requirements.val(JSON.stringify(requirements_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_requirements();
		});
	</script>
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_general" data-toggle="tab">General</a></li>
		<li><a href="#p_muid_tab_conditions" data-toggle="tab">Conditions</a></li>
		<li><a href="#p_muid_tab_requirements" data-toggle="tab">Requirements</a></li>
	</ul>
	<div id="p_muid_special_tabs" class="tab-content">
		<div class="tab-pane active" id="p_muid_tab_general">
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
				<label><span class="pf-label">Code</span>
					<span class="pf-note">This code can be used to add the special to a sale. It is not case sensitive.</span>
					<input class="pf-field" type="text" name="code" size="24" value="<?php echo htmlspecialchars($this->entity->code); ?>" onchange="this.value=this.value.toUpperCase();" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Before Tax</span>
					<span class="pf-note">Apply this special before tax.</span>
					<span class="pf-note">Flat taxes still apply to all items on the sale.</span>
					<input class="pf-field" type="checkbox" name="before_tax" value="ON"<?php echo $this->entity->before_tax ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Apply to All Eligible Sales</span>
					<input class="pf-field" type="checkbox" name="apply_to_all" value="ON"<?php echo $this->entity->apply_to_all ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Hide Code on Receipt</span>
					<input class="pf-field" type="checkbox" name="hide_code" value="ON"<?php echo $this->entity->hide_code ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Quantity per Ticket</span>
					<span class="pf-note">Enter 0 (zero) for unlimited.</span>
					<input class="pf-field" type="text" name="per_ticket" size="24" value="<?php echo htmlspecialchars($this->entity->per_ticket); ?>" onchange="this.value=this.value.replace(/\D/g, '');" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h3>Discounts</h3>
				<p>These discounts will be applied for this special.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="discounts_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Qualifier</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->discounts)) foreach ($this->entity->discounts as $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_value['type']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['qualifier']->sku); ?></td>
							<td><?php echo htmlspecialchars($cur_value['value']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="discounts" />
			</div>
			<div class="discount_dialog" style="display: none;" title="Add a Discount">
				<div class="pf-form">
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<select class="pf-field" name="cur_discount_type" id="p_muid_cur_discount_type">
								<option value="order_amount">Whole Order (Amount)</option>
								<option value="order_percent">Whole Order (Percent)</option>
								<option value="product_amount">Specific Product (Amount) (x Qty)</option>
								<option value="product_percent">Specific Product (Percent)</option>
								<option value="item_amount">One Product (Amount)</option>
								<option value="item_percent">One Product (Percent)</option>
								<?php /* <option value="category_amount">Products from Category (Amount) (x Qty)</option>
								<option value="category_percent">Products from Category (Percent)</option> */ ?>
							</select></label>
					</div>
					<div id="p_muid_dis_forms">
						<div class="dis_form product_amount product_percent item_amount item_percent">
							<div class="pf-element">
								<label><span class="pf-label">Product</span>
									<input class="pf-field p_muid_product_select" type="text" size="24" /></label>
							</div>
						</div>
						<?php /* <div class="dis_form category_amount category_percent">
							<div class="pf-element">
								<label><span class="pf-label">Category</span>
									<select class="pf-field">
										<?php foreach ($categories as $cur_cat) {
											$num_parents = 0;
											$cur_parent = $cur_cat->parent;
											while (isset($cur_parent->guid)) {
												$num_parents++;
												$cur_parent = $cur_parent->parent;
											}
											?>
										<option value="<?php echo htmlspecialchars($cur_cat->guid); ?>"><?php echo htmlspecialchars(str_repeat('->', $num_parents).' '.$cur_cat->name); ?></option>
										<?php } ?>
									</select></label>
							</div>
						</div> */ ?>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field" name="cur_discount_value" type="text" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h3>Special Conditions</h3>
				<p>The special will only be applied if these conditions are met.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="conditions_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->conditions)) foreach ($this->entity->conditions as $cur_key => $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_key); ?></td>
							<td><?php echo htmlspecialchars($cur_value); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="conditions" />
			</div>
			<div class="condition_dialog" style="display: none;" title="Add a Condition">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Detected Types</span>
						<span class="pf-note">These types were detected on this system.</span>
						<div class="pf-group">
							<div class="pf-field"><em><?php
							$checker_links = array();
							foreach (array_keys($pines->depend->checkers) as $cur_checker) {
								$checker_html = htmlspecialchars($cur_checker);
								$checker_js = htmlspecialchars(json_encode($cur_checker));
								$checker_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val($checker_js);\">$checker_html</a>";
							}
							echo implode(', ', $checker_links);
							?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<input class="pf-field" type="text" name="cur_condition_type" id="p_muid_cur_condition_type" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field" type="text" name="cur_condition_value" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_requirements">
			<div class="pf-element pf-heading">
				<h3>Special Requirements</h3>
				<p>The special will only be applied if these requirements are met.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="requirements_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->requirements)) foreach ($this->entity->requirements as $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_value['type']); ?></td>
							<td><?php
							switch ($cur_value['type']) {
								case 'has_product':
								case 'has_not_product':
									echo htmlspecialchars($cur_value['value']->sku);
									break;
								/* case 'has_category':
								case 'has_not_category':
									echo htmlspecialchars($cur_value['value']->guid);
									break; */
								case 'has_special':
								case 'has_not_special':
									echo ($cur_value['value'] === 'any') ? 'any' : htmlspecialchars($cur_value['value']->guid);
									break;
								case 'date_lt':
								case 'date_gt':
									echo htmlspecialchars(format_date($cur_value['value'], 'custom', 'Y-m-d'));
								default:
									echo htmlspecialchars($cur_value['value']);
									break;
							}
							?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="requirements" />
			</div>
			<div class="requirement_dialog" style="display: none;" title="Add a Requirement">
				<div class="pf-form">
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<select class="pf-field" name="cur_requirement_type" id="p_muid_cur_requirement_type">
								<option value="subtotal_eq">Sale Subtotal Equals</option>
								<option value="subtotal_lt">Sale Subtotal Less Than</option>
								<option value="subtotal_gt">Sale Subtotal Greater Than</option>
								<option value="has_product">Sale Has Product</option>
								<option value="has_not_product">Sale Doesn't Have Product</option>
								<?php /* <option value="has_category">Sale Has Category</option>
								<option value="has_not_category">Sale Doesn't Have Category</option> */ ?>
								<option value="has_special">Sale Has Special</option>
								<option value="has_not_special">Sale Doesn't Have Special</option>
								<option value="date_lt">Before Date</option>
								<option value="date_gt">After Date</option>
							</select></label>
					</div>
					<div id="p_muid_req_forms">
						<div class="req_form subtotal_eq subtotal_gt subtotal_lt">
							<div class="pf-element">
								<label><span class="pf-label">Value</span>
									<span class="pf-field">$<input type="text" size="24" /></span></label>
							</div>
						</div>
						<div class="req_form has_product has_not_product">
							<div class="pf-element">
								<label><span class="pf-label">Product</span>
									<input class="pf-field p_muid_product_select" type="text" size="24" /></label>
							</div>
						</div>
						<?php /* <div class="req_form has_category has_not_category">
							<div class="pf-element">
								<label><span class="pf-label">Category</span>
									<select class="pf-field">
										<?php foreach ($categories as $cur_cat) {
											$num_parents = 0;
											$cur_parent = $cur_cat->parent;
											while (isset($cur_parent->guid)) {
												$num_parents++;
												$cur_parent = $cur_parent->parent;
											}
											?>
										<option value="<?php echo htmlspecialchars($cur_cat->guid); ?>"><?php echo htmlspecialchars(str_repeat('->', $num_parents).' '.$cur_cat->name); ?></option>
										<?php } ?>
									</select></label>
							</div>
						</div> */ ?>
						<div class="req_form has_special has_not_special">
							<div class="pf-element">
								<label><span class="pf-label">Special</span>
									<select class="pf-field">
										<option value="any">-- Any Other Special --</option>
										<?php foreach ($specials as $cur_special) { ?>
										<option value="<?php echo htmlspecialchars($cur_special->guid); ?>"><?php echo htmlspecialchars($cur_special->name); ?></option>
										<?php } ?>
									</select></label>
							</div>
						</div>
						<div class="req_form date_lt date_gt">
							<div class="pf-element">
								<label><span class="pf-label">Date</span>
									<span class="pf-label">Before and after date use the time of midnight on their dates in <strong>your</strong> timezone.</span>
									<input class="pf-field p_muid_date_select" type="text" size="24" /></label>
							</div>
							<div class="pf-element">
								Because the time used is midnight, after date is inclusive of the date you select, and before date is not inclusive. So to include only all of January, pick Jan 1st to Feb 1st.
							</div>
						</div>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'special/list'))); ?>);" value="Cancel" />
	</div>
</form>