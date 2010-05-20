<?php
/**
 * Provides a form for the user to edit a package.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Package' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide package details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// Conditions
		var conditions = $("#package_details [name=meta_conditions]");
		var conditions_table = $("#package_details .conditions_table");
		var condition_dialog = $("#package_details .condition_dialog");
		var cur_condition = null;

		conditions_table.pgrid({
			pgrid_paginate: false,
			pgrid_view_height: '200px',
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Condition',
					extra_class: 'picon picon_16x16_document-new',
					selection_optional: true,
					click: function(){
						cur_condition = null;
						condition_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Edit Condition',
					extra_class: 'picon picon_16x16_document-edit',
					double_click: true,
					click: function(e, rows){
						cur_condition = rows;
						condition_dialog.find("select[name=cur_condition_class]").val(rows.pgrid_get_value(1));
						condition_dialog.find("input[name=cur_condition_type]").val(rows.pgrid_get_value(2));
						condition_dialog.find("input[name=cur_condition_value]").val(rows.pgrid_get_value(3));
						condition_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Condition',
					extra_class: 'picon picon_16x16_edit-delete',
					click: function(e, rows){
						rows.pgrid_delete();
						update_conditions();
					}
				}
			]
		});

		// Condition Dialog
		condition_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function() {
					var cur_condition_class = condition_dialog.find("select[name=cur_condition_class]").val();
					var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val();
					var cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
					if (cur_condition_type == "" || cur_condition_value == "") {
						alert("Please provide both a type and a value for this condition.");
						return;
					}
					// Is this a duplicate type?
					var dupe = false;
					conditions_table.pgrid_get_all_rows().each(function(){
						if (dupe) return;
						var check_row = $(this);
						if (check_row.pgrid_get_value(1) == cur_condition_class && check_row.pgrid_get_value(2) == cur_condition_type) {
							// If this is the current row being edited, it isn't a duplicate.
							if (!cur_condition || !cur_condition.is(check_row))
								dupe = true;
						}
					});
					if (dupe) {
						pines.notice('There is already a condition of that type for this class.');
						return;
					}
					if (!cur_condition) {
						var new_condition = [{
							key: null,
							values: [
								cur_condition_class,
								cur_condition_type,
								cur_condition_value
							]
						}];
						conditions_table.pgrid_add(new_condition);
					} else {
						cur_condition.pgrid_set_value(1, cur_condition_class);
						cur_condition.pgrid_set_value(2, cur_condition_type);
						cur_condition.pgrid_set_value(3, cur_condition_value);
					}
					update_conditions();
					$(this).dialog('close');
				}
			}
		});

		function update_conditions() {
			condition_dialog.find("select[name=cur_condition_class]").val("depend");
			condition_dialog.find("input[name=cur_condition_type]").val("");
			condition_dialog.find("input[name=cur_condition_value]").val("");
			conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
		}

		update_conditions();

		$("#package_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="package_details" action="<?php echo htmlentities(pines_url('com_packager', 'savepackage')); ?>">
	<div id="package_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_images">Images</a></li>
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
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						$("#package_details input[name=type]").change(function(){
							var class_name = $("#package_details input[name=type]:checked").val();
							$("#package_details div.package_type").hide();
							$("#package_details div.package_type."+class_name).show();
						}).change();
					});
					// ]]>
				</script>
				<span class="pf-label">Type</span>
				<div class="pf-group">
					<label><input class="pf-field ui-state-default" type="radio" name="type" value="component"<?php echo (($this->entity->type == 'component') ? ' checked="checked"' : ''); ?> /> Component</label>
					<label><input class="pf-field ui-state-default" type="radio" name="type" value="template"<?php echo (($this->entity->type == 'template') ? ' checked="checked"' : ''); ?> /> Template</label>
					<label><input class="pf-field ui-state-default" type="radio" name="type" value="system"<?php echo (($this->entity->type == 'system') ? ' checked="checked"' : ''); ?> /> System</label>
					<label><input class="pf-field ui-state-default" type="radio" name="type" value="meta"<?php echo (($this->entity->type == 'meta') ? ' checked="checked"' : ''); ?> /> Meta Package</label>
				</div>
			</div>
			<div class="package_type component">
				<div class="pf-element pf-heading">
					<h1>Component Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Component</span>
						<span class="pf-note">Information will be gathered from the component's info file.</span>
						<select class="pf-field ui-widget-content" name="pkg_component">
							<option value="null">-- Choose Component --</option>
							<?php foreach ($this->components as $cur_component) {
								if (substr($cur_component, 0, 4) != 'com_')
									continue;
								?>
							<option value="<?php echo $cur_component; ?>"<?php echo (($this->entity->component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo "{$pines->info->$cur_component->name} [{$cur_component}]"; ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
			</div>
			<div class="package_type template">
				<div class="pf-element pf-heading">
					<h1>Template Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Template</span>
						<span class="pf-note">Information will be gathered from the template's info file.</span>
						<select class="pf-field ui-widget-content" name="pkg_template">
							<option value="null">-- Choose Template --</option>
							<?php foreach ($this->components as $cur_component) {
								if (substr($cur_component, 0, 4) != 'tpl_')
									continue;
								?>
							<option value="<?php echo $cur_component; ?>"<?php echo (($this->entity->component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo "{$pines->info->$cur_component->name} [{$cur_component}]"; ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
			</div>
			<div class="package_type system">
				<div class="pf-element pf-heading">
					<h1>System Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Package Name</span>
						<input class="pf-field ui-widget-content" type="text" name="system_package_name" size="24" value="<?php echo $this->entity->name; ?>" onkeyup="this.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '');" />
					</label>
				</div>
				<div class="pf-element">
					<span>No other options are available for system packages. Information will be gathered from the system's info file.</span>
				</div>
			</div>
			<div class="package_type meta">
				<div class="pf-element pf-heading">
					<h1>Meta Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Package Name</span>
						<input class="pf-field ui-widget-content" type="text" name="meta_package_name" size="24" value="<?php echo $this->entity->name; ?>" onkeyup="this.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '');" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Canonical Name</span>
						<span class="pf-note">The name the user will see.</span>
						<input class="pf-field ui-widget-content" type="text" name="meta_name" size="24" value="<?php echo $this->entity->meta['name']; ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Author</span>
						<input class="pf-field ui-widget-content" type="text" name="meta_author" size="24" value="<?php echo $this->entity->meta['author']; ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Version</span>
						<input class="pf-field ui-widget-content" type="text" name="meta_version" size="24" value="<?php echo $this->entity->meta['version']; ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">License</span>
						<span class="pf-note">Provide the URL to an online version. If that's not available, provide the name of the license.</span>
						<input class="pf-field ui-widget-content" type="text" name="meta_license" size="24" value="<?php echo $this->entity->meta['license']; ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Short Description</span>
						<span class="pf-note">Please provide a simple description, sentence caps, no period. E.g. "XML parsing library"</span>
						<input class="pf-field ui-widget-content" type="text" name="meta_short_description" size="24" value="<?php echo $this->entity->meta['short_description']; ?>" />
					</label>
				</div>
				<div class="pf-element pf-full-width">
					<label>
						<span class="pf-label">Description</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content" style="width: 100%;" rows="3" cols="35" name="meta_description"><?php echo $this->entity->meta['description']; ?></textarea></span>
					</label>
				</div>
				<div class="pf-element pf-full-width">
					<span class="pf-label">Conditions</span>
					<span class="pf-note">There are three classes.</span>
					<span class="pf-note">Depend: package will only install if all these conditions are met.</span>
					<span class="pf-note">Recommend: package will recommend that these conditions are met.</span>
					<span class="pf-note">Conflict: package will not install if any of these conditions are met.</span>
					<div class="pf-group">
						<div class="pf-field">
							<table class="conditions_table">
								<thead>
									<tr>
										<th>Class</th>
										<th>Type</th>
										<th>Value</th>
									</tr>
								</thead>
								<tbody>
									<?php if (isset($this->entity->meta['depend'])) foreach ($this->entity->meta['depend'] as $cur_key => $cur_value) { ?>
									<tr>
										<td>depend</td>
										<td><?php echo $cur_key; ?></td>
										<td><?php echo $cur_value; ?></td>
									</tr>
									<?php } ?>
									<?php if (isset($this->entity->meta['recommend'])) foreach ($this->entity->meta['recommend'] as $cur_key => $cur_value) { ?>
									<tr>
										<td>recommend</td>
										<td><?php echo $cur_key; ?></td>
										<td><?php echo $cur_value; ?></td>
									</tr>
									<?php } ?>
									<?php if (isset($this->entity->meta['conflict'])) foreach ($this->entity->meta['conflict'] as $cur_key => $cur_value) { ?>
									<tr>
										<td>conflict</td>
										<td><?php echo $cur_key; ?></td>
										<td><?php echo $cur_value; ?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
							<input type="hidden" name="meta_conditions" />
						</div>
					</div>
				</div>
				<div class="condition_dialog" style="display: none;" title="Add a Condition">
					<div class="pf-form">
						<div class="pf-element">
							<label>
								<span class="pf-label">Class</span>
								<select class="pf-field ui-widget-content" name="cur_condition_class">
									<option value="depend">Depend</option>
									<option value="recommend">Recommend</option>
									<option value="conflict">Conflict</option>
								</select>
							</label>
						</div>
						<div class="pf-element">
							<label>
								<span class="pf-label">Type</span>
								<input class="pf-field ui-widget-content" type="text" name="cur_condition_type" size="24" />
							</label>
						</div>
						<div class="pf-element">
							<label>
								<span class="pf-label">Value</span>
								<input class="pf-field ui-widget-content" type="text" name="cur_condition_value" size="24" />
							</label>
						</div>
					</div>
					<br style="clear: both; height: 1px;" />
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Packaging Options</h1>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Filename</span>
					<span class="pf-note">Leave this blank to use the default filename scheme, "name-version".</span>
					<input class="pf-field ui-widget-content" type="text" name="filename" size="24" value="<?php echo $this->entity->filename; ?>" />
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_images">
			<div class="pf-element pf-full-width">
				<span class="pf-label">Icon</span>
				<span class="pf-field">Nothing here yet...</span>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Screenshots</span>
				<span class="pf-field">Nothing here yet...</span>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<br />
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_packager', 'listpackages')); ?>');" value="Cancel" />
	</div>
</form>