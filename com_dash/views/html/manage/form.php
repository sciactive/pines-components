<?php
/**
 * Provides a form for the user to edit a dashboard.
 *
 * @package Components
 * @subpackage dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Dashboard' : 'Editing Dashboard for '.htmlspecialchars($this->entity->user->name);
$this->note = 'Provide dashboard details in this form.';
$pines->com_pgrid->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_dash', 'manage/save')); ?>">
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_general" data-toggle="tab">General</a></li>
		<li><a href="#p_muid_tab_advanced" data-toggle="tab">Advanced</a></li>
	</ul>
	<div id="p_muid_dashboard_tabs" class="tab-content">
		<div class="tab-pane active" id="p_muid_tab_general">
			<div class="pf-element">
				<label>
					<span class="pf-label">User</span>
					<span class="pf-note">Note that giving a dashboard no user makes it visible to everyone.</span>
					<select class="pf-field" name="user" size="1">
						<option value="none">--No User--</option>
						<?php foreach ($this->user_array as $cur_user) {
							?><option value="<?php echo (int) $cur_user->guid; ?>"<?php echo $cur_user->is($this->entity->user) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("{$cur_user->name} [{$cur_user->username}]"); ?></option><?php
						} ?>
					</select>
				</label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Group</span>
					<select class="pf-field" name="group" size="1">
						<option value="none">--No Group--</option>
						<?php foreach ($this->group_array as $cur_group) {
							?><option value="<?php echo (int) $cur_group->guid; ?>"<?php echo $cur_group->is($this->entity->group) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars(str_repeat('->', $cur_group->get_level())." {$cur_group->name} [{$cur_group->groupname}]"); ?></option><?php
						} ?>
					</select>
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Locked</span>
					<span class="pf-note">Locking a dashboard means the user will be unable to edit it.</span>
					<input class="pf-field" type="checkbox" name="locked" value="ON"<?php echo $this->entity->locked ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Group Access</span>
					<span class="pf-note">Must be set to read or write to allow this to be a group's dashboard.</span>
					<select class="pf-field" name="group_access">
						<option value="0"<?php echo $this->entity->ac->group == 0 ? ' selected="selected"' : ''; ?>>No Access</option>
						<option value="1"<?php echo $this->entity->ac->group == 1 ? ' selected="selected"' : ''; ?>>Read</option>
						<option value="2"<?php echo $this->entity->ac->group == 2 ? ' selected="selected"' : ''; ?>>Read and Write</option>
						<option value="3"<?php echo $this->entity->ac->group == 3 ? ' selected="selected"' : ''; ?>>Read, Write, and Delete</option>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Other Access</span>
					<span class="pf-note">Must be set to read or write to allow this to be the dashboard of a group's descendants.</span>
					<select class="pf-field" name="other_access">
						<option value="0"<?php echo $this->entity->ac->other == 0 ? ' selected="selected"' : ''; ?>>No Access</option>
						<option value="1"<?php echo $this->entity->ac->other == 1 ? ' selected="selected"' : ''; ?>>Read</option>
						<option value="2"<?php echo $this->entity->ac->other == 2 ? ' selected="selected"' : ''; ?>>Read and Write</option>
						<option value="3"<?php echo $this->entity->ac->other == 3 ? ' selected="selected"' : ''; ?>>Read, Write, and Delete</option>
					</select></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_advanced">
			<div class="pf-element pf-heading">
				<h3>Dashboard for Users</h3>
				<p>Choose users here who will use this dashboard. This will replace their current dashboard, if they have one.</p>
			</div>
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					pines(function(){
						// User Grid
						$("#p_muid_user_grid").pgrid({
							pgrid_toolbar: true,
							pgrid_toolbar_contents: [
								{type: 'button', text: 'All', title: 'Check All', extra_class: 'picon picon-checkbox', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).attr("checked", "true");
								}},
								{type: 'button', text: 'None', title: 'Check None', extra_class: 'picon picon-dialog-cancel', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).removeAttr("checked");
								}}
							],
							pgrid_sort_col: 2,
							pgrid_sort_ord: "asc",
							pgrid_paginate: false,
							pgrid_view_height: "300px"
						});
					});
				</script>
				<table id="p_muid_user_grid">
					<thead>
						<tr>
							<th>Set</th>
							<th>Name</th>
							<th>Username</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($this->user_array as $cur_user) { ?>
						<tr title="<?php echo (int) $cur_user->guid ?>">
							<td><input type="checkbox" name="users[]" value="<?php echo (int) $cur_user->guid ?>" <?php echo ($cur_user->dashboard->guid && $cur_user->dashboard->is($this->entity)) ? 'checked="checked" ' : ''; ?>/></td>
							<td><?php echo htmlspecialchars($cur_user->name); ?></td>
							<td><?php echo htmlspecialchars($cur_user->username); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="pf-element pf-heading">
				<h3>Dashboard for Groups</h3>
				<p>Choose groups here who will use this dashboard. Order of precedence is Primary Group, Secondary Groups, then ancestors of Primary Group. If a user already has a dashboard, this will not replace it. (It will only affect new users in these groups.)</p>
			</div>
			<div class="pf-element pf-full-width">
				<script type="text/javascript">
					pines(function(){
						// Group Grid
						$("#p_muid_group_grid").pgrid({
							pgrid_toolbar: true,
							pgrid_toolbar_contents: [
								{type: 'button', text: 'Expand', title: 'Expand All', extra_class: 'picon picon-arrow-down', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_expand_rows();
								}},
								{type: 'button', text: 'Collapse', title: 'Collapse All', extra_class: 'picon picon-arrow-right', selection_optional: true, return_all_rows: true, click: function(e, rows){
									rows.pgrid_collapse_rows();
								}},
								{type: 'separator'},
								{type: 'button', text: 'All', title: 'Check All', extra_class: 'picon picon-checkbox', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).attr("checked", "true");
								}},
								{type: 'button', text: 'None', title: 'Check None', extra_class: 'picon picon-dialog-cancel', selection_optional: true, return_all_rows: true, click: function(e, rows){
									$("input", rows).removeAttr("checked");
								}}
							],
							pgrid_sort_col: 2,
							pgrid_sort_ord: "asc",
							pgrid_child_prefix: "ch_",
							pgrid_paginate: false,
							pgrid_view_height: "300px"
						});
					});
				</script>
				<table id="p_muid_group_grid">
					<thead>
						<tr>
							<th>Set</th>
							<th>Name</th>
							<th>Groupname</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($this->group_array as $cur_group) { ?>
						<tr title="<?php echo (int) $cur_group->guid ?>" class="<?php echo $cur_group->get_children() ? 'parent ' : ''; ?><?php echo (isset($cur_group->parent) && $cur_group->parent->in_array($this->group_array)) ? htmlspecialchars("child ch_{$cur_group->parent->guid} ") : ''; ?>">
							<td><input type="checkbox" name="groups[]" value="<?php echo (int) $cur_group->guid ?>" <?php echo ($cur_group->dashboard->guid && $cur_group->dashboard->is($this->entity)) ? 'checked="checked" ' : ''; ?>/></td>
							<td><?php echo htmlspecialchars($cur_group->name); ?></td>
							<td><?php echo htmlspecialchars($cur_group->groupname); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_dash', 'manage/list'))); ?>);" value="Cancel" />
	</div>
</form>