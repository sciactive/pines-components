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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$this->title = ($this->enabled ? '' : 'Disabled ').'Users';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_user/list_users'];
?>
<script type="text/javascript">
	// <![CDATA[
	
	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_user/newuser')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-list-add-user', selection_optional: true, url: '<?php echo addslashes(pines_url('com_user', 'edituser')); ?>'},
				<?php } if (gatekeeper('com_user/edituser')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-properties', double_click: true, url: '<?php echo addslashes(pines_url('com_user', 'edituser', array('id' => '__title__'))); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_user/deleteuser')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-list-remove-user', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_user', 'deleteuser', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } if ($this->enabled) { ?>
				{type: 'button', text: 'Disabled', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: '<?php echo addslashes(pines_url('com_user', 'listusers', array('enabled' => 'false'))); ?>'},
				<?php } else { ?>
				{type: 'button', text: 'Enabled', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: '<?php echo addslashes(pines_url('com_user', 'listusers')); ?>'},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'users',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_user/list_users", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>GUID</th>
			<th>Username</th>
			<?php if (in_array('name', $pines->config->com_user->user_fields)) { ?>
			<th>Real Name</th>
			<?php } if (in_array('email', $pines->config->com_user->user_fields)) { ?>
			<th>Email</th>
			<?php } if (in_array('timezone', $pines->config->com_user->user_fields)) { ?>
			<th>Timezone</th>
			<?php } ?>
			<th>Primary Group</th>
			<th>Groups</th>
			<th>Inherit Abilities</th>
			<?php if ($pines->config->com_user->referral_codes) { ?>
			<th>Referral Code</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->users as $user) { ?>
		<tr title="<?php echo $user->guid; ?>">
			<td><?php echo $user->guid; ?></td>
			<td><?php echo htmlspecialchars($user->username); ?></td>
			<?php if (in_array('name', $pines->config->com_user->user_fields)) { ?>
			<td><?php echo htmlspecialchars($user->name); ?></td>
			<?php } if (in_array('email', $pines->config->com_user->user_fields)) { ?>
			<td><?php echo htmlspecialchars($user->email); ?></td>
			<?php } if (in_array('timezone', $pines->config->com_user->user_fields)) { ?>
			<td><?php echo htmlspecialchars($user->get_timezone()).(empty($user->timezone) ? ' (I)' : ' (A)'); ?></td>
			<?php } ?>
			<td><a href="<?php echo htmlspecialchars(pines_url('com_user', 'editgroup', array('id' => $user->group->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($user->group->groupname); ?></a></td>
			<td><?php
			if (count($user->groups) < 15) {
				$group_list = '';
				foreach ($user->groups as $cur_group) {
					$group_list .= (empty($group_list) ? '' : ', ').'<a href="'.htmlspecialchars(pines_url('com_user', 'editgroup', array('id' => $cur_group->guid))).'" onclick="window.open(this.href); return false;">'.htmlspecialchars($cur_group->groupname).'</a>';
				}
				echo $group_list;
			} else {
				echo count($user->groups).' groups';
			}
			?></td>
			<td><?php echo $user->inherit_abilities ? 'Yes' : 'No'; ?></td>
			<?php if ($pines->config->com_user->referral_codes) { ?>
			<td><?php echo htmlspecialchars($user->referral_code); ?></td>
			<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php if (in_array('timezone', $pines->config->com_user->user_fields)) { ?>
<small>Note: Under timezones (I) means inherited and (A) means assigned.</small>
<?php } ?>