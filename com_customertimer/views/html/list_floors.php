<?php
/**
 * Lists floors and provides functions to manipulate them.
 *
 * @package Components\customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Floors';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_customertimer/list_floors']);
?>
<script type="text/javascript">
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_customertimer/newfloor')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_customertimer', 'editfloor')); ?>},
				<?php } if (gatekeeper('com_customertimer/editfloor')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', url: <?php echo json_encode(pines_url('com_customertimer', 'editfloor', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_customertimer/timefloor')) { ?>
				{type: 'button', text: 'Timer', extra_class: 'picon picon-chronometer', double_click: true, url: <?php echo json_encode(pines_url('com_customertimer', 'timefloor', array('id' => '__title__'))); ?>},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_customertimer/deletefloor')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_customertimer', 'deletefloor', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_customertimer/list_floors", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Enabled</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->floors as $floor) { ?>
		<tr title="<?php echo (int) $floor->guid ?>">
			<td><a data-entity="<?php echo htmlspecialchars($floor->guid); ?>" data-entity-context="com_customertimer_floor"><?php echo htmlspecialchars($floor->name); ?></a></td>
			<td><?php echo ($floor->enabled ? 'Yes' : 'No'); ?></td>
			<td><?php echo htmlspecialchars($floor->description); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>