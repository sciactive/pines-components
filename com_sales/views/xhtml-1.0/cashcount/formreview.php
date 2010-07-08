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
	$this->note = 'Created by ' . $this->entity->user->name . ' on ' . date('Y-m-d', $this->entity->p_cdate) . ' - Last Modified on ' . date('Y-m-d', $this->entity->p_mdate);
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/cashcount/formreview'];

$comment_count = 0;
$comment_str = '';
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .error {
		color: red;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	
	var comments_note = new Array();
	
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/cashcount/formreview", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);

		pines.com_sales_create_comment = function(entry_comment){
			comments_note.push($.pnotify({
				pnotify_text: entry_comment,
				pnotify_hide: false,
				pnotify_closer: false,
				pnotify_history: false,
				pnotify_animate_speed: 100,
				pnotify_opacity: .9,
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
			}));
		};
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo pines_url('com_sales', 'cashcount/savestatus'); ?>">
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>Time</th>
				<th>Type</th>
				<th>User</th>
				<?php foreach ($this->entity->currency as $cur_denom) { ?>
					<th><?php echo $this->entity->currency_symbol . $cur_denom; ?></th>
				<?php } ?>
				<th>Total in Till</th>
				<th>Transaction Total</th>
				<th>Variance</th>
			</tr>
		</thead>
		<tbody>
			<?php if ($this->entity->comments != '') { ?>
			<script type="text/javascript">
				// <![CDATA[
				pines.com_sales_create_comment("<?php echo $this->entity->comments; ?>");
				// ]]>
			</script>
			<?php $comment_str = 'onmouseover="comments_note['.$comment_count.'].pnotify_display();" onmousemove="comments_note['.$comment_count.'].css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseout="comments_note['.$comment_count.'].pnotify_remove();"'; $comment_count++; } ?>
			<tr <?php echo $comment_str; ?>>
				<td><?php echo format_date($this->entity->p_cdate); ?></td>
				<td>Cash-In</td>
				<td><?php echo $this->entity->user->name; ?></td>
				<?php foreach ($this->entity->count as $cur_float_count) { ?>
				<td><?php echo $cur_float_count; ?></td>
				<?php } ?>
				<td>$<?php echo $this->entity->float; ?></td>
				<td>$<?php echo $this->entity->float; ?></td>
				<td>$0</td>
			</tr>
			<?php foreach ($this->entity->audits as $cur_audit) { $comment_str = ''; ?>
			<?php if ($cur_audit->comments != '') { ?>
			<script type="text/javascript">
				// <![CDATA[
				pines.com_sales_create_comment("<?php echo $cur_audit->comments; ?>");
				// ]]>
			</script>
			<?php $comment_str = 'onmouseover="comments_note['.$comment_count.'].pnotify_display();" onmousemove="comments_note['.$comment_count.'].css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseout="comments_note['.$comment_count.'].pnotify_remove();" '; $comment_count++; } ?>
			<tr <?php echo $comment_str; ?>title="<?php echo $cur_audit->guid; ?>"  <?php echo ($cur_audit->variance != 0) ? 'style="color: red;"' : ''; ?>>
				<td><?php echo format_date($cur_audit->p_cdate); ?></td>
				<td>Audit</td>
				<td><?php echo $cur_audit->user->name; ?></td>
				<?php foreach ($cur_audit->count as $cur_audit_count) { ?>
				<td><?php echo $cur_audit_count; ?></td>
				<?php } ?>
				<td>$<?php echo $cur_audit->till_total; ?></td>
				<td>$<?php echo $cur_audit->total; ?></td>
				<td>$<?php echo $cur_audit->till_total - $cur_audit->total; ?></td>
			</tr>
			<?php } foreach ($this->entity->skims as $cur_skim) { $comment_str = ''; ?>
			<?php if ($cur_skim->comments != '') { ?>
			<script type="text/javascript">
				// <![CDATA[
				pines.com_sales_create_comment("<?php echo $cur_skim->comments; ?>");
				// ]]>
			</script>
			<?php $comment_str = 'onmouseover="comments_note['.$comment_count.'].pnotify_display();" onmousemove="comments_note['.$comment_count.'].css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseout="comments_note['.$comment_count.'].pnotify_remove();" '; $comment_count++; } ?>
			<tr <?php echo $comment_str; ?>title="<?php echo $cur_skim->guid; ?>">
				<td><?php echo format_date($cur_skim->p_cdate); ?></td>
				<td>Skim</td>
				<td><?php echo $cur_skim->user->name; ?></td>
				<?php foreach ($cur_skim->count as $cur_skim_count) { ?>
				<td><?php echo $cur_skim_count; ?></td>
				<?php } ?>
				<td>$<?php echo $cur_skim->till_total; ?></td>
				<td>$<?php echo $cur_skim->total; ?></td>
				<td>$<?php echo -1 * $cur_skim->total; ?></td>
			</tr>
			<?php } foreach ($this->entity->deposits as $cur_deposit) { $comment_str = ''; ?>
			<?php if ($cur_deposit->comments != '') { ?>
			<script type="text/javascript">
				// <![CDATA[
				pines.com_sales_create_comment("<?php echo $cur_deposit->comments; ?>");
				// ]]>
			</script>
			<?php $comment_str = 'onmouseover="comments_note['.$comment_count.'].pnotify_display();" onmousemove="comments_note['.$comment_count.'].css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseout="comments_note['.$comment_count.'].pnotify_remove();" '; $comment_count++; } ?>
			<tr <?php echo $comment_str; ?>title="<?php echo $cur_deposit->guid; ?>" <?php echo ($cur_deposit->status == 'flagged') ? 'style="color: red;"' : ''; ?>>
				<td><?php echo format_date($cur_deposit->p_cdate); ?></td>
				<td>Deposit</td>
				<td><?php echo $cur_deposit->user->name; ?></td>
				<?php foreach ($cur_deposit->count as $cur_deposit_count) { ?>
				<td><?php echo $cur_deposit_count; ?></td>
				<?php } ?>
				<td>$<?php echo $cur_deposit->till_total; ?></td>
				<td>$<?php echo $cur_deposit->total; ?></td>
				<td>$<?php echo $cur_deposit->total; ?></td>
			</tr>
			<?php } ?>
			<?php if (isset($this->total_out)) {
			if ($this->entity->comments != '') { ?>
			<script type="text/javascript">
				// <![CDATA[
				pines.com_sales_create_comment("<?php echo $this->entity->comments; ?>");
				// ]]>
			</script>
			<?php $comment_str = 'onmouseover="comments_note['.$comment_count.'].pnotify_display();" onmousemove="comments_note['.$comment_count.'].css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseout="comments_note['.$comment_count.'].pnotify_remove();"'; $comment_count++; } ?>
			<tr>
				<td><?php echo format_date($this->entity->p_mdate); ?></td>
				<td>Cash-Out</td>
				<td><?php echo $this->entity->user->name; ?></td>
				<?php foreach ($this->entity->count_out as $cur_out_count) { ?>
				<td><?php echo $cur_out_count; ?></td>
				<?php } ?>
				<td>$<?php echo $this->entity->total_out; ?></td>
				<td>$<?php echo $this->entity->total_out; ?></td>
				<td>$<?php echo $this->entity->total_out-$this->entity->total; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table><br/>
	<?php if (!empty($this->entity->comments)) { ?>
	<div class="pf-element">
		<span class="pf-label">Comments</span>
		<div class="pf-group">
			<div class="pf-field"><?php echo $this->entity->comments; ?></div>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label>
			<span class="pf-label">Update Status</span>
			<select class="pf-field ui-widget-content" name="status" size="1">
				<option value="closed" <?php echo ($this->entity->status == 'closed') ? 'selected="selected"' : ''; ?>>Closed (Approved)</option>
				<option value="flagged" <?php echo ($this->entity->status == 'flagged') ? 'selected="selected"' : ''; ?>>Flagged (declined)</option>
				<option value="info_requested" <?php echo ($this->entity->status == 'info_requested') ? 'selected="selected"' : ''; ?>>Info Requested</option>
				<option value="pending" <?php echo ($this->entity->status == 'pending') ? 'selected="selected"' : ''; ?>>Pending</option>
			</select>
		</label>
	</div>
	<div class="pf-element pf-heading">
		<h1>Review Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea class="ui-widget-content" style="width: 100%;" rows="3" cols="35" name="review_comments"><?php echo $this->entity->review_comments; ?></textarea></div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input name="approve" class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_sales', 'cashcount/list'); ?>');" value="Cancel" />
	</div>
</form>