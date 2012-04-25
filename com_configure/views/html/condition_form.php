<?php
/**
 * Provides a form for the user to edit a condition.
 *
 * @package Components
 * @subpackage configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Conditional Configuration' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide condition details in this form.';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
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
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_configure', 'condition/save')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>Conditional Configuration</h3>
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
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'list', array('percondition' => '1')))); ?>);" value="Cancel" />
	</div>
</form>