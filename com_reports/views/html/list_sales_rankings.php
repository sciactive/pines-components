<?php
/**
 * Lists all of the sales rankings.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Sales Rankings';

$pines->com_pgrid->load();
$google_drive = false;
if (isset($pines->com_googledrive)) {
    $pines->com_googledrive->export_to_drive('csv');
    $google_drive = true;
} else {
    pines_log("Google Drive is not installed", 'notice');
}
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_reports/list_sales_rankings']);
?>
<script type="text/javascript">
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_reports/newsalesranking')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_reports', 'editsalesranking')); ?>},
				<?php }if (gatekeeper('com_reports/viewsalesranking')) { ?>
				{type: 'button', text: 'View', extra_class: 'picon picon-document-preview', double_click: true, url: <?php echo json_encode(pines_url('com_reports', 'viewsalesranking', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_reports/editsalesranking')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', url: <?php echo json_encode(pines_url('com_reports', 'editsalesranking', array('id' => '__title__'))); ?>},
				{type: 'separator'},
				{type: 'button', text: 'Finalize', extra_class: 'picon picon-task-complete', url: <?php echo json_encode(pines_url('com_reports', 'finalizesalesranking', array('id' => '__title__'))); ?>},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_reports/newsalesranking')) { ?>
				{type: 'button', text: 'Duplicate', extra_class: 'picon picon-tab-duplicate', confirm: true, url: <?php echo json_encode(pines_url('com_reports', 'duplicatesalesranking', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				<?php if (gatekeeper('com_reports/deletesalesranking')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_reports', 'deletesalesranking', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'sales_rankings_list',
						content: rows
					});
				}},
                                <?php // Need to check if Google Drive is installed
                                    if ($google_drive && !empty($pines->config->com_googledrive->client_id)) { ?>
                                        {type: 'button', title: 'Export to Google Drive', extra_class: 'picon drive-icon', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
                                        // First need to set the rows to which we want to export
                                        setRows(rows);
                                        // Then we have to check if we have permission to post to user's google drive
                                        checkAuth();
                                    }},
                                    <?php } elseif ($google_drive && empty($pines->config->com_googledrive->client_id)) { ?>
                                        {type: 'button', title: 'Export to Google Drive', extra_class: 'picon drive-icon', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
                                        // They have com_googledrive installed but didn't set the client id, so alert them on click
                                        alert('You need to set the CLIENT ID before you can export to Google Drive');
                                    }},
                                    <?php } ?>
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_reports/list_sales_rankings", state: cur_state});
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
			<th>Start</th>
			<th>End</th>
			<th>Finalized</th>
			<th>Highest Division</th>
			<th>Created by</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->rankings as $cur_ranking) { ?>
		<tr title="<?php echo htmlspecialchars($cur_ranking->guid); ?>">
			<td><?php echo htmlspecialchars($cur_ranking->guid); ?></td>
			<td><a data-entity="<?php echo htmlspecialchars($cur_ranking->guid); ?>" data-entity-context="com_reports_sales_ranking"><?php echo htmlspecialchars($cur_ranking->name); ?></a></td>
			<td><?php echo htmlspecialchars(format_date($cur_ranking->start_date, 'date_sort')); ?></td>
			<td><?php echo htmlspecialchars(format_date($cur_ranking->end_date - 1, 'date_sort')); ?></td>
			<td><?php echo $cur_ranking->final ? 'Yes' : 'No'; ?></td>
			<td><?php echo htmlspecialchars($cur_ranking->top_location->name); ?></td>
			<td><?php echo htmlspecialchars($cur_ranking->user->name); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>