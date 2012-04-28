<?php
/**
 * Lists groups and provides functions to manipulate them.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = ($this->enabled ? '' : 'Disabled ').'Groups';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_user/list_groups']);

// Build an array of parents, so we can include the parent class on their rows.
$parents = array();
foreach($this->groups as $cur_group) {
	if (isset($cur_group->parent) && !in_array($cur_group->parent, $parents)) {
		array_push($parents, $cur_group->parent->guid);
	}
}
?>
<script type="text/javascript">
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_user/newgroup')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-user-group-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'editgroup')); ?>},
				<?php } if (gatekeeper('com_user/editgroup')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-group-properties', double_click: true, url: <?php echo json_encode(pines_url('com_user', 'editgroup', array('id' => '__title__'))); ?>},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_user/deletegroup')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-user-group-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_user', 'deletegroup', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } if ($this->enabled) { ?>
				{type: 'button', text: 'Disabled', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'listgroups', array('enabled' => 'false'))); ?>},
				<?php } else { ?>
				{type: 'button', text: 'Enabled', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: <?php echo json_encode(pines_url('com_user', 'listgroups')); ?>},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'groups',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'asc',
			pgrid_child_prefix: "ch_",
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_user/list_groups", state: cur_state});
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
			<th>Groupname</th>
			<th>Display Name</th>
			<th>Email</th>
			<th>Timezone</th>
			<th>Members</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->groups as $group) { ?>
		<tr title="<?php echo (int) $group->guid ?>" class="<?php
		if (in_array($group->guid, $parents))
			echo "parent ";
		if (isset($group->parent) && $group->parent->in_array($this->groups))
			echo htmlspecialchars("child ch_{$group->parent->guid}");
		?>">
			<td><?php echo (int) $group->guid ?></td>
			<td><a data-entity="<?php echo htmlspecialchars($group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($group->groupname); ?></a></td>
			<td><?php echo htmlspecialchars($group->name); ?></td>
			<td><a href="mailto:<?php echo htmlspecialchars($group->email); ?>"><?php echo htmlspecialchars($group->email); ?></a></td>
			<td><?php echo htmlspecialchars($group->timezone); ?></td>
			<td><?php
			$user_array = $pines->entity_manager->get_entities(
					array('class' => user, 'limit' => 51),
					array('&',
						'tag' => array('com_user', 'user', 'enabled')
					),
					array('|',
						'ref' => array(
							array('group', $group),
							array('groups', $group)
						)
					)
				);
			$count = count($user_array);
			if ($count < 15) {
				$user_list = '';
				foreach ($user_array as $cur_user) {
					$user_list .= (empty($user_list) ? '' : ', ').'<a data-entity="'.htmlspecialchars($cur_user->guid).'" data-entity-context="user">'.htmlspecialchars($cur_user->username).'</a>';
				}
				echo $user_list;
			} elseif ($count === 51) {
				echo 'Over 50 users';
			} else {
				echo count($user_array).' users';
			}
			?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>