<?php
/**
 * Display a form to edit the warboard.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Edit the Warboard';
$pines->com_pgrid->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
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
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// Location Grid
		$("#p_muid_group_grid").pgrid({
			pgrid_sort_col: 5,
			pgrid_sort_ord: "desc",
			pgrid_sortable: false,
			pgrid_paginate: false,
			pgrid_view_height: "auto",
			pgrid_toolbar: false
		});

		$("#p_muid_group_grid").pgrid_expand_rows($("#p_muid_group_grid").pgrid_get_all_rows());
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_reports', 'savewarboard')); ?>">
	<div class="pf-element">
		<span class="pf-label">Company Name</span>
		<input class="ui-widget-content ui-corner-all form_date" type="text" name="company_name" size="24" value="<?php echo htmlspecialchars($this->entity->company_name); ?>" />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Employee Positions</span>
		<span class="pf-note">Show all employees with these Job Titles</span>
		<?php foreach ($this->job_titles as $cur_title) { ?>
		<input type="checkbox" name="titles[]" value="<?php echo htmlspecialchars($cur_title); ?>" <?php echo in_array($cur_title, $this->entity->positions) ? 'checked="checked" ' : ''; ?>/> <?php echo htmlspecialchars($cur_title); ?>&nbsp;&nbsp;
		<?php } ?>
	</div>
	<div class="pf-element">
		
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-field">
			<table id="p_muid_group_grid">
				<thead>
					<tr>
						<th class="location_label">Locations</th>
						<th class="important_label" title="Select 2">Important</th>
						<th class="hq_label" title="Select 1">Headquarters</th>
						<th>Location</th>
						<th>Parent</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($this->groups as $cur_group) { ?>
					<tr title="<?php echo $cur_group->guid; ?>">
						<td class="location_label"><input type="checkbox" name="locations[]" value="<?php echo $cur_group->guid; ?>" <?php echo $cur_group->in_array($this->entity->locations) ? 'checked="checked" ' : ''; ?>/></td>
						<td class="important_label"><input type="checkbox" name="important[]" value="<?php echo $cur_group->guid; ?>" <?php echo $cur_group->in_array($this->entity->important) ? 'checked="checked" ' : ''; ?>/></td>
						<td class="hq_label"><input type="radio" name="hq" value="<?php echo $cur_group->guid; ?>" <?php echo $cur_group->is($this->entity->hq) ? 'checked="checked" ' : ''; ?>/></td>
						<td><?php echo htmlspecialchars($cur_group->name); ?></td>
						<td><?php echo htmlspecialchars($cur_group->parent->name); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="pf-element">
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<input class="ui-corner-all ui-state-default" type="submit" value="Save" />
	</div>
</form>