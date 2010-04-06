<?php
/**
 * Provides a form for the user to edit a widget.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Widget' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide widget details in this form.';
?>
<form class="pform" method="post" id="widget_details" action="<?php echo htmlentities(pines_url('com_example', 'savewidget')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			var attributes = $("#attributes");
			var attributes_table = $("#attributes_table");
			var attribute_dialog = $("#attribute_dialog");

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
				width: 600,
				buttons: {
					"Done": function() {
						var cur_attribute_name = $("#cur_attribute_name").val();
						var cur_attribute_value = $("#cur_attribute_value").val();
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
				$("#cur_attribute_name").val("");
				$("#cur_attribute_value").val("");
				attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
			}

			$("#widget_tabs").tabs();
			update_attributes();
		});
		// ]]>
	</script>
	<div id="widget_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_attributes">Attributes</a></li>
		</ul>
		<div id="tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
					<?php if (isset($this->entity->uid)) { ?>
				<span>Created By: <span class="date"><?php echo $pines->user_manager->get_username($this->entity->uid); ?></span></span>
				<br />
					<?php } ?>
				<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
				<br />
				<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Name</span>
					<input class="field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Enabled</span>
					<input class="field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element full_width">
				<span class="label">Description</span><br />
				<textarea rows="3" cols="35" class="peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<div class="element full_width">
				<span class="label">Short Description</span><br />
				<textarea rows="3" cols="35" class="peditor_simple" style="width: 100%;" name="short_description"><?php echo $this->entity->short_description; ?></textarea>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_attributes">
			<div class="element full_width">
				<span class="label">Attributes</span>
				<div class="group">
					<table id="attributes_table">
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
					<input type="hidden" id="attributes" name="attributes" size="24" />
				</div>
			</div>
			<div id="attribute_dialog" title="Add an Attribute">
				<div style="width: 100%">
					<label>
						<span>Name</span>
						<input type="text" name="cur_attribute_name" id="cur_attribute_name" />
					</label>
					<label>
						<span>Value</span>
						<input type="text" name="cur_attribute_value" id="cur_attribute_value" />
					</label>
				</div>
			</div>
			<br class="spacer" />
		</div>
	</div>
	<br />
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_example', 'listwidgets')); ?>');" value="Cancel" />
	</div>
</form>