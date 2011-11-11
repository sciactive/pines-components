<?php
/**
 * Lists all of the paystubs.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Company Paystubs';

$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_reports/list_paystubs']);
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_reports/editpayroll')) { ?>
				{type: 'button', text: 'Present Period', extra_class: 'picon picon-view-time-schedule-calculus', selection_optional: true, url: <?php echo json_encode(pines_url('com_reports', 'reportpayroll')); ?>},
				<?php } ?>
				{type: 'button', text: 'View', extra_class: 'picon picon-document-preview', double_click: true, url: <?php echo json_encode(pines_url('com_reports', 'reportpayroll', array('id' => '__title__'))); ?>},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'paystub_list',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_reports/list_paystubs", state: cur_state});
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
			<th>ID</th>
			<th>Start</th>
			<th>End</th>
			<th>Total</th>
			<th>Comptroller</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->paystubs as $cur_stub) { ?>
		<tr title="<?php echo (int) $cur_stub->guid ?>">
			<td><?php echo (int) $cur_stub->guid ?></td>
			<td><?php echo format_date($cur_stub->start, 'date_sort'); ?></td>
			<td><?php echo format_date($cur_stub->end, 'date_sort'); ?></td>
			<td>$<?php echo number_format($cur_stub->total, 2, '.', ''); ?></td>
			<td><?php echo htmlspecialchars($cur_stub->user->name); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>