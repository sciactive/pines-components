<?php
/**
 * Provides a form for the user to edit a category.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Category' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide category details in this form.';
if ($pines->config->com_sales->com_storefront) {
	$pines->com_pgrid->load();
	$pines->com_ptags->load();
	$pines->editor->load();
}
?>
<?php if ($pines->config->com_sales->com_storefront) { ?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_spec_options").ptags({ptags_delimiter: ';;', ptags_sortable: null});
		$("#p_muid_spec_do_sort").click(function(){
			var tags_elem = $("#p_muid_spec_options");
			var tags = tags_elem.val().split(';;');
			// Natural Sort http://my.opera.com/GreyWyvern/blog/show.dml/1671288
			for (var z = 0, t; t = tags[z]; z++) {
				tags[z] = [];
				var x = 0, y = -1, n = 0, i, j;
				while (i = (j = t.charAt(x++)).charCodeAt(0)) {
					var m = (i == 46 || (i >=48 && i <= 57));
					if (m !== n) {
						tags[z][++y] = "";
						n = m;
					}
					tags[z][y] += j;
				}
			}
			tags.sort(function(a, b) {
				for (var x = 0, aa, bb; (aa = a[x]) && (bb = b[x]); x++) {
					aa = aa.toLowerCase();
					bb = bb.toLowerCase();
					if (aa !== bb) {
						var c = Number(aa), d = Number(bb);
						if (c == aa && d == bb)
							return c - d;
						else return (aa > bb) ? 1 : -1;
					}
				}
				return a.length - b.length;
			});
			for (var z = 0; z < tags.length; z++)
				tags[z] = tags[z].join("");
			tags_elem.val(tags.join(';;'));
		});
		
		// Specs
		var specs = $("input[name=specs]", "#p_muid_form");
		var specs_table = $("#p_muid_specs_table");
		var spec_dialog = $("#p_muid_spec_dialog");
		var cur_spec = null;

		specs_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Spec',
					extra_class: 'picon picon-document-new',
					selection_optional: true,
					click: function(){
						cur_spec = null;
						spec_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Edit Spec',
					extra_class: 'picon picon-document-edit',
					double_click: true,
					click: function(e, rows){
						if (rows.hasClass("ui-state-disabled")) {
							alert("That row is inherited from another category. It can't be edited here.");
							return;
						}
						cur_spec = rows;
						spec_dialog.find("input[name=cur_spec_order]").val(rows.pgrid_get_value(1));
						spec_dialog.find("input[name=cur_spec_name]").val(rows.pgrid_get_value(2));
						spec_dialog.find("select[name=cur_spec_type]").val(rows.pgrid_get_value(3)).change();
						spec_dialog.find("input[name=cur_spec_show_filter]").attr("checked", rows.pgrid_get_value(4) == "Yes");
						spec_dialog.find("input[name=cur_spec_restricted]").attr("checked", rows.pgrid_get_value(5) == "Yes");
						spec_dialog.find("input[name=cur_spec_options]").val(rows.pgrid_get_value(6));
						spec_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Spec',
					extra_class: 'picon picon-edit-delete',
					click: function(e, rows){
						if (rows.hasClass("ui-state-disabled")) {
							alert("That row is inherited from another category. It can't be removed here.");
							return;
						}
						rows.pgrid_delete();
						update_specs();
					}
				}
			],
			pgrid_view_height: "300px"
		});

		// Spec Dialog
		spec_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			buttons: {
				"Done": function(){
					var cur_spec_order = spec_dialog.find("input[name=cur_spec_order]").val();
					var cur_spec_name = spec_dialog.find("input[name=cur_spec_name]").val();
					var cur_spec_type = spec_dialog.find("select[name=cur_spec_type]").val();
					var cur_spec_show_filter = !!spec_dialog.find("input[name=cur_spec_show_filter]").attr("checked");
					var cur_spec_restricted = !!spec_dialog.find("input[name=cur_spec_restricted]").attr("checked");
					var cur_spec_options = spec_dialog.find("input[name=cur_spec_options]").val();
					if (cur_spec_name == "" || cur_spec_type == "") {
						alert("Please provide both a name and a type for this spec.");
						return;
					}
					if (cur_spec == null) {
						var new_spec = [{
							key: null,
							values: [
								cur_spec_order,
								cur_spec_name,
								cur_spec_type,
								(cur_spec_type == "heading") ? '' : (cur_spec_show_filter ? 'Yes' : 'No'),
								(cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : (cur_spec_restricted ? 'Yes' : 'No'),
								(cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : cur_spec_options
							]
						}];
						specs_table.pgrid_add(new_spec);
					} else {
						cur_spec.pgrid_set_value(1, cur_spec_order);
						cur_spec.pgrid_set_value(2, cur_spec_name);
						cur_spec.pgrid_set_value(3, cur_spec_type);
						cur_spec.pgrid_set_value(4, (cur_spec_type == "heading") ? '' : (cur_spec_show_filter ? 'Yes' : 'No'));
						cur_spec.pgrid_set_value(5, (cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : (cur_spec_restricted ? 'Yes' : 'No'));
						cur_spec.pgrid_set_value(6, (cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : cur_spec_options);
					}
					$(this).dialog('close');
				}
			},
			close: function(){
				update_specs();
			}
		});

		var update_specs = function(){
			spec_dialog.find("input[name=cur_spec_order]").val("");
			spec_dialog.find("input[name=cur_spec_name]").val("");
			spec_dialog.find("select[name=cur_spec_type]").val("").change();
			spec_dialog.find("input[name=cur_spec_show_filter]").attr("checked", false);
			spec_dialog.find("input[name=cur_spec_restricted]").attr("checked", false);
			spec_dialog.find("input[name=cur_spec_options]").val("");
			specs.val(JSON.stringify(specs_table.pgrid_get_all_rows().filter(":not(.ui-state-disabled)").pgrid_export_rows()));
		};

		update_specs();

		$("#p_muid_menu_position").autocomplete({
			source: <?php echo json_encode($pines->info->template->positions); ?>
		});
	});
	// ]]>
</script>
<?php } ?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'category/save')); ?>">
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
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Parent</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="parent">
				<option value="null">-- No Parent --</option>
				<?php
				/**
				 * Print children of a category into the select box.
				 * @param com_sales_category $parent The parent category.
				 * @param com_sales_category|null $entity The current category.
				 * @param string $prefix The prefix to insert before names.
				 */
				function com_sales__category_form_children($parent, $entity, $prefix = '->') {
					foreach ($parent->children as $category) {
						if ($category->is($entity))
							continue;
						?>
						<option value="<?php echo $category->guid; ?>"<?php echo $category->is($entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("{$prefix} {$category->name}"); ?></option>
						<?php
						if ($category->children)
							com_sales__category_form_children($category, $entity, "{$prefix}->");
					}
				}
				foreach ($this->categories as $category) {
					if ($category->is($this->entity))
						continue;
					?>
					<option value="<?php echo $category->guid; ?>"<?php echo $category->is($this->entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($category->name); ?></option>
					<?php
					if ($category->children)
						com_sales__category_form_children($category, $this->entity);
				} ?>
			</select>
		</label>
	</div>
	<?php if ($pines->config->com_sales->com_storefront) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Show Menu</span>
			<input class="pf-field" type="checkbox" name="show_menu" value="ON"<?php echo $this->entity->show_menu ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Menu Position</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_menu_position" name="menu_position" size="24" value="<?php echo htmlspecialchars($this->entity->menu_position); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Show Child Categories</span>
			<span class="pf-note">Show child categories when browsing this category.</span>
			<input class="pf-field" type="checkbox" name="show_children" value="ON"<?php echo $this->entity->show_children ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Show Description</span>
			<span class="pf-note">Show description when browsing this category.</span>
			<input class="pf-field" type="checkbox" name="show_description" value="ON"<?php echo $this->entity->show_description ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Description</span>
		<div class="pf-group">
			<div class="pf-field"><textarea rows="3" cols="35" class="peditor ui-widget-content ui-corner-all" name="description"><?php echo $this->entity->description; ?></textarea></div>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Common Specs</span>
		<span class="pf-note">These specs will be available for filtering in the storefront.</span>
		<div class="pf-group pf-full-width">
			<div class="pf-field">
				<table id="p_muid_specs_table">
					<thead>
						<tr>
							<th>Order</th>
							<th>Name</th>
							<th>Type</th>
							<th>Show Filter</th>
							<th>Restricted</th>
							<th>Options</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->specs)) foreach ($this->entity->specs as $key => $cur_value) { ?>
						<tr title="<?php echo htmlspecialchars($key); ?>">
							<td><?php echo htmlspecialchars($cur_value['order']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['type']); ?></td>
							<td><?php echo ($cur_value['type'] == 'heading') ? '' : ($cur_value['show_filter'] ? 'Yes' : 'No'); ?></td>
							<td><?php echo ($cur_value['type'] == 'bool' || $cur_value['type'] == 'heading') ? '' : ($cur_value['restricted'] ? 'Yes' : 'No'); ?></td>
							<td><?php echo ($cur_value['type'] == 'bool' || $cur_value['type'] == 'heading') ? '' : htmlspecialchars(implode(';;', $cur_value['options'])); ?></td>
						</tr>
						<?php } ?>
						<?php $anc_specs = isset($this->entity) ? $this->entity->get_specs_ancestors() : array(); foreach ($anc_specs as $key => $cur_value) { ?>
						<tr class="ui-state-disabled" title="<?php echo htmlspecialchars($key); ?> (Inherited from <?php echo htmlspecialchars($cur_value['category']->name); ?>)">
							<td><?php echo htmlspecialchars($cur_value['order']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['type']); ?></td>
							<td><?php echo ($cur_value['type'] == 'heading') ? '' : ($cur_value['show_filter'] ? 'Yes' : 'No'); ?></td>
							<td><?php echo ($cur_value['type'] == 'bool' || $cur_value['type'] == 'heading') ? '' : ($cur_value['restricted'] ? 'Yes' : 'No'); ?></td>
							<td><?php echo ($cur_value['type'] == 'bool' || $cur_value['type'] == 'heading') ? '' : htmlspecialchars(implode(';;', $cur_value['options'])); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
		<input type="hidden" name="specs" />
	</div>
	<style type="text/css">
		/* <![CDATA[ */
		#p_muid_spec_dialog .ui-ptags-tag {
			display: block;
			margin-top: 0.2em;
			text-align: left;
		}
		/* ]]> */
	</style>
	<div id="p_muid_spec_dialog" style="display: none;" title="Add a Spec">
		<div class="pf-form">
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					$("#p_muid_spec_type").change(function(){
						$("#p_muid_spec_forms").children().hide().filter("."+$(this).val()).show();
					}).change();
				});
				// ]]>
			</script>
			<div class="pf-element">
				<label><span class="pf-label">Sort Order</span>
					<span class="pf-note">Leave blank to sort by name.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_spec_order" size="4" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_spec_name" size="24" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Type</span>
					<select class="pf-field ui-widget-content ui-corner-all" id="p_muid_spec_type" name="cur_spec_type">
						<option value="string">String (Text)</option>
						<option value="float">Number</option>
						<option value="bool">Boolean (Yes/No)</option>
						<option value="heading">Heading (For Separation)</option>
					</select></label>
			</div>
			<div id="p_muid_spec_forms">
				<div class="string float bool">
					<div class="pf-element">
						<span class="pf-label">Show Filter</span>
						<label><input class="pf-field" type="checkbox" name="cur_spec_show_filter" /> Show filtering options in the category browser in storefront.</label>
					</div>
				</div>
				<div class="string float">
					<div class="pf-element">
						<span class="pf-label">Restricted</span>
						<label><input class="pf-field" type="checkbox" name="cur_spec_restricted" /> Only allow these options.</label>
					</div>
					<div class="pf-element">
						<span class="pf-label">Options</span>
						<span class="pf-note">Hit Enter after each option.</span>
						<span class="pf-note"><button type="button" class="ui-state-default ui-corner-all" id="p_muid_spec_do_sort">Sort Alphanumerically</button></span>
						<div class="pf-group">
							<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_spec_options" name="cur_spec_options" size="24" />
						</div>
					</div>
				</div>
				<div class="heading">
					<div class="pf-element">Headings will show up in the product page in the storefront.</div>
				</div>
			</div>
		</div>
		<br style="clear: both; height: 1px;" />
	</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">Products</span>
		<span class="pf-note">These products are assigned to this category.</span>
		<div class="pf-group">
			<div class="pf-field ui-widget-content ui-corner-all" style="padding: 1em; min-width: 300px; max-height: 200px; overflow: auto;">
				<?php foreach ($this->entity->products as $cur_product) { ?>
				<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_product->guid))); ?>"><?php echo htmlspecialchars("{$cur_product->sku} : {$cur_product->name}"); ?></a><br />
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'category/list')); ?>');" value="Cancel" />
	</div>
</form>