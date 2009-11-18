<?php
/**
 * Lists users and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Users';
?>
<script type="text/javascript">
	// <![CDATA[
	
	$(document).ready(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_user', 'newuser'); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_user', 'edituser', array('id' => '#title#')); ?>'},
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_user', 'deleteuser', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo $config->template->url('system', 'csv'); ?>", {
						filename: 'users',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 'col_1',
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_user/list_users", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#user_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="user_grid">
	<thead>
		<tr>
			<th>Username</th>
			<th>Real Name</th>
			<th>Email</th>
			<th>Default Component</th>
			<th>Primary Group</th>
			<th>Groups</th>
			<th>Inherit Abilities</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->users as $user) { ?>
		<tr title="<?php echo $user->guid; ?>">
			<td><?php echo $user->username; ?></td>
			<td><?php echo $user->name; ?></td>
			<td><?php echo $user->email; ?></td>
			<td><?php echo $user->default_component; ?></td>
			<td><?php echo $config->user_manager->get_groupname($user->gid); ?></td>
			<td><?php
			if (is_array($user->groups)) {
				if (count($user->groups) < 15) {
					$groupname_array = array();
					foreach ($user->groups as $cur_group) {
						array_push($groupname_array, $config->user_manager->get_groupname($cur_group));
					}
					echo implode(', ', $groupname_array);
				} else {
					echo count($user->groups).' groups';
				}
			}
			?></td>
			<td><?php echo $user->inherit_abilities ? "Yes" : "No"; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>