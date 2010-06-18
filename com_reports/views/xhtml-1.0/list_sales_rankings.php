<?php
/**
 * Lists all of the sales rankings.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales Rankings';

$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/list_sales_rankings'];
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_reports/newsalesranking')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo pines_url('com_reports', 'editsalesranking'); ?>'},
				<?php }if (gatekeeper('com_reports/viewsalesranking')) { ?>
				{type: 'button', text: 'View', extra_class: 'picon picon-document-preview', double_click: true, url: '<?php echo pines_url('com_reports', 'viewsalesranking', array('id' => '__title__')); ?>'},
				<?php } if (gatekeeper('com_reports/editsalesranking')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', url: '<?php echo pines_url('com_reports', 'editsalesranking', array('id' => '__title__')); ?>'},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_reports/newsalesranking')) { ?>
				{type: 'button', text: 'Duplicate', extra_class: 'picon picon-tab-duplicate', confirm: true, url: '<?php echo pines_url('com_reports', 'duplicatesalesranking', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				<?php if (gatekeeper('com_reports/deletesalesranking')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_reports', 'deletesalesranking', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'sales_rankings_list',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_reports/list_sales_rankings", state: cur_state});
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
			<th>Name</th>
			<th>Start</th>
			<th>End</th>
			<th>Highest Division</th>
			<th>Created by</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->rankings as $cur_ranking) { ?>
		<tr title="<?php echo $cur_ranking->guid; ?>">
			<td><?php echo $cur_ranking->guid; ?></td>
			<td><?php echo $cur_ranking->name; ?></td>
			<td><?php echo format_date($cur_ranking->start_date, 'date_short'); ?></td>
			<td><?php echo format_date($cur_ranking->end_date, 'date_short'); ?></td>
			<td><?php echo $cur_ranking->top_location->name; ?></td>
			<td><?php echo $cur_ranking->user->name; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>