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
?>
<script type="text/javascript">
    // <![CDATA[
    var user_grid;
    var user_grid_state;

    $(document).ready(function(){
        user_grid = $("#user_grid").pgrid({
            pgrid_toolbar: true,
            pgrid_toolbar_contents: [
                {type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo $config->template->url('com_user', 'newuser'); ?>'},
                {type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo $config->template->url('com_user', 'edituser', array('user_id' => '#title#')); ?>'},
                //{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
                {type: 'separator'},
                {type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo $config->template->url('com_user', 'deleteuser', array('user_id' => '#title#')); ?>', delimiter: ','},
                {type: 'separator'},
                {type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
                {type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true}
            ],
            pgrid_sort_col: 'col_1',
            pgrid_sort_ord: 'asc'
        });
    });

    function save_state() {
        user_grid_state = user_grid.export_state();
    }

    function load_state() {
         user_grid.import_state(user_grid_state);
    }
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
				$groupname_array = array();
				foreach ($user->groups as $cur_group) {
					array_push($groupname_array, $config->user_manager->get_groupname($cur_group));
				}
				echo implode(', ', $groupname_array);
			}
            ?></td>
            <td><?php echo $user->inherit_abilities ? "Yes" : "No"; ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>