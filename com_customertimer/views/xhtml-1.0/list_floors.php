<?php
/**
 * Lists floors and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Floors';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customertimer/list_floors'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_customertimer/newfloor')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_customertimer', 'editfloor'); ?>'},
				<?php } if (gatekeeper('com_customertimer/editfloor')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', url: '<?php echo pines_url('com_customertimer', 'editfloor', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_customertimer/timefloor')) { ?>
				{type: 'button', text: 'Timer', extra_class: 'icon picon_16x16_stock_generic_stock_timer', double_click: true, url: '<?php echo pines_url('com_customertimer', 'timefloor', array('id' => '__title__')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_customertimer/deletefloor')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_customertimer', 'deletefloor', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'floors',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_customertimer/list_floors", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#floor_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="floor_grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Enabled</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->floors as $floor) { ?>
		<tr title="<?php echo $floor->guid; ?>">
			<td><?php echo $floor->name; ?></td>
			<td><?php echo ($floor->enabled ? 'Yes' : 'No'); ?></td>
			<td><?php echo $floor->description; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>