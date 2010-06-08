<?php
/**
 * Lists groups and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Groups';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_user/list_groups'];

// Build an array of parents, so we can include the parent class on their rows.
$parents = array();
foreach($this->groups as $cur_group) {
	if (isset($cur_group->parent) && !in_array($cur_group->parent, $parents)) {
		array_push($parents, $cur_group->parent->guid);
	}
}
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newgroup')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-user-group-new', selection_optional: true, url: '<?php echo pines_url('com_user', 'editgroup'); ?>'},
				<?php } if (gatekeeper('com_sales/editgroup')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-group-properties', double_click: true, url: '<?php echo pines_url('com_user', 'editgroup', array('id' => '__title__')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletegroup')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-user-group-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_user', 'deletegroup', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'groups',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_user/list_groups", state: cur_state});
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
			<th>Timezone</th>
			<th>Members</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->groups as $group) { ?>
		<tr title="<?php echo $group->guid; ?>" class="<?php
		if (in_array($group->guid, $parents))
			echo "parent ";
		if (isset($group->parent))
			echo "child {$group->parent->guid}";
		?>">
			<td><?php echo $group->groupname; ?></td>
			<td><?php echo $group->name; ?></td>
			<td><?php echo $group->email; ?></td>
			<td><?php echo $group->timezone; ?></td>
			<td><?php
			$user_array = $group->get_users();
			if (count($user_array) < 15) {
				$user_list = '';
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