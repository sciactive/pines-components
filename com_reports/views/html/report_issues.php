<?php
/**
 * Shows a list of all employee issues.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Employee Issues ['.htmlspecialchars($this->location->name).']';
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
$google_drive = false;
if (isset($pines->com_googledrive)) {
    $pines->com_googledrive->export_to_drive('csv');
    $google_drive = true;
} else {
    pines_log("Google Drive is not installed", 'notice');
}
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_reports/report_issues']);
?>
<style type="text/css" >
	.p_muid_issue_actions button {
		padding: 0;
	}
	.p_muid_issue_actions button .ui-button-text {
		padding: 0;
	}
	.p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
</style>
<script type="text/javascript">
	var p_muid_notice;

	pines(function(){
		pines.search_issues = function(){
			// Submit the form with all of the fields.
			pines.get(<?php echo json_encode(pines_url('com_reports', 'reportissues')); ?>, {
				"location": location,
				"descendants": descendants,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = <?php echo $this->start_date ? json_encode(format_date($this->start_date, 'date_sort')) : '""'; ?>;
		var end_date = <?php echo $this->end_date ? json_encode(format_date($this->end_date - 1, 'date_sort')) : '""'; ?>;
		// Location Defaults
		var location = <?php echo json_encode("{$this->location->guid}"); ?>;
		var descendants = <?php echo $this->descendants ? 'true' : 'false'; ?>;

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){issues_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){issues_grid.date_form();}},
				{type: 'separator'},
				<?php if (gatekeeper('com_hrm/listemployees')) { ?>
				{type: 'button', text: 'History', extra_class: 'picon picon-folder-html', url: <?php echo json_encode(pines_url('com_hrm', 'employee/history', array('id' => '__title__'))); ?>},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'employee_issues',
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
			pgrid_sortable: true,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_reports/report_issues", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		var issues_grid = $("#p_muid_grid").pgrid(cur_options);

		issues_grid.date_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'dateselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the date form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Date Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
								pines.search_issues();
							}
						}
					});
					pines.play();
				}
			});
		};
		issues_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'locationselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendants": descendants},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the location form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Location Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendants]").attr('checked'))
									descendants = true;
								else
									descendants = false;
								form.dialog('close');
								pines.search_issues();
							}
						}
					});
					pines.play();
				}
			});
		};

		p_muid_notice = $.pnotify({
			text: "",
			hide: false,
			closer: false,
			sticker: false,
			history: false,
			animate_speed: 100,
			icon: "ui-icon ui-icon-comment",
			// Setting stack to false causes Pines Notify to ignore this notice when positioning.
			stack: false,
			after_init: function(pnotify){
				// Remove the notice if the user mouses over it.
				pnotify.mouseout(function(){
					pnotify.pnotify_remove();
				});
			},
			before_open: function(pnotify){
				// This prevents the notice from displaying when it's created.
				pnotify.pnotify({
					before_open: null
				});
				return false;
			}
		});
		$("tbody", "#p_muid_grid").mouseenter(function(){
			if (p_muid_notice.text)
				p_muid_notice.pnotify_display();
		}).mouseleave(function(){
			p_muid_notice.pnotify_remove();
		}).mousemove(function(e){
			p_muid_notice.css({"top": e.clientY+12, "left": e.clientX+12});
		});
		p_muid_notice.com_reports_issue_update = function(comments){
			if (comments == "<ul><li></li></ul>") {
				p_muid_notice.pnotify_remove();
			} else {
				p_muid_notice.pnotify({text: comments});
				if (!p_muid_notice.is(":visible"))
					p_muid_notice.pnotify_display();
			}
		};
		<?php if (gatekeeper('com_hrm/resolveissue')) { ?>
		// Mark an employee issue as resolved, unresolved or remove it altogther.
		pines.com_reports_process_issue = function(id, status){
			var comments;
			if (status == 'delete') {
				if (!confirm('Delete Employee Issue?'))
					return;
			} else {
				comments = prompt('Comments:');
				if (comments == null)
					return;
			}
			$.ajax({
				url: <?php echo json_encode(pines_url('com_hrm', 'issue/process')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": id, "status": status, "comments": comments},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to process this issue:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == 'Error') {
						pines.error("An error occured while trying to process this issue.");
					} else {
						location.reload(true);
					}
				}
			});
		};
		<?php } ?>
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Date</th>
			<th>Location</th>
			<th>Employee</th>
			<th>Issue</th>
			<th>Quantity</th>
			<th>Penalty</th>
			<th>Filed by</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->issues as $cur_issue) { ?>
		<tr title="<?php echo htmlspecialchars($cur_issue->employee->guid); ?>" onmouseover="p_muid_notice.com_reports_issue_update('&lt;ul&gt;&lt;li&gt;'+<?php echo htmlspecialchars(json_encode(implode(array_map('htmlspecialchars', $cur_issue->comments), '</li><li>')), ENT_QUOTES); ?>+'&lt;/li&gt;&lt;/ul&gt;');">
			<td><?php echo htmlspecialchars(format_date($cur_issue->date, 'date_sort')); ?></td>
			<td><?php echo htmlspecialchars($cur_issue->location->name); ?></td>
			<td><?php echo htmlspecialchars($cur_issue->employee->name); ?></td>
			<td><?php echo htmlspecialchars($cur_issue->issue_type->name); ?></td>
			<td><?php echo htmlspecialchars($cur_issue->quantity); ?></td>
			<td>$<?php echo round($cur_issue->issue_type->penalty*$cur_issue->quantity, 2); ?></td>
			<td><?php echo htmlspecialchars($cur_issue->user->name); ?></td>
			<td><?php echo htmlspecialchars($cur_issue->status); ?></td>
			<td><div class="p_muid_issue_actions">
				<?php if (gatekeeper('com_hrm/resolveissue')) {
					if ($cur_issue->status != 'resolved') { ?>
					<button class="btn" type="button" onclick="pines.com_reports_process_issue(<?php echo htmlspecialchars(json_encode("{$cur_issue->guid}")); ?>, 'resolved');" title="Resolve"><span class="p_muid_btn picon picon-flag-yellow"></span></button>
					<?php } else { ?>
					<button class="btn" type="button" onclick="pines.com_reports_process_issue(<?php echo htmlspecialchars(json_encode("{$cur_issue->guid}")); ?>, 'unresolved');" title="Unresolved"><span class="p_muid_btn picon picon-flag-red"></span></button>
					<?php } ?>
					<button class="btn" type="button" onclick="pines.com_reports_process_issue(<?php echo htmlspecialchars(json_encode("{$cur_issue->guid}")); ?>, 'delete');" title="Remove"><span class="p_muid_btn picon picon-edit-delete"></span></button>
				<?php } ?>
				</div></td>
		</tr>
		<?php } ?>
	</tbody>
</table>