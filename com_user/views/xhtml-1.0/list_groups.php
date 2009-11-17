<?php
/**
 * Lists groups and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Groups';

// Build an array of parents, so we can include the parent class on their rows.
$parents = array();
foreach($this->groups as $cur_group) {
	if (!is_null($cur_group->parent) && !in_array($cur_group->parent, $parents)) {
		array_push($parents, $cur_group->parent);
	}
}
?>
<script type="text/javascript">
	// <![CDATA[

	$(document).ready(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_user', 'newgroup'); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_user', 'editgroup', array('id' => '#title#')); ?>'},
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_user', 'deletegroup', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					window.open("data:text/csv;charset=utf8," + encodeURIComponent(rows));
				}}
			],
			pgrid_sort_col: 'col_1',
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_user/list_groups", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#group_grid").pgrid(cur_options);
	});
	
	// ]]>
</script>
<table id="group_grid">
	<thead>
		<tr>
			<th>Groupname</th>
			<th>Display Name</th>
			<th>Email</th>
			<th>Members</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->groups as $group) { ?>
		<tr title="<?php echo $group->guid; ?>" class="<?php
		if (in_array($group->guid, $parents)) {
			echo "parent ";
		}
		if (!is_null($group->parent)) {
			echo "child {$group->parent}";
		}
		?>">
			<td><?php echo $group->groupname; ?></td>
			<td><?php echo $group->name; ?></td>
			<td><?php echo $group->email; ?></td>
			<td><?php
			$user_array = $config->user_manager->get_users_by_group($group->guid);
			if (count($user_array) < 15) {
				foreach ($user_array as $cur_user) {
					$user_list .= (empty($user_list) ? '' : ', ').$cur_user->username;
				}
				echo $user_list;
			} else {
				echo count($user_array).' users';
			}
			?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>