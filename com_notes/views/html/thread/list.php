<?php
/**
 * Lists threads and provides functions to manipulate them.
 *
 * @package Components
 * @subpackage notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Threads';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_notes/thread/list']);
?>
<script type="text/javascript">

	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_notes/editthread')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_notes', 'thread/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_notes/deletethread')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_notes', 'thread/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'threads',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_notes/thread/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Last Modified</th>
			<th>Created</th>
			<th>Attached Entity</th>
			<th>Hidden</th>
			<th>Privacy</th>
			<th>Notes</th>
			<th>Original Poster</th>
			<th>Original Note</th>
			<th>Latest Poster</th>
			<th>Latest Note</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->threads as $thread) { ?>
		<tr title="<?php echo (int) $thread->guid ?>">
			<td><?php echo htmlspecialchars(format_date($thread->p_mdate)); ?></td>
			<td><?php echo htmlspecialchars(format_date($thread->p_cdate)); ?></td>
			<td><?php echo htmlspecialchars($thread->entities[0]->guid.': '.implode(', ', $thread->entities[0]->tags)); ?></td>
			<td><?php echo ($thread->hidden ? 'Yes' : 'No'); ?></td>
			<td><?php echo ($thread->ac->other ? 'everyone' : ($thread->ac->group ? 'my-group' : 'only-me')); ?></td>
			<td><?php echo htmlspecialchars(count($thread->notes)); ?></td>
			<td><?php echo htmlspecialchars("{$thread->user->name} [{$thread->user->username}]"); ?></td>
			<td><?php $first_note = reset($thread->notes); echo htmlspecialchars(strlen($first_note['text']) > 100 ? substr($first_note['text'], 0, 100).'...' : $first_note['text']); ?></td>
			<td><?php $last_note = end($thread->notes); echo htmlspecialchars("{$last_note['user']->name} [{$last_note['user']->username}]"); ?></td>
			<td><?php echo htmlspecialchars(strlen($last_note['text']) > 100 ? substr($last_note['text'], 0, 100).'...' : $last_note['text']); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>