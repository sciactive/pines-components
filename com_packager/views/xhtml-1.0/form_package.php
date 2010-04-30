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
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// Attributes
		var attributes = $("#tab_attributes .attributes");
		var attributes_table = $("#tab_attributes .attributes_table");
		var attribute_dialog = $("#tab_attributes .attribute_dialog");

		attributes_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Attribute',
					extra_class: 'icon picon_16x16_actions_list-add',
					selection_optional: true,
					click: function(){
						attribute_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Attribute',
					extra_class: 'icon picon_16x16_actions_list-remove',
					click: function(e, rows){
						rows.pgrid_delete();
						update_attributes();
					}
				}
			]
		});

		// Attribute Dialog
		attribute_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function() {
					var cur_attribute_name = attribute_dialog.find("input[name=cur_attribute_name]").val();
					var cur_attribute_value = attribute_dialog.find("input[name=cur_attribute_value]").val();
					if (cur_attribute_name == "" || cur_attribute_value == "") {
						alert("Please provide both a name and a value for this attribute.");
						return;
					}
					var new_attribute = [{
						key: null,
						values: [
							cur_attribute_name,
							cur_attribute_value
						]
					}];
					attributes_table.pgrid_add(new_attribute);
					update_attributes();
					$(this).dialog('close');
				}
			}
		});

		function update_attributes() {
			attribute_dialog.find("input[name=cur_attribute_name]").val("");
			attribute_dialog.find("input[name=cur_attribute_value]").val("");
			attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
		}

		update_attributes();

		$("#package_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="package_details" action="<?php echo htmlentities(pines_url('com_packager', 'savepackage')); ?>">
	<div id="package_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_images">Images</a></li>
			<li><a href="#tab_attributes">Attributes</a></li>
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
				<label>
					<span class="pf-label">Name</span>
					<span class="pf-note">This name is only used in this system. It will not be used when making the package.</span>
					<input class="pf-field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" />
				</label>
			</div>
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
			<div class="package_type meta">
				<div class="pf-element pf-heading">
					<h1>Meta Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Title</span>
						<input class="pf-field ui-widget-content" type="text" name="meta_title" size="24" value="<?php echo $this->entity->meta['title']; ?>" />
					</label>
				</div>
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
		<div id="tab_attributes">
			<div class="pf-element pf-full-width">
				<span class="pf-label">Attributes</span>
				<div class="pf-group">
					<table class="attributes_table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
							<tr>
								<td><?php echo $cur_attribute['name']; ?></td>
								<td><?php echo $cur_attribute['value']; ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<input type="hidden" name="attributes" />
				</div>
			</div>
			<div class="attribute_dialog" style="display: none;" title="Add an Attribute">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content" type="text" name="cur_attribute_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content" type="text" name="cur_attribute_value" size="24" />
						</label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
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