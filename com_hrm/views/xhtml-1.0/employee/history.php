<?php
/**
 * Provides a form for the user to edit a employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->entity->name);
$pines->com_pgrid->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_history .date {
		padding-right: 35px;
		font-size: 0.8em;
	}
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
		<?php if (!empty($this->sales)) { ?>
		var cur_defaults = {
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_toolbar: true,
			pgrid_view_height: 'auto',
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'sale_history',
						content: rows
					});
				}}
			]
		};
		$("#p_muid_grid, #p_muid_grid2").pgrid(cur_defaults);
		<?php } ?>
		$("#p_muid_issues").pgrid({
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_toolbar: false,
			pgrid_footer: false,
			pgrid_view_height: 'auto'
		});

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
		$("tbody", "#p_muid_issues").mouseenter(function(){
			if (p_muid_notice.pnotify_text)
				p_muid_notice.pnotify_display();
		}).mouseleave(function(){
			p_muid_notice.pnotify_remove();
		}).mousemove(function(e){
			p_muid_notice.css({"top": e.clientY+12, "left": e.clientX+12});
		});
		p_muid_notice.com_hrm_issue_update = function(comments){
			if (comments == "<ul><li></li></ul>") {
				p_muid_notice.pnotify_remove();
			} else {
				p_muid_notice.pnotify({pnotify_text: comments});
				if (!p_muid_notice.is(":visible"))
					p_muid_notice.pnotify_display();
			}
		};
		<?php if (gatekeeper('com_hrm/resolveissue')) { ?>
		// Mark an employee issue as resolved, unresolved or remove it altogether.
		pines.com_hrm_process_issue = function(id, status){
			var comments;
			if (status == 'delete') {
				if (!confirm('Delete Employee Issue?'))
					return;
			} else {
				comments = prompt('Comments:');
				if (comments == null)
					comments = '';
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
		$("#p_muid_div").accordion({autoHeight: false});
	});
	// ]]>
</script>
<div class="pf-form" id="p_muid_div">
	<?php if (!empty($this->entity->employment_history)) { ?>
	<div>
		<h3 class="ui-helper-clearfix"><a href="#">Employment History</a></h3>
		<div id="p_muid_history">
			<?php foreach ($this->entity->employment_history as $cur_history) { ?>
			<div>
				<span class="date"><?php echo format_date($cur_history[0], 'date_long'); ?></span>
				<span><?php echo htmlspecialchars($cur_history[1]); ?></span>
			</div>
			<?php } ?>
		</div>
	<?php } if (!empty($this->issues)) { ?>
	<h3 class="ui-helper-clearfix"><a href="#">Issues/Transgressions</a></h3>
	<div>
		<table id="p_muid_issues">
			<thead>
				<tr>
					<th>Date</th>
					<th>Issue</th>
					<th>Quantity</th>
					<th>Penalty</th>
					<th>Filed by</th>
					<th>Status</th>
					<?php if (gatekeeper('com_hrm/resolveissue')) { ?>
					<th>Actions</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->issues as $cur_issue) { ?>
				<tr onmouseover="p_muid_notice.com_hrm_issue_update('&lt;ul&gt;&lt;li&gt;<?php echo htmlspecialchars(implode($cur_issue->comments, '</li><li>')); ?>&lt;/li&gt;&lt;/ul&gt;');">
					<td><?php echo format_date($cur_issue->date, 'date_short'); ?></td>
					<td><?php echo htmlspecialchars($cur_issue->issue_type->name); ?></td>
					<td>x<?php echo htmlspecialchars($cur_issue->quantity); ?></td>
					<td>$<?php echo round($cur_issue->issue_type->penalty*$cur_issue->quantity, 2); ?></td>
					<td><?php echo htmlspecialchars($cur_issue->user->name); ?></td>
					<td><?php echo htmlspecialchars($cur_issue->status); ?></td>
					<?php if (gatekeeper('com_hrm/resolveissue')) { ?>
					<td><div class="p_muid_issue_actions">
						<?php if ($cur_issue->status != 'resolved') { ?>
						<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_process_issue('<?php echo $cur_issue->guid; ?>', 'resolved');" title="Resolve"><span class="p_muid_btn picon picon-flag-yellow"></span></button>
						<?php } else { ?>
						<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_process_issue('<?php echo $cur_issue->guid; ?>', 'unresolved');" title="Reissue"><span class="p_muid_btn picon picon-flag-red"></span></button>
						<?php } ?>
						<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_process_issue('<?php echo $cur_issue->guid; ?>', 'delete');" title="Remove"><span class="p_muid_btn picon picon-edit-delete"></span></button>
					</div></td>
					<?php } ?>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } if (!empty($this->sales)) { ?>
	<h3 class="ui-helper-clearfix"><a href="#">Sales History</a></h3>
	<div>
		<table id="p_muid_grid">
			<thead>
				<tr>
					<th>ID</th>
					<th>Date</th>
					<th>Customer</th>
					<th>First Item</th>
					<th>Price</th>
					<th>Status</th>
					<th>Location</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($this->sales as $cur_sale) { ?>
				<tr title="<?php echo $cur_sale->guid; ?>">
					<td><?php echo htmlspecialchars($cur_sale->id); ?></td>
					<td><?php echo format_date($cur_sale->p_cdate); ?></td>
					<td><?php echo htmlspecialchars($cur_sale->customer->name); ?></td>
					<td><?php echo htmlspecialchars($cur_sale->products[0]['entity']->name); ?></td>
					<td>$<?php echo htmlspecialchars($cur_sale->total); ?></td>
					<td><?php echo htmlspecialchars(ucwords($cur_sale->status)); ?></td>
					<td><?php echo htmlspecialchars($cur_sale->group->name); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } if (!empty($this->returns)) { ?>
	<h3 class="ui-helper-clearfix"><a href="#">Return History</a></h3>
	<div>
		<table id="p_muid_grid2">
			<thead>
				<tr>
					<th>ID</th>
					<th>Date</th>
					<th>Customer</th>
					<th>First Item</th>
					<th>Price</th>
					<th>Status</th>
					<th>Location</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($this->returns as $cur_return) { ?>
				<tr title="<?php echo $cur_return->guid; ?>">
					<td><?php echo htmlspecialchars($cur_return->id); ?></td>
					<td><?php echo format_date($cur_return->p_cdate); ?></td>
					<td><?php echo htmlspecialchars($cur_return->customer->name); ?></td>
					<td><?php echo htmlspecialchars($cur_return->products[0]['entity']->name); ?></td>
					<td>$<?php echo htmlspecialchars($cur_return->total); ?></td>
					<td><?php echo htmlspecialchars(ucwords($cur_return->status)); ?></td>
					<td><?php echo htmlspecialchars($cur_return->group->name); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>
	<br class="pf-clearing" />
</div>
<?php if (gatekeeper('com_hrm/listemployees')) { ?>
<input class="pf-button ui-state-default ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_hrm', 'employee/list', array('employed' => isset($this->entity->terminated) ? 'false' : 'true'))); ?>');" value="&laquo; Employees" />
<?php } ?>