<?php
/**
 * Provides a form for the user to edit a return checklist.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Return Checklist' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide Return Checklist details in this form.';
$pines->com_pgrid->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'returnchecklist/save')); ?>">
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
			<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Label</span>
			<input class="pf-field" type="text" name="label" size="24" value="<?php echo htmlspecialchars($this->entity->label); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<script type="text/javascript">
		pines(function(){
			// Conditions
			var conditions = $("#p_muid_form [name=conditions]"),
				conditions_table = $("#p_muid_form .conditions_table"),
				condition_dialog = $("#p_muid_form .condition_dialog"),
				cur_condition = null;

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
							condition_dialog.find("input[name=cur_condition_condition]").val(pines.unsafe(rows.pgrid_get_value(1)));
							condition_dialog.find("select[name=cur_condition_type]").val(pines.unsafe(rows.pgrid_get_value(2)));
							condition_dialog.find("input[name=cur_condition_amount]").val(pines.unsafe(rows.pgrid_get_value(3)));
							if (rows.pgrid_get_value(4) == "Yes")
								condition_dialog.find("input[name=cur_condition_always]").attr("checked", true);
							else
								condition_dialog.find("input[name=cur_condition_always]").removeAttr("checked");
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
						var cur_condition_condition = condition_dialog.find("input[name=cur_condition_condition]").val(),
							cur_condition_type = condition_dialog.find("select[name=cur_condition_type]").val(),
							cur_condition_amount = condition_dialog.find("input[name=cur_condition_amount]").val(),
							cur_condition_always = condition_dialog.find("input[name=cur_condition_always]").is(":checked");
						if (cur_condition_condition == "" || cur_condition_type == "" || cur_condition_amount == "") {
							alert("Please provide both a type and an amount for this condition.");
							return;
						}
						// TODO: Check for duplicate.
						if (cur_condition == null) {
							var new_condition = [{
								key: null,
								values: [
									pines.safe(cur_condition_condition),
									pines.safe(cur_condition_type),
									pines.safe(cur_condition_amount),
									cur_condition_always ? 'Yes' : 'No'
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, pines.safe(cur_condition_condition));
							cur_condition.pgrid_set_value(2, pines.safe(cur_condition_type));
							cur_condition.pgrid_set_value(3, pines.safe(cur_condition_amount));
							cur_condition.pgrid_set_value(4, cur_condition_always ? 'Yes' : 'No');
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_conditions();
				}
			});

			var update_conditions = function(){
				condition_dialog.find("input[name=cur_condition_condition]").val("");
				condition_dialog.find("select[name=cur_condition_type]").val("");
				condition_dialog.find("input[name=cur_condition_amount]").val("");
				condition_dialog.find("input[name=cur_condition_always]").removeAttr("checked");
				conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_conditions();
		});
	</script>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Conditions</span>
		<div class="pf-group">
			<div class="pf-field">
				<table class="conditions_table">
					<thead>
						<tr>
							<th>Condition</th>
							<th>Type</th>
							<th>Amount</th>
							<th>Always Charged</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->conditions)) foreach ($this->entity->conditions as $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_value['condition']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['type']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['amount']); ?></td>
							<td><?php echo htmlspecialchars($cur_value['always'] ? 'Yes' : 'No'); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="conditions" />
			</div>
		</div>
	</div>
	<div class="condition_dialog" style="display: none;" title="Add a Condition">
		<div class="pf-form">
			<div class="pf-element">
				<label><span class="pf-label">Condition</span>
					<input class="pf-field" type="text" name="cur_condition_condition" size="24" /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Type</span>
					<select class="pf-field" name="cur_condition_type">
						<option value="percentage">Percentage</option>
						<option value="flat_rate">Flat Rate</option>
					</select>
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Amount</span>
					<span class="pf-note">$ or %</span>
					<input class="pf-field" type="text" name="cur_condition_amount" size="24" onkeyup="this.value=this.value.replace(/[^\d.]/g, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Always Charged</span>
					<input class="pf-field" type="checkbox" name="cur_condition_always" value="ON" /></label>
			</div>
		</div>
		<br style="clear: both; height: 1px;" />
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'returnchecklist/list'))); ?>);" value="Cancel" />
	</div>
</form>