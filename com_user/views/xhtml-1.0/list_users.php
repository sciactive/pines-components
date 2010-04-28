<?php
/**
 * Lists users and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Users';
?>
<script type="text/javascript">
	// <![CDATA[
	
	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newuser')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_user', 'edituser'); ?>'},
				<?php } if (gatekeeper('com_sales/edituser')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_user', 'edituser', array('id' => '__title__')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteuser')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_user', 'deleteuser', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'users',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_user/list_users", state: cur_state});
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
			<th>Timezone</th>
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
			<td><?php echo $user->get_timezone().(empty($user->timezone) ? ' (I)' : ' (A)'); ?></td>
			<td><?php echo $user->default_component; ?></td>
			<td><?php echo $user->group->groupname; ?></td>
			<td><?php
			if (count($user->groups) < 15) {
				$group_list = '';
				foreach ($user->groups as $cur_group) {
					$group_list .= (empty($group_list) ? '' : ', ').$cur_group->groupname;
				}
				echo $group_list;
			} else {
				echo count($user->groups).' groups';
			}
			?></td>
			<td><?php echo $user->inherit_abilities ? 'Yes' : 'No'; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<small>Note: Under timezones (I) means inherited and (A) means assigned.</small>