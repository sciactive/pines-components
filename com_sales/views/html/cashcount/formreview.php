<?php
/**
 * Provides a form for the user to review a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Reviewing Cash Count ['.$this->entity->guid.']';
if (isset($this->entity->guid))
	$this->note = 'Created by ' . htmlspecialchars($this->entity->user->name) . ' on ' . format_date($this->entity->p_cdate, 'date_short') . ' - Last Modified on ' . format_date($this->entity->p_mdate, 'date_short');
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/cashcount/formreview'];
?>
<script type="text/javascript">
	// <![CDATA[

	var p_muid_notice;

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: false,
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/cashcount/formreview", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
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
			p_muid_notice.pnotify_display();
		}).mouseleave(function(){
			p_muid_notice.pnotify_remove();
		}).mousemove(function(e){
			p_muid_notice.css({"top": e.clientY+12, "left": e.clientX+12});
		});
		p_muid_notice.com_sales_update = function(comments){
			if (comments == "") {
				p_muid_notice.pnotify_remove();
			} else {
				p_muid_notice.pnotify({pnotify_text: comments});
				if (!p_muid_notice.is(":visible"))
					p_muid_notice.pnotify_display();
			}
		};
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'cashcount/savestatus')); ?>">
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>Time</th>
				<th>Type</th>
				<th>User</th>
				<?php foreach ($this->entity->currency as $cur_denom) { ?>
					<th><?php echo htmlspecialchars($this->entity->currency_symbol . $cur_denom); ?></th>
				<?php } ?>
				<th>Total in Till</th>
				<th>Transaction Total</th>
				<th>Variance</th>
			</tr>
		</thead>
		<tbody>
			<tr onmouseover="p_muid_notice.com_sales_update('<?php echo htmlspecialchars(addslashes($this->entity->comments)); ?>');">
				<td><?php echo format_date($this->entity->p_cdate); ?></td>
				<td>Cash-In</td>
				<td><?php echo htmlspecialchars($this->entity->user->name); ?></td>
				<?php foreach ($this->entity->count as $cur_float_count) { ?>
				<td><?php echo htmlspecialchars($cur_float_count); ?></td>
				<?php } ?>
				<td>$<?php echo htmlspecialchars($this->entity->float); ?></td>
				<td>$<?php echo htmlspecialchars($this->entity->float); ?></td>
				<td>$0</td>
			</tr>
			<?php foreach ($this->entity->audits as $cur_audit) { ?>
			<tr onmouseover="p_muid_notice.com_sales_update('<?php echo htmlspecialchars(addslashes($cur_audit->comments)); ?>');" <?php echo (($cur_audit->till_total - $cur_audit->total) != 0) ? 'class="ui-state-error"' : ''; ?>>
				<td><?php echo format_date($cur_audit->p_cdate); ?></td>
				<td>Audit</td>
				<td><?php echo htmlspecialchars($cur_audit->user->name); ?></td>
				<?php foreach ($cur_audit->count as $cur_audit_count) { ?>
				<td><?php echo htmlspecialchars($cur_audit_count); ?></td>
				<?php } ?>
				<td>$<?php echo htmlspecialchars($cur_audit->till_total); ?></td>
				<td>$<?php echo htmlspecialchars($cur_audit->total); ?></td>
				<td>$<?php echo htmlspecialchars($cur_audit->till_total - $cur_audit->total); ?></td>
			</tr>
			<?php } foreach ($this->entity->skims as $cur_skim) { ?>
			<tr onmouseover="p_muid_notice.com_sales_update('<?php echo htmlspecialchars(addslashes($cur_skim->comments)); ?>');">
				<td><?php echo format_date($cur_skim->p_cdate); ?></td>
				<td>Skim</td>
				<td><?php echo htmlspecialchars($cur_skim->user->name); ?></td>
				<?php foreach ($cur_skim->count as $cur_skim_count) { ?>
				<td><?php echo htmlspecialchars($cur_skim_count); ?></td>
				<?php } ?>
				<td>$<?php echo htmlspecialchars($cur_skim->till_total); ?></td>
				<td>$<?php echo htmlspecialchars($cur_skim->total); ?></td>
				<td>$<?php echo htmlspecialchars(-1 * $cur_skim->total); ?></td>
			</tr>
			<?php } foreach ($this->entity->deposits as $cur_deposit) { ?>
			<tr onmouseover="p_muid_notice.com_sales_update('<?php echo htmlspecialchars(addslashes($cur_deposit->comments)); ?>');" <?php echo ($cur_deposit->status == 'flagged') ? 'class="ui-state-error"' : ''; ?>>
				<td><?php echo format_date($cur_deposit->p_cdate); ?></td>
				<td>Deposit</td>
				<td><?php echo htmlspecialchars($cur_deposit->user->name); ?></td>
				<?php foreach ($cur_deposit->count as $cur_deposit_count) { ?>
				<td><?php echo htmlspecialchars($cur_deposit_count); ?></td>
				<?php } ?>
				<td>$<?php echo htmlspecialchars($cur_deposit->till_total); ?></td>
				<td>$<?php echo htmlspecialchars($cur_deposit->total); ?></td>
				<td>$<?php echo htmlspecialchars($cur_deposit->total); ?></td>
			</tr>
			<?php } ?>
			<?php if ($this->entity->cashed_out) { ?>
			<tr onmouseover="p_muid_notice.com_sales_update('<?php echo htmlspecialchars(addslashes($this->entity->comments)); ?>');">
				<td><?php echo format_date($this->entity->cashed_out_date); ?></td>
				<td>Cash-Out</td>
				<td><?php echo htmlspecialchars($this->entity->cashed_out_user->name); ?></td>
				<?php foreach ($this->entity->count_out as $cur_out_count) { ?>
				<td><?php echo htmlspecialchars($cur_out_count); ?></td>
				<?php } ?>
				<td>$<?php echo htmlspecialchars($this->entity->total); ?></td>
				<td>$<?php echo htmlspecialchars($this->entity->total_out); ?></td>
				<td>$<?php echo htmlspecialchars($this->entity->total_out - $this->entity->total); ?></td>
			</tr>
			<?php } else { ?>
			<tr onmouseover="p_muid_notice.com_sales_update('<?php echo htmlspecialchars(addslashes($this->entity->comments)); ?>');">
				<td><?php echo format_date(time()); ?></td>
				<td>Current</td>
				<td></td>
				<?php foreach ($this->entity->count as $cur_count) { ?>
				<td></td>
				<?php } ?>
				<td>$<?php echo htmlspecialchars($this->entity->total); ?></td>
				<td>$0</td>
				<td>$0</td>
			</tr>
			<?php } ?>
		</tbody>
	</table><br />
	<?php if (!empty($this->entity->comments)) { ?>
	<div class="pf-element">
		<span class="pf-label">Comments</span>
		<div class="pf-group">
			<div class="pf-field"><?php echo htmlspecialchars($this->entity->comments); ?></div>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label>
			<span class="pf-label">Update Status</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="status" size="1">
				<option value="closed" <?php echo ($this->entity->status == 'closed') ? 'selected="selected"' : ''; ?>>Closed (Approved)</option>
				<option value="flagged" <?php echo ($this->entity->status == 'flagged') ? 'selected="selected"' : ''; ?>>Flagged (Declined)</option>
				<option value="info_requested" <?php echo ($this->entity->status == 'info_requested') ? 'selected="selected"' : ''; ?>>Info Requested</option>
				<option value="pending" <?php echo ($this->entity->status == 'pending') ? 'selected="selected"' : ''; ?>>Pending</option>
			</select>
		</label>
	</div>
	<div class="pf-element pf-heading">
		<h1>Review Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="review_comments"><?php echo $this->entity->review_comments; ?></textarea></div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input name="approve" class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'cashcount/list')); ?>');" value="Cancel" />
	</div>
</form>