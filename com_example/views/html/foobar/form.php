<?php
/**
 * Provides a form for the user to edit a foobar.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Foobar' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide foobar details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_example', 'foobar/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			// Attributes
			var attributes = $("#p_muid_tab_attributes input[name=attributes]");
			var attributes_table = $("#p_muid_tab_attributes .attributes_table");
			var attribute_dialog = $("#p_muid_tab_attributes .attribute_dialog");

			attributes_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Attribute',
						extra_class: 'picon picon-list-add',
						selection_optional: true,
						click: function(){
							attribute_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Attribute',
						extra_class: 'picon picon-list-remove',
						click: function(e, rows){
							rows.pgrid_delete();
							update_attributes();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// Attribute Dialog
			attribute_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_attribute_name = attribute_dialog.find("input[name=cur_attribute_name]").val();
						var cur_attribute_value = attribute_dialog.find("input[name=cur_attribute_value]").val();
						if (cur_attribute_name == "" || cur_attribute_value == "") {
							alert("Please provide both a name and a value for this attribute.");
							return;
						}
						var new_attribute = [{
							key: null,
							values: [
								pines.safe(cur_attribute_name),
								pines.safe(cur_attribute_value)
							]
						}];
						attributes_table.pgrid_add(new_attribute);
						$(this).dialog('close');
					}
				},
				close: function(){
					update_attributes();
				}
			});

			var update_attributes = function(){
				attribute_dialog.find("input[name=cur_attribute_name]").val("");
				attribute_dialog.find("input[name=cur_attribute_value]").val("");
				attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_attributes();

			$("#p_muid_foobar_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="p_muid_foobar_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_attributes">Attributes</a></li>
		</ul>
		<div id="p_muid_tab_general">
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
				<label><span class="pf-label">Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="3" cols="35" class="peditor" style="width: 100%;" name="description"><?php echo htmlspecialchars($this->entity->description); ?></textarea>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Short Description</span><br />
				<textarea rows="3" cols="35" class="peditor-simple" style="width: 100%;" name="short_description"><?php echo htmlspecialchars($this->entity->short_description); ?></textarea>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_attributes">
			<div class="pf-element pf-full-width">
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
							<td><?php echo htmlspecialchars($cur_attribute['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_attribute['value']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="attributes" />
			</div>
			<div class="attribute_dialog" title="Add an Attribute" style="display: none;">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_attribute_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_attribute_value" size="24" />
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
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_example', 'foobar/list')); ?>');" value="Cancel" />
	</div>
</form>