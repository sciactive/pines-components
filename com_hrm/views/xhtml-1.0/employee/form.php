<?php
/**
 * Provides a form for the user to edit a employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Employee' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide employee account details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
?>
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
							cur_attribute_name,
							cur_attribute_value
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

		$("#p_muid_employee_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_hrm', 'employee/save')); ?>">
	<div id="p_muid_employee_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_attributes">Attributes</a></li>
			<?php if ($pines->config->com_hrm->com_sales) { ?>
			<li><a href="#p_muid_tab_commissions">Commissions</a></li>
			<?php } ?>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<span class="pf-label">Name</span>
				<span class="pf-field"><?php echo htmlentities($this->entity->name); ?></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Username</span>
				<span class="pf-field"><?php echo htmlentities($this->entity->username); ?></span>
			</div>
			<?php if ($pines->config->com_hrm->ssn_field && gatekeeper('com_hrm/showssn')) { ?>
			<div class="pf-element">
				<label><span class="pf-label">SSN</span>
					<span class="pf-note">Without dashes.</span>
					<input class="pf-field ui-widget-content" type="text" name="ssn" size="24" value="<?php echo htmlentities($this->entity->ssn); ?>" /></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Job Title</span>
					<input class="pf-field ui-widget-content" type="text" name="job_title" size="24" value="<?php echo htmlentities($this->entity->job_title); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Schedule Color</span>
					<select class="pf-field ui-widget-content" name="color">
						<option value="blue" <?php echo ($this->entity->color == 'blue') ? 'selected="selected"' : ''; ?>>Blue</option>
						<option value="blueviolet" <?php echo ($this->entity->color == 'blueviolet') ? 'selected="selected"' : ''; ?>>Blue Violet</option>
						<option value="brown" <?php echo ($this->entity->color == 'brown') ? 'selected="selected"' : ''; ?>>Brown</option>
						<option value="cornflowerblue" <?php echo ($this->entity->color == 'cornflowerblue') ? 'selected="selected"' : ''; ?>>Cornflower Blue</option>
						<option value="darkorange" <?php echo ($this->entity->color == 'darkorange') ? 'selected="selected"' : ''; ?>>Dark Orange</option>
						<option value="gainsboro" <?php echo ($this->entity->color == 'gainsboro') ? 'selected="selected"' : ''; ?>>Gainsboro</option>
						<option value="gold" <?php echo ($this->entity->color == 'gold') ? 'selected="selected"' : ''; ?>>Gold</option>
						<option value="greenyellow" <?php echo ($this->entity->color == 'greenyellow') ? 'selected="selected"' : ''; ?>>Green Yellow</option>
						<option value="lightpink" <?php echo ($this->entity->color == 'lightpink') ? 'selected="selected"' : ''; ?>>Light Pink</option>
						<option value="olive" <?php echo ($this->entity->color == 'olive') ? 'selected="selected"' : ''; ?>>Olive</option>
						<option value="red" <?php echo ($this->entity->color == 'red') ? 'selected="selected"' : ''; ?>>Red</option>
						<option value="vanilla" <?php echo ($this->entity->color == 'vanilla') ? 'selected="selected"' : ''; ?>>Vanilla</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Hours in Full Workday</span>
					<span class="pf-note">When the employee is scheduled "all day", it will be considered this many hours.</span>
					<span class="pf-note">Leave blank to use the default.</span>
					<input class="pf-field ui-widget-content" type="text" name="workday_length" size="24" value="<?php echo htmlentities($this->entity->workday_length); ?>" /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="3" cols="35" class="pf-field peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
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
						<?php foreach ($this->entity->employee_attributes as $cur_attribute) { ?>
						<tr>
							<td><?php echo htmlentities($cur_attribute['name']); ?></td>
							<td><?php echo htmlentities($cur_attribute['value']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="attributes" />
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
		<?php if ($pines->config->com_hrm->com_sales) { ?>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				// Commissions
				var commissions_table = $("#p_muid_form .commissions_table");

				commissions_table.pgrid({
					pgrid_view_height: "300px"
				});
			});
			// ]]>
		</script>
		<div id="p_muid_tab_commissions">
			<div class="pf-element pf-full-width">
				<table class="commissions_table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Amount</th>
							<th>Ticket</th>
							<th>Product</th>
							<th>Note</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->commissions as $cur_commission) { ?>
						<tr>
							<td><?php echo format_date($cur_commission['date']); ?></td>
							<td style="text-align: right;"><?php echo isset($cur_commission['amount']) ? '$'.number_format($cur_commission['amount'], 2) : ''; ?></td>
							<td><?php
							if ($cur_commission['ticket']->has_tag('sale')) {
								echo htmlentities("Sale: {$cur_commission['ticket']->id}");
							} elseif ($cur_commission['ticket']->has_tag('return')) {
								echo htmlentities("Return: {$cur_commission['ticket']->id}");
							}
							?></td>
							<td><?php echo htmlentities("{$cur_commission['product']->guid}: {$cur_commission['product']->name}"); ?></td>
							<td><?php echo htmlentities($cur_commission['note']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_hrm', 'employee/list')); ?>');" value="Cancel" />
	</div>
</form>