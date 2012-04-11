<?php
/**
 * Lists raffles and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Raffles';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_raffle/raffle/list']);
?>
<script type="text/javascript">

	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_raffle/newraffle')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_raffle', 'raffle/edit')); ?>},
				<?php } if (gatekeeper('com_raffle/editraffle')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_raffle', 'raffle/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_raffle/completeraffle')) { ?>
				{type: 'button', text: 'Complete', title: 'Decide a winner/winners or show the already decided winner/winners.', extra_class: 'picon picon-games-highscores', confirm: true, url: <?php echo json_encode(pines_url('com_raffle', 'raffle/complete', array('id' => '__title__'))); ?>},
				{type: 'separator'},
				<?php } if (gatekeeper('com_raffle/deleteraffle')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_raffle', 'raffle/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'raffles',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_raffle/raffle/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Contestants</th>
			<th>Places</th>
			<th>Complete</th>
			<th>Public Link</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->raffles as $raffle) { ?>
		<tr title="<?php echo (int) $raffle->guid ?>">
			<td><?php echo htmlspecialchars($raffle->id); ?></td>
			<td><?php echo htmlspecialchars($raffle->name); ?></td>
			<td><?php echo (count($raffle->contestants) + count($raffle->public_contestants)); ?></td>
			<td><?php echo htmlspecialchars($raffle->places); ?></td>
			<td><?php echo ($raffle->complete ? 'Yes' : 'No'); ?></td>
			<td><?php echo ($raffle->public ? '<a href="'.htmlspecialchars(pines_url('com_raffle', 'enter', array('id' => $raffle->guid), true)).'" onclick="window.open(this.href); return false;">'.htmlspecialchars(pines_url('com_raffle', 'raffle', array('id' => $raffle->guid), true)).'</a>' : 'Not Public'); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>