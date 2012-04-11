<?php
/**
 * Lists dashboards and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Dashboards';
if (isset($this->category))
	$this->title .= htmlspecialchars(" in {$this->category->name} [{$this->category->alias}]");
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_dash/manage/list']);
?>
<script type="text/javascript">
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'Edit Dashboard', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_dash', null, array('id' => '__title__'))); ?>},
				{type: 'button', text: 'Edit Options', extra_class: 'picon picon-view-form', url: <?php echo json_encode(pines_url('com_dash', 'manage/edit', array('id' => '__title__'))); ?>},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_dash', 'manage/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'dashboards',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_dash/manage/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>GUID</th>
			<th>User</th>
			<th>Current <a title="Whether this dashboard is the current dashboard the user sees." href="javascript:void(0);" onclick="alert($(this).attr('title'));">(?)</a></th>
			<th>Group</th>
			<th>Created</th>
			<th>Modified</th>
			<th>Locked</th>
			<th>Tabs</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->dashboards as $dashboard) { ?>
		<tr title="<?php echo (int) $dashboard->guid ?>">
			<td><?php echo htmlspecialchars($dashboard->guid); ?></td>
			<td><?php echo htmlspecialchars("{$dashboard->user->name} [{$dashboard->user->username}]"); ?></td>
			<td><?php echo $dashboard->is($dashboard->user->dashboard) ? 'Yes' : 'No'; ?></td>
			<td><?php echo htmlspecialchars("{$dashboard->group->name} [{$dashboard->group->groupname}]"); ?></td>
			<td><?php echo htmlspecialchars(format_date($dashboard->p_cdate)); ?></td>
			<td><?php echo htmlspecialchars(format_date($dashboard->p_mdate)); ?></td>
			<td><?php echo $dashboard->locked ? 'Yes' : 'No'; ?></td>
			<td><?php echo htmlspecialchars(count($dashboard->tabs)); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>