<?php
/**
 * Lists user templates and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'User Templates';
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newusertemplate')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_hrm', 'editusertemplate'); ?>'},
				<?php } if (gatekeeper('com_sales/editusertemplate')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_hrm', 'editusertemplate', array('id' => '#title#')); ?>'},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteusertemplate')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_hrm', 'deleteusertemplate', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'user_templates',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_hrm/list_user_templates", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#user_template_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="user_template_grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Default Component</th>
			<th>Primary Group</th>
			<th>Groups</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->user_templates as $user_template) { ?>
		<tr title="<?php echo $user_template->guid; ?>">
			<td><?php echo $user_template->name; ?></td>
			<td><?php echo $user_template->default_component; ?></td>
			<td><?php echo $user_template->group->groupname; ?></td>
			<td><?php
			if (count($user_template->groups) < 15) {
				$group_list = '';
				foreach ($user_template->groups as $cur_group) {
					$group_list .= (empty($group_list) ? '' : ', ').$cur_group->groupname;
				}
				echo $group_list;
			} else {
				echo count($user_template->groups).' groups';
			}
			?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>