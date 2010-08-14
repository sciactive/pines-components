<?php
/**
 * Provides a form for the user to edit a condition.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Conditional Configuration' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide condition details in this form.';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
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
					if (cur_condition_type == "" || cur_condition_value == "") {
						alert("Please provide both a type and a value for this condition.");
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
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_configure', 'condition/save')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlentities($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h1>Conditional Configuration</h1>
		<p>Configuration for this will only be applied if all these conditions are met.</p>
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
					<td><?php echo htmlentities($cur_key); ?></td>
					<td><?php echo htmlentities($cur_value); ?></td>
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
					<div class="pf-field"><em><?php echo htmlentities(implode(', ', array_keys($pines->depend->checkers))); ?></em></div>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Type</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_type" size="24" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Value</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_value" size="24" /></label>
			</div>
		</div>
		<br style="clear: both; height: 1px;" />
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_configure', 'list', array('percondition' => '1'))); ?>');" value="Cancel" />
	</div>
</form>