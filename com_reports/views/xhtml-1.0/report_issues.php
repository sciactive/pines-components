<?php
/**
 * Shows a list of all employee issues.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$pines->icons->load();
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_reports/report_issues'];
?>
<style type="text/css" >
	/* <![CDATA[ */
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
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var p_muid_notice;

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: false,
			pgrid_sortable: true,
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_reports/report_issues", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		cur_options.pgrid_sort_col = false;
		$("#p_muid_grid").pgrid(cur_options);

		p_muid_notice = $.pnotify({
			pnotify_text: "",
			pnotify_hide: false,
			pnotify_closer: false,
			pnotify_history: false,
			pnotify_animate_speed: 100,
			pnotify_notice_icon: "ui-icon ui-icon-comment",
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
		}).mousemove(function(e){
			p_muid_notice.css({"top": e.clientY+12, "left": e.clientX+12});
		});
		p_muid_notice.com_reports_issue_update = function(comments){
			if (comments == "<ul><li></li></ul>") {
				p_muid_notice.pnotify_remove();
			} else {
				p_muid_notice.pnotify({pnotify_text: comments});
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
				url: "<?php echo addslashes(pines_url('com_hrm', 'issue/process')); ?>",
				type: "POST",
				dataType: "html",
				data: {"id": id, "status": status, "comments": comments},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to process this issue:\n"+XMLHttpRequest.status+": "+textStatus);
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
	// ]]>
</script>
<div class="pf-element pf-full-width">
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
			<tr onmouseover="p_muid_notice.com_reports_issue_update('&lt;ul&gt;&lt;li&gt;<?php echo htmlspecialchars(implode($cur_issue->comments, '</li><li>')); ?>&lt;/li&gt;&lt;/ul&gt;');">
				<td><?php echo format_date($cur_issue->date, 'date_sort'); ?></td>
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
						<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_reports_process_issue('<?php echo $cur_issue->guid; ?>', 'resolved');" title="Resolve"><span class="p_muid_btn picon picon-flag-yellow"></span></button>
						<?php } else { ?>
						<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_reports_process_issue('<?php echo $cur_issue->guid; ?>', 'unresolved');" title="Unresolved"><span class="p_muid_btn picon picon-flag-red"></span></button>
						<?php } ?>
						<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_reports_process_issue('<?php echo $cur_issue->guid; ?>', 'delete');" title="Remove"><span class="p_muid_btn picon picon-edit-delete"></span></button>
					<?php } ?>
					</div></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>