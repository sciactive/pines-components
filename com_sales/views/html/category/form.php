<?php
/**
 * Provides a form for the user to edit a category.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Category' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide category details in this form.';
if ($pines->config->com_sales->com_storefront) {
	$pines->com_pgrid->load();
	$pines->com_ptags->load();
	$pines->editor->load();
	$pages = $pines->entity_manager->get_entities(array('class' => com_content_page), array('&', 'tag' => array('com_content', 'page')));
	$pines->entity_manager->sort($pages, 'name');
}
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'category/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			<?php if ($pines->config->com_sales->com_storefront) { ?>
			$("#p_muid_spec_options").ptags({ptags_delimiter: ';;', ptags_sortable: null});
			$("#p_muid_spec_do_sort").click(function(){
				var tags_elem = $("#p_muid_spec_options");
				if (tags_elem.val() == "")
					return;
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
							spec_dialog.find("input[name=cur_spec_order]").val(pines.unsafe(rows.pgrid_get_value(1)));
							spec_dialog.find("input[name=cur_spec_name]").val(pines.unsafe(rows.pgrid_get_value(2)));
							spec_dialog.find("select[name=cur_spec_type]").val(pines.unsafe(rows.pgrid_get_value(3))).change();
							spec_dialog.find("input[name=cur_spec_show_filter]").attr("checked", rows.pgrid_get_value(4) == "Yes");
							spec_dialog.find("input[name=cur_spec_restricted]").attr("checked", rows.pgrid_get_value(5) == "Yes");
							spec_dialog.find("input[name=cur_spec_options]").val(pines.unsafe(rows.pgrid_get_value(6)));
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
									pines.safe(cur_spec_order),
									pines.safe(cur_spec_name),
									pines.safe(cur_spec_type),
									(cur_spec_type == "heading") ? '' : (cur_spec_show_filter ? 'Yes' : 'No'),
									(cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : (cur_spec_restricted ? 'Yes' : 'No'),
									(cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : pines.safe(cur_spec_options)
								]
							}];
							specs_table.pgrid_add(new_spec);
						} else {
							cur_spec.pgrid_set_value(1, pines.safe(cur_spec_order));
							cur_spec.pgrid_set_value(2, pines.safe(cur_spec_name));
							cur_spec.pgrid_set_value(3, pines.safe(cur_spec_type));
							cur_spec.pgrid_set_value(4, (cur_spec_type == "heading") ? '' : (cur_spec_show_filter ? 'Yes' : 'No'));
							cur_spec.pgrid_set_value(5, (cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : (cur_spec_restricted ? 'Yes' : 'No'));
							cur_spec.pgrid_set_value(6, (cur_spec_type == "bool" || cur_spec_type == "heading") ? '' : pines.safe(cur_spec_options));
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
			
			$("#p_muid_google_category").autocomplete({
				source: <?php echo json_encode(pines_url('com_sales','category/googlecategories_autocomplete')); ?>,
				minLength: 3
			}).each(function(){
				$(this).data("autocomplete")._renderItem = function(ul, item){
					return $("<li></li>")
						.data("item.autocomplete", item)
						.append("<a>"+item.label+"</a>")
						.appendTo(ul);
				};
			});
			<?php } ?>
		});
	</script>
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_general" data-toggle="tab">General</a></li>
		<?php if ($pines->config->com_sales->com_storefront) { ?>
		<li><a href="#p_muid_tab_storefront" data-toggle="tab">Storefront</a></li>
		<li><a href="#p_muid_tab_head" data-toggle="tab">Page Head</a></li>
		<?php } ?>
	</ul>
	<div id="p_muid_category_tabs" class="tab-content">
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
				<label>
					<span class="pf-label">Parent</span>
					<select class="pf-field" name="parent">
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
								<option value="<?php echo htmlspecialchars($category->guid); ?>"<?php echo $category->is($entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("{$prefix} {$category->name}"); ?></option>
								<?php
								if ($category->children)
									com_sales__category_form_children($category, $entity, "{$prefix}->");
							}
						}
						foreach ($this->categories as $category) {
							if ($category->is($this->entity))
								continue;
							?>
							<option value="<?php echo htmlspecialchars($category->guid); ?>"<?php echo $category->is($this->entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($category->name); ?></option>
							<?php
							if ($category->children)
								com_sales__category_form_children($category, $this->entity);
						} ?>
					</select>
				</label>
			</div>
			<?php if ($pines->config->com_sales->google_categories) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Google Category</span>
					<span class="pf-note">Corresponding category for Google Shopping.</span>
					<span class="pf-group">
						<input id="p_muid_google_category" class="pf-field" type="text" name="google_category" value="<?php echo htmlspecialchars($this->entity->google_category); ?>" />
						<a class="pf-field" href="http://support.google.com/merchants/bin/answer.py?hl=en-GB&amp;answer=1705911" target="_blank">See the full list.</a>
					</span></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<span class="pf-label">Products</span>
				<span class="pf-note">These products are assigned to this category.</span>
				<div class="pf-group">
					<div class="pf-field well" style="padding: 1em; min-width: 300px; max-height: 200px; overflow: auto;">
						<?php foreach ($this->entity->products as $cur_product) { ?>
						<a data-entity="<?php echo htmlspecialchars($cur_product->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars("[{$cur_product->sku}] {$cur_product->name}"); ?></a><br />
						<?php } ?>
					</div>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php if ($pines->config->com_sales->com_storefront) { ?>
		<div class="tab-pane" id="p_muid_tab_storefront">
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
			<div class="pf-element pf-full-width">
				<label>
					<span class="pf-label">Replace Title</span>
					<span class="pf-note">If this is not empty, it will be used, instead of the name, as the title above the content.</span>
					<span class="pf-group pf-full-width">
						<span class="pf-field" style="display: block;">
							<input style="width: 100%;" type="text" name="replace_title" value="<?php echo htmlspecialchars($this->entity->replace_title); ?>" />
						</span>
					</span>
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Title</span>
					<input class="pf-field" type="checkbox" name="show_title" value="ON"<?php echo $this->entity->show_title ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Breadcrumbs</span>
					<input class="pf-field" type="checkbox" name="show_breadcrumbs" value="ON"<?php echo $this->entity->show_breadcrumbs ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Menu</span>
					<input class="pf-field" type="checkbox" name="show_menu" value="ON"<?php echo $this->entity->show_menu ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Menu Position</span>
					<input class="pf-field" type="text" id="p_muid_menu_position" name="menu_position" size="24" value="<?php echo htmlspecialchars($this->entity->menu_position); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Child Categories</span>
					<span class="pf-note">Show child categories when browsing this category.</span>
					<input class="pf-field" type="checkbox" name="show_children" value="ON"<?php echo $this->entity->show_children ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					// <[CDATA[
					pines(function(){
						$("#p_muid_add_show_page").click(function(){
							var guid = $("#p_muid_show_page_selector").val();
							var name = $("#p_muid_show_page_selector option:selected").html();

							$("<div class=\"pf-field well\">"+pines.safe(name)+"<input type=\"hidden\" name=\"show_pages[]\" value=\""+pines.safe(guid)+"\" /> <a href=\"javascript:void(0);\" class=\"remove_page close\" style=\"float: right;\">&times;</a></div>").appendTo($("#p_muid_show_pages"));
						});
						
						$("#p_muid_show_pages").delegate("a.remove_page", "click", function(){
							$(this).closest("div.pf-field").remove();
						}).sortable();
					});
				</script>
				<label><span class="pf-label">Show Pages</span>
					<span class="pf-note">Show content page(s) when browsing this category. You can use page conditions to control which page is shown.</span>
					<select class="pf-field" id="p_muid_show_page_selector">
						<?php foreach ($pages as $cur_page) { ?>
						<option value="<?php echo htmlspecialchars($cur_page->guid); ?>"><?php echo htmlspecialchars($cur_page->name); ?></option>
						<?php } ?>
					</select></label>
				<button class="pf-field btn btn-success" id="p_muid_add_show_page" type="button">Add</button>
				<div class="pf-group" id="p_muid_show_pages" style="margin-top: .5em; padding: .2em;">
					<?php foreach ((array) $this->entity->show_pages as $cur_show_page) {
						if (!isset($cur_show_page->guid))
							continue; ?>
					<div class="pf-field well"><?php echo htmlspecialchars($cur_show_page->name); ?><input type="hidden" name="show_pages[]" value="<?php echo htmlspecialchars($cur_show_page->guid); ?>" /> <a href="javascript:void(0);" class="remove_page close" style="float: right;">&times;</a></div>
					<?php } ?>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Products</span>
					<span class="pf-note">Show products when browsing this category. This includes products from all descendant categories.</span>
					<input class="pf-field" type="checkbox" name="show_products" value="ON"<?php echo $this->entity->show_products ? ' checked="checked"' : ''; ?> /></label>
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
				#p_muid_spec_dialog .ui-ptags-tag {
					display: block;
					margin-top: 0.2em;
					text-align: left;
				}
			</style>
			<div id="p_muid_spec_dialog" style="display: none;" title="Add a Spec">
				<div class="pf-form">
					<script type="text/javascript">
						pines(function(){
							$("#p_muid_spec_type").change(function(){
								$("#p_muid_spec_forms").children().hide().filter("."+$(this).val()).show();
							}).change();
						});
					</script>
					<div class="pf-element">
						<label><span class="pf-label">Sort Order</span>
							<span class="pf-note">Leave blank to sort by name.</span>
							<input class="pf-field" type="text" name="cur_spec_order" size="4" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Name</span>
							<input class="pf-field" type="text" name="cur_spec_name" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<select class="pf-field" id="p_muid_spec_type" name="cur_spec_type">
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
								<span class="pf-note"><button type="button" class="btn" id="p_muid_spec_do_sort">Sort Alphanumerically</button></span>
								<div class="pf-group">
									<input class="pf-field" type="text" id="p_muid_spec_options" name="cur_spec_options" size="24" />
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
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'category/list'))); ?>);" value="Cancel" />
	</div>
</form>