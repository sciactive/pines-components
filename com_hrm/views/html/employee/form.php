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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Employee' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide employee account details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_rate {
		padding-left: 25px;
	}
	#p_muid_rate .label {
		font-size: 0.8em;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$("#p_muid_form [name=hire_date], #p_muid_form [name=training_completion_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

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

		$("#p_muid_form [name=pay_type]").change(function(){
			var pay_type = $(this);
			if (pay_type.val() == "hourly" || pay_type.val() == "hourly_commission" || pay_type.val() == "commission_draw") {
				$("#p_muid_rate .label").empty().append("/Hour");
				$("#p_muid_rate").show();
			} else if (pay_type.val() == "salary" || pay_type.val() == "salary_commission") {
				$("#p_muid_rate .label").empty().append("/Year");
				$("#p_muid_rate").show();
			} else {
				$("#p_muid_rate").hide();
			}
		}).change();

		$("#p_muid_employee_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_hrm', 'employee/save')); ?>">
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
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->name); ?></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Username</span>
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->username); ?></span>
			</div>
			<?php if ($pines->config->com_hrm->ssn_field && gatekeeper('com_hrm/showssn')) { ?>
			<div class="pf-element">
				<label><span class="pf-label">SSN</span>
					<span class="pf-note">Without dashes.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="ssn" size="24" value="<?php echo htmlspecialchars($this->entity->ssn); ?>" /></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">New Hire</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="checkbox" name="new_hire" value="ON" <?php echo $this->entity->new_hire ? 'checked="checked" ' : ''; ?>/></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Hire Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="hire_date" value="<?php echo empty($this->entity->hire_date) ? '' : format_date($this->entity->hire_date, 'date_sort'); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Job Title</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="job_title">
						<?php foreach ($pines->config->com_hrm->employee_departments as $cur_dept) { $cur_dept = explode(':', $cur_dept); ?>
						<option value="<?php echo htmlspecialchars($cur_dept[0]); ?>" <?php echo ($this->entity->job_title == $cur_dept[0]) ? 'selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_dept[0]); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Training Completion Date</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="training_completion_date" value="<?php echo empty($this->entity->training_completion_date) ? '' : format_date($this->entity->training_completion_date, 'date_sort'); ?>" /></label>
			</div>
			<?php if ($pines->config->com_hrm->com_calendar) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Schedule Color</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="color">
						<option value="aqua" <?php echo ($this->entity->color == 'aqua') ? 'selected="selected"' : ''; ?>>Aqua</option>
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
						<option value="orchid" <?php echo ($this->entity->color == 'orchid') ? 'selected="selected"' : ''; ?>>Orchid</option>
						<option value="peru" <?php echo ($this->entity->color == 'peru') ? 'selected="selected"' : ''; ?>>Peru</option>
						<option value="purple" <?php echo ($this->entity->color == 'purple') ? 'selected="selected"' : ''; ?>>Purple</option>
						<option value="red" <?php echo ($this->entity->color == 'red') ? 'selected="selected"' : ''; ?>>Red</option>
						<option value="vanilla" <?php echo ($this->entity->color == 'vanilla') ? 'selected="selected"' : ''; ?>>Vanilla</option>
						<option value="white" <?php echo ($this->entity->color == 'white') ? 'selected="selected"' : ''; ?>>White</option>
					</select></label>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Phone Extension</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="number" name="phone_ext" size="5" value="<?php echo htmlspecialchars($this->entity->phone_ext); ?>" /></label>
			</div>
			<div class="pf-element">
				<span class="pf-label">Compensation</span>
				<span class="pf-field" style="display: inline-block;">
					<select class="ui-widget-content ui-corner-all" name="pay_type">
						<option value="hourly" <?php echo ($this->entity->pay_type == 'hourly') ? 'selected="selected"' : ''; ?>>Hourly</option>
						<option value="commission" <?php echo ($this->entity->pay_type == 'commission') ? 'selected="selected"' : ''; ?>>Commission</option>
						<option value="hourly_commission" <?php echo ($this->entity->pay_type == 'hourly_commission') ? 'selected="selected"' : ''; ?>>Hourly + Commission</option>
						<option value="commission_draw" <?php echo ($this->entity->pay_type == 'commission_draw') ? 'selected="selected"' : ''; ?>>Commission vs Draw</option>
						<option value="salary" <?php echo ($this->entity->pay_type == 'salary') ? 'selected="selected"' : ''; ?>>Salary</option>
						<option value="salary_commission" <?php echo ($this->entity->pay_type == 'salary_commission') ? 'selected="selected"' : ''; ?>>Salary + Commission</option>
					</select>
					<label id="p_muid_rate">$<input class="ui-widget-content ui-corner-all" type="text" name="pay_rate" size="8" value="<?php echo htmlspecialchars($this->entity->pay_rate); ?>" /><span class="label"></span></label>
				</span>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Hours in Full Workday</span>
					<span class="pf-note">When the employee is scheduled "all day", it will be considered this many hours.</span>
					<span class="pf-note">Leave blank to use the default.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="workday_length" size="24" value="<?php echo htmlspecialchars($this->entity->workday_length); ?>" /></label>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="3" cols="35" class="pf-field peditor" style="width: 100%;" name="description"><?php echo htmlspecialchars($this->entity->description); ?></textarea>
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
							<td><?php echo htmlspecialchars($cur_attribute['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_attribute['value']); ?></td>
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
		<?php if ($pines->config->com_hrm->com_sales) { ?>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				// Commissions
				var commissions_table = $("#p_muid_form .commissions_table");

				commissions_table.pgrid({
					pgrid_view_height: "300px",
					pgrid_toolbar: true,
					pgrid_toolbar_contents: [
						{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
						{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
						{type: 'separator'},
						{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
							pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
								filename: "commissions-<?php echo addslashes($this->entity->username); ?>",
								content: rows
							});
						}}
					]
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
							<th>Amount ($)</th>
							<th>Ticket</th>
							<th>Product</th>
							<th>Note</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->commissions as $cur_commission) { ?>
						<tr>
							<td><?php echo format_date($cur_commission['date']); ?></td>
							<td style="text-align: right;"><?php echo isset($cur_commission['amount']) ? number_format($cur_commission['amount'], 2) : ''; ?></td>
							<td><?php
							if (!isset($cur_commission['ticket']->guid)) {
								echo "Ticket not found.";
							} elseif ($cur_commission['ticket']->has_tag('sale')) {
								echo htmlspecialchars("Sale: {$cur_commission['ticket']->id}");
							} elseif ($cur_commission['ticket']->has_tag('return')) {
								echo htmlspecialchars("Return: {$cur_commission['ticket']->id}");
							}
							?></td>
							<td><?php echo htmlspecialchars("{$cur_commission['product']->guid}: {$cur_commission['product']->name}"); ?></td>
							<td><?php echo htmlspecialchars($cur_commission['note']); ?></td>
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
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_hrm', 'employee/list', array('employed' => isset($this->entity->terminated) ? 'false' : 'true'))); ?>');" value="Cancel" />
	</div>
</form>