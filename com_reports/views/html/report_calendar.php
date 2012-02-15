<?php
/**
 * Shows a list of all employee calendar events.
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

$this->title = 'Calendar Events ['.htmlspecialchars($this->location->name).']';
if ($this->descendents)
	$this->note = 'Including locations beneath '.htmlspecialchars($this->location->name);
$pines->icons->load();
$pines->com_jstree->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_reports/report_calendar']);
?>
<style type="text/css" >
	/* <![CDATA[ */
	.p_muid_calendar_actions button {
		padding: 0;
	}
	.p_muid_calendar_actions button .ui-button-text {
		padding: 0;
	}
	.p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var p_muid_notice;

	pines(function(){
		pines.search_calendar = function(){
			// Submit the form with all of the fields.
			pines.get(<?php echo json_encode(pines_url('com_reports', 'reportcalendar')); ?>, {
				"location": location,
				"descendents": descendents,
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
		var location = "<?php echo (int) $this->location->guid ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){calendar_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){calendar_grid.date_form();}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'calendar',
						content: rows
					});
				}}
			],
			pgrid_sortable: true,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_reports/report_calendar", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		var calendar_grid = $("#p_muid_grid").pgrid(cur_options);

		calendar_grid.date_form = function(){
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
								pines.search_calendar();
							}
						}
					});
					pines.play();
				}
			});
		};
		calendar_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'locationselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
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
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
								form.dialog('close');
								pines.search_calendar();
							}
						}
					});
					pines.play();
				}
			});
		};
		p_muid_notice = $.pnotify({
			pnotify_title: "Information",
			pnotify_text: "",
			pnotify_hide: false,
			pnotify_closer: false,
			pnotify_sticker: false,
			pnotify_history: false,
			pnotify_animation: "none",
			pnotify_animate_speed: 0,
			pnotify_opacity: 1,
			pnotify_notice_icon: "",
			// Setting stack to false causes Pines Notify to ignore this notice when positioning.
			pnotify_stack: false,
			pnotify_after_init: function(pnotify){
				// Remove the notice if the user mouses over it.
				pnotify.mouseout(function(){
					pnotify.pnotify_remove();
				});
			},
			pnotify_before_open: function(pnotify){
				// This prevents the notice from displaying when it's created.
				pnotify.pnotify({
					pnotify_before_open: null
				});
				return false;
			}
		});
		$("tbody", "#p_muid_grid").mouseenter(function(){
			if (p_muid_notice.pnotify_text)
				p_muid_notice.pnotify_display();
		}).mouseleave(function(){
			p_muid_notice.pnotify_remove();
		});
		p_muid_notice.com_reports_issue_update = function(title, info){
			if (info == null) {
				info = '';
			} else {
				p_muid_notice.pnotify({ pnotify_title: pines.safe(title), pnotify_text: pines.safe(info) });
				p_muid_notice.pnotify_display();
				if (!p_muid_notice.is(":visible"))
					p_muid_notice.pnotify_display();
			}
		};
	});
	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Event</th>
			<th>Start</th>
			<th>End</th>
			<th>Location</th>
			<th>Employee</th>
			<th>Type</th>
			<th>Status</th>
			<th>Created by</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($this->events as $cur_event) {
			if ($cur_event->appointment)
				$cur_event_type = 'Appointment';
			elseif ($cur_event->scheduled)
				$cur_event_type = 'Shift';
			else
				$cur_event_type = 'Event';
			$info = str_replace('\'', '', $cur_event->information);
		?>
		<tr title="<?php echo (int) $cur_event->guid ?>" onmouseover="p_muid_notice.com_reports_issue_update(<?php echo htmlspecialchars(json_encode($cur_event->title)); ?>, <?php echo htmlspecialchars(json_encode($info)); ?>);">
			<td><?php echo htmlspecialchars($cur_event->title); ?></td>
			<td><?php echo htmlspecialchars(format_date($cur_event->start)); ?></td>
			<td><?php echo htmlspecialchars(format_date($cur_event->end)); ?></td>
			<td><?php echo htmlspecialchars($cur_event->group->name); ?></td>
			<td><?php echo htmlspecialchars($cur_event->employee->name); ?></td>
			<td><?php echo htmlspecialchars($cur_event_type); ?></td>
			<td><?php echo htmlspecialchars(ucwords($cur_event->appointment->status)); ?></td>
			<td><?php echo htmlspecialchars($cur_event->user->name); ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>