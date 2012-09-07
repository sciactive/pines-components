<?php
/**
 * Display a form to edit the warboard.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Edit the Company Warboard';
$pines->com_pgrid->load();
?>
<style type="text/css" >
	#p_muid_form .location_label {
		background-color: beige;
		color: black;
	}
	#p_muid_form .important_label {
		background-color: lightsteelblue;
		color: black;
	}
	#p_muid_form .hq_label {
		background-color: palegreen;
		color: black;
	}
</style>
<script type="text/javascript">
	pines(function(){
		// Location Grid
		$("#p_muid_group_grid").pgrid({
			pgrid_sort_col: 5,
			pgrid_sort_ord: "asc",
			pgrid_paginate: false,
			pgrid_view_height: "auto",
			pgrid_toolbar: false
		});

		$("#p_muid_group_grid").pgrid_expand_rows($("#p_muid_group_grid").pgrid_get_all_rows());
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_reports', 'savewarboard')); ?>">
	<div class="pf-element">
		<span class="pf-label">Company Name</span>
		<input class="pf-field form_date" type="text" name="company_name" size="24" value="<?php echo htmlspecialchars($this->entity->company_name); ?>" />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Employee Positions</span>
		<span class="pf-note">Show all employees with these Job Titles</span>
		<div class="pf-group">
			<?php foreach ($pines->config->com_hrm->employee_departments as $cur_dept) { $cur_dept = explode(':', $cur_dept); ?>
			<label class="pf-field"><input type="checkbox" name="titles[]" value="<?php echo htmlspecialchars($cur_dept[0]); ?>" <?php echo in_array($cur_dept[0], $this->entity->positions) ? 'checked="checked" ' : ''; ?>/> <?php echo htmlspecialchars($cur_dept[0]); ?></label>
			<?php } ?>
		</div>
	</div>
	<div class="pf-element">
		<span class="pf-label">Columns</span>
		<span class="pf-note">The number of columns in each row.</span>
		<select class="pf-field" name="columns">
			<option value="1" <?php echo ($this->entity->columns == 1) ? 'selected="selected"' : ''; ?>>1</option>
			<option value="2" <?php echo ($this->entity->columns == 2) ? 'selected="selected"' : ''; ?>>2</option>
			<option value="3" <?php echo ($this->entity->columns == 3) ? 'selected="selected"' : ''; ?>>3</option>
			<option value="4" <?php echo ($this->entity->columns == 4) ? 'selected="selected"' : ''; ?>>4</option>
			<option value="5" <?php echo ($this->entity->columns == 5) ? 'selected="selected"' : ''; ?>>5</option>
			<option value="6" <?php echo ($this->entity->columns == 6) ? 'selected="selected"' : ''; ?>>6</option>
			<option value="7" <?php echo ($this->entity->columns == 7) ? 'selected="selected"' : ''; ?>>7</option>
			<option value="8" <?php echo ($this->entity->columns == 8) ? 'selected="selected"' : ''; ?>>8</option>
			<option value="9" <?php echo ($this->entity->columns == 9) ? 'selected="selected"' : ''; ?>>9</option>
			<option value="10" <?php echo ($this->entity->columns == 10) ? 'selected="selected"' : ''; ?>>10</option>
		</select>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_group_grid">
			<thead>
				<tr>
					<th class="location_label">Locations</th>
					<th class="important_label">Important</th>
					<th class="hq_label">Headquarters</th>
					<th>Location</th>
					<th>Parent</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($this->groups as $cur_group) { ?>
				<tr title="<?php echo htmlspecialchars($cur_group->guid); ?>">
					<td class="location_label"><input type="checkbox" name="locations[]" value="<?php echo htmlspecialchars($cur_group->guid); ?>" <?php echo $cur_group->in_array($this->entity->locations) ? 'checked="checked" ' : ''; ?>/></td>
					<td class="important_label"><input type="checkbox" name="important[]" value="<?php echo htmlspecialchars($cur_group->guid); ?>" <?php echo $cur_group->in_array($this->entity->important) ? 'checked="checked" ' : ''; ?>/></td>
					<td class="hq_label"><input type="radio" name="hq" value="<?php echo htmlspecialchars($cur_group->guid); ?>" <?php echo $cur_group->is($this->entity->hq) ? 'checked="checked" ' : ''; ?>/></td>
					<td><a data-entity="<?php echo htmlspecialchars($cur_group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($cur_group->name); ?></a></td>
					<td><a data-entity="<?php echo htmlspecialchars($cur_group->parent->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($cur_group->parent->name); ?></a></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Save" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_reports', 'warboard'))); ?>);" value="Cancel" />
	</div>
</form>