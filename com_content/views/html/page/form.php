<?php
/**
 * Provides a form for the user to edit an page.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Page' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide page details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
$pines->com_ptags->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_content', 'page/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_menu_position").autocomplete({
				source: <?php echo json_encode($pines->info->template->positions); ?>
			});


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
							condition_dialog.find("input[name=cur_condition_type]").val(rows.pgrid_get_value(1));
							condition_dialog.find("input[name=cur_condition_value]").val(rows.pgrid_get_value(2));
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
									cur_condition_type,
									cur_condition_value
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, cur_condition_type);
							cur_condition.pgrid_set_value(2, cur_condition_value);
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

			$("#p_muid_page_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="p_muid_page_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_categories">Categories</a></li>
			<li><a href="#p_muid_tab_conditions">Conditions</a></li>
			<li><a href="#p_muid_tab_advanced">Advanced</a></li>
		</ul>
		<div id="p_muid_tab_general">
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
					<span class="pf-label">Name</span>
					<span style="display: block;" class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="name" value="<?php echo htmlspecialchars($this->entity->name); ?>" />
					</span>
				</label>
			</div>
			<div class="pf-element pf-full-width">
				<label>
					<span class="pf-label">Alias</span>
					<span style="display: block;" class="pf-group pf-full-width">
						<input class="pf-field ui-widget-content ui-corner-all" style="width: 100%;" type="text" name="alias" value="<?php echo htmlspecialchars($this->entity->alias); ?>" onkeyup="this.value=this.value.replace(/[^\w\d-.]/g, '_');" />
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
				<label><span class="pf-label">Show on Front Page</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_front_page">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_front_page === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_front_page === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Tags</span>
				<div class="pf-group">
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="content_tags" size="24" value="<?php echo htmlspecialchars(implode(',', $this->entity->content_tags)); ?>" />
					<script type="text/javascript">
						// <![CDATA[
						pines(function(){
							$("#p_muid_form [name=content_tags]").ptags();
						});
						// ]]>
					</script>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Intro</h1>
			</div>
			<div class="pf-element pf-full-width">
				<textarea rows="3" cols="35" class="peditor" style="width: 100%;" name="intro"><?php echo $this->entity->intro; ?></textarea>
			</div>
			<div class="pf-element pf-heading">
				<h1>Content</h1>
			</div>
			<div class="pf-element pf-full-width">
				<textarea rows="8" cols="35" class="peditor" style="width: 100%; height: 500px;" name="content"><?php echo $this->entity->content; ?></textarea>
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
									$("input", rows).attr("checked", "true");
								}},
								{type: 'button', text: 'None', title: 'Check None', extra_class: 'picon picon-dialog-cancel', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).removeAttr("checked");
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
							<th>Pages</th>
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
							<td><?php echo count($cur_category->pages); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h1>Page Conditions</h1>
				<p>Users will only see this page if these conditions are met.</p>
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
								$checker_js = addslashes($cur_checker);
								$checker_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val('$checker_js');\">$checker_html</a>";
							}
							echo implode(', ', $checker_links);
							?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_type" id="p_muid_cur_condition_type" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_value" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_advanced">
			<div class="pf-element pf-heading">
				<h1>Dates</h1>
				<p>Dates can be entered in almost any standard English phrase. (Next Monday, July 1st, Tomorrow 4pm, etc.)</p>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Override Created Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="p_cdate" value="<?php echo $this->entity->p_cdate ? format_date($this->entity->p_cdate, 'full_med') : ''; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Override Modified Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="p_mdate" value="<?php echo $this->entity->p_mdate ? format_date($this->entity->p_mdate, 'full_med') : ''; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Begin Publish Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="publish_begin" value="<?php echo $this->entity->publish_begin ? format_date($this->entity->publish_begin, 'full_med') : format_date(time(), 'full_med'); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">End Publish Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="publish_end" value="<?php echo $this->entity->publish_end ? format_date($this->entity->publish_end, 'full_med') : ''; ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Options</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Title</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_title">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_title === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_title === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Author Info</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_author_info">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_author_info === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_author_info === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Full Content in List</span>
					<span class="pf-note">Show both the intro and the content when this page is shown in a page list.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_content_in_list">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_content_in_list === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_content_in_list === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Intro on Page</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_intro">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_intro === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_intro === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Breadcrumbs</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="show_breadcrumbs">
						<option value="null">Use Default</option>
						<option value="true"<?php echo $this->entity->show_breadcrumbs === true ? ' selected="selected"' : ''; ?>>Yes</option>
						<option value="false"<?php echo $this->entity->show_breadcrumbs === false ? ' selected="selected"' : ''; ?>>No</option>
					</select></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Menu</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Menu</span>
					<input class="pf-field" type="checkbox" name="show_menu" value="ON"<?php echo $this->entity->show_menu ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Menu Position</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_menu_position" name="menu_position" size="24" value="<?php echo htmlspecialchars($this->entity->menu_position); ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Page Variants</h1>
			</div>
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					$("#p_muid_variant_template").change(function(){
						var cur_template = $(this).val();
						$("option", "#p_muid_variant_variant").hide().filter("."+cur_template).show();
					}).change();
					$("#p_muid_variant_button").click(function(){
						var cur_template = $("#p_muid_variant_template").val();
						if ($("."+cur_template, "#p_muid_variants").length) {
							alert("There is already a variant set for this template. You must remove it before setting a new variant.");
							return;
						}
						var cur_template_name = $("option:selected", "#p_muid_variant_template").text();
						var cur_variant = $("#p_muid_variant_variant").val();
						var new_html = '<div class="pf-element pf-full-width '+cur_template+'">\
							<button class="pf-field ui-state-default ui-corner-all remove" style="float: right;" type="button">Remove</button>\
							<span class="pf-label">'+cur_template_name+'</span>\
							<span class="pf-field">'+cur_variant+'</span>\
							<input type="hidden" name="variants[]" value="'+cur_template+'::'+cur_variant+'" />\
						</div>';
						$("#p_muid_variants").append(new_html).find(":button").button();
					});
					$("#p_muid_variants").delegate(".remove", "click", function(){
						$(this).closest(".pf-element").remove();
					}).find(":button").button();
				});
				// ]]>
			</script>
			<div class="pf-element">
				<span class="pf-label">Add a Variant</span>
				<?php
				$variants = array();
				foreach ($pines->components as $cur_template) {
					if (strpos($cur_template, 'tpl_') !== 0)
						continue;
					$cur_template = clean_filename($cur_template);
					// Is there even a variant option?
					if (!isset($pines->config->$cur_template->variant))
						continue;
					// Find the defaults file.
					if (!file_exists("templates/$cur_template/defaults.php"))
						continue;
					/**
					 * Get the template defaults to list all the variants.
					 */
					$template_options = (array) include("templates/$cur_template/defaults.php");
					foreach ($template_options as $cur_option) {
						if ($cur_option['name'] != 'variant')
							continue;
						$variants[$cur_template] = $cur_option['options'];
						break;
					}
				}
				if (empty($variants)) {
				?>
				<span class="pf-field">None of the enabled templates have any page variants.</span>
				<?php } else { ?>
				<br />
				<select class="pf-field ui-widget-content ui-corner-all" id="p_muid_variant_template">
					<?php foreach ($variants as $cur_template => $cur_variants) { ?>
					<option value="<?php echo htmlspecialchars($cur_template); ?>"<?php echo $cur_template == $pines->current_template ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("{$pines->info->$cur_template->name} ($cur_template)"); ?></option>
					<?php } ?>
				</select>
				<select class="pf-field ui-widget-content ui-corner-all" id="p_muid_variant_variant">
					<?php foreach ($variants as $cur_template => $cur_variants) {
						foreach ($cur_variants as $cur_description => $cur_variant) { ?>
					<option class="<?php echo htmlspecialchars($cur_template); ?>" value="<?php echo htmlspecialchars($cur_variant); ?>"><?php echo htmlspecialchars($cur_description); ?></option>
					<?php } } ?>
				</select>
				<button class="pf-field ui-state-default ui-corner-all" type="button" id="p_muid_variant_button">Add</button>
				<?php } ?>
			</div>
			<div id="p_muid_variants">
				<?php foreach ((array) $this->entity->variants as $cur_template => $cur_variant) { ?>
				<div class="pf-element pf-full-width <?php echo htmlspecialchars($cur_template); ?>">
					<button class="pf-field ui-state-default ui-corner-all remove" style="float: right;" type="button">Remove</button>
					<span class="pf-label"><?php echo htmlspecialchars("{$pines->info->$cur_template->name} ($cur_template)"); ?></span>
					<span class="pf-field"><?php echo htmlspecialchars($cur_variant); ?></span>
					<input type="hidden" name="variants[]" value="<?php echo htmlspecialchars("{$cur_template}::{$cur_variant}"); ?>" />
				</div>
				<?php } ?>
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
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_content', 'page/list')); ?>');" value="Cancel" />
	</div>
</form>