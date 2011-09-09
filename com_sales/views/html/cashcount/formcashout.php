<?php
/**
 * Provides a form for the user to cash-out a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Cash-Out of Cash Count ['.htmlspecialchars($this->entity->guid).']';
if (isset($this->entity->guid))
	$this->note = 'Created by ' . htmlspecialchars($this->entity->user->name) . ' on ' . format_date($this->entity->p_cdate, 'date_short') . ' - Last Modified on ' . format_date($this->entity->p_mdate, 'date_short');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .amount {
		font-weight: bold;
		display: inline-block;
		width: 3em;
		text-align: right;
	}
	#p_muid_form .amt_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#p_muid_form .entry {
		width: 2em;
		text-align: right;
	}
	#p_muid_form .total {
		border-width: .2em;
		font-weight: bold;
		font-size: 2em;
		position: absolute;
		right: 0;
		top: 0;
		padding: 50px;
		text-align: center;
	}
	/* Add and Remove Classes to show recent changes. */
	#p_muid_form .added {
		border: green solid 1px;
		color: green;
	}
	#p_muid_form .removed {
		border: red solid 1px;
		color: red;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var cash_symbol = "<?php echo addslashes($this->entity->currency_symbol); ?>";

		// Update the cash count as money is counted.
		$("#p_muid_form .entry").change(function(){
			update_total();
		}).focus(function(){
			$(this).select();
		});

		var update_total = function(){
			var total_count = 0;
			$("#p_muid_form .entry").each(function() {
				//This looks complicated but it simply multiplies the number of
				//bills/coins for each denomition by its respective value.
				//ex: 5 x 0.25 for 5 quarters that have been counted
				var cur_entry = $(this);
				var subtotal = parseInt(cur_entry.val()) * parseFloat(cur_entry.attr("title"));
				if (isNaN(subtotal))
					cur_entry.val('0');
				else
					total_count += subtotal;
				cur_entry.removeClass("added removed");
			});
			$("#p_muid_total_cashcount").html(cash_symbol+total_count.toFixed(2));
		};

		$("button.clear_btn", "#p_muid_form").click(function(){
			if (confirm("Clear all entered cash counts?")) {
				$("#p_muid_form .entry").each(function() { $(this).val(0); });
				update_total();
			}
			$("#p_muid_form [name=clear_btn]").blur();
		});

		$("button.add_btn", "#p_muid_form").click(function(){
			var cur_button = $(this);
			var cur_input = cur_button.siblings("input.entry");
			cur_input.val(parseInt(cur_input.val()) + 1).change().addClass('added');
			cur_button.blur();
		});
		$("button.remove_btn", "#p_muid_form").click(function(){
			var cur_button = $(this);
			var cur_input = cur_button.siblings("input.entry");
			cur_input.val(parseInt(cur_input.val()) - 1).change().addClass('removed');
			cur_button.blur();
		});

		update_total();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'cashcount/savecashout')); ?>">
	<?php if (!empty($this->entity->review_comments)) {?>
	<div class="pf-element pf-heading">
		<h1>Reviewer Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-field"><?php echo htmlspecialchars($this->entity->review_comments); ?></div>
	</div>
	<?php } if (!$this->entity->cashed_out) { ?>
	<div class="pf-element pf-heading">
		<button class="ui-state-default ui-corner-all clear_btn" type="button" style="display: block; float: right;">Clear All</button>
		<h1>Cash Drawer Contents</h1>
	</div>
	<div class="pf-element pf-full-width" style="position: relative;">
		<?php foreach ($this->entity->currency as $key => $cur_denom) { ?>
		<div class="pf-element">
			<input class="pf-field ui-widget-content ui-corner-all entry" type="text" name="count_<?php echo htmlspecialchars($key); ?>" title="<?php echo htmlspecialchars($cur_denom); ?>" value="0" <?php echo $this->entity->final ? 'readonly="readonly"' : ''; ?> />
			x <span class="amount"><?php echo htmlspecialchars($this->entity->currency_symbol . $cur_denom); ?></span>
			<button class="pf-field ui-state-default ui-corner-all add_btn" type="button"><span class="amt_btn picon picon-list-add"></span></button>
			<button class="pf-field ui-state-default ui-corner-all remove_btn" type="button"><span class="amt_btn picon picon-list-remove"></span></button>
		</div>
		<?php } ?>
		<div class="ui-state-highlight ui-corner-all total">
			<div>Total in Till</div>
			<div id="p_muid_total_cashcount"></div>
		</div>
	</div>
	<?php } else { ?>
	<div class="pf-element pf-heading">
		<h1>Cash Drawer has been Cashed-Out</h1>
	</div>
	<?php
	$variance = $this->entity->total_out - $this->entity->float;
	$class = ($this->entity->total == $this->entity->total_out) ? 'ui-state-highlight' : 'ui-state-error';
	?>
	<div class="pf-element">
		<span class="pf-label">Expected Count</span>
		<span class="pf-field">$<?php echo htmlspecialchars($this->entity->total); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">- Actual Count</span>
		<span class="pf-field">$<?php echo htmlspecialchars($this->entity->total_out); ?></span>
		<hr />
	</div>
	<div class="pf-element <?php echo $class; ?> ui-corner-all" style="padding: .2em .5em;">
		<span class="pf-label">Error</span>
		<span class="pf-field">$<?php echo htmlspecialchars($this->entity->total - $this->entity->total_out); ?></span>
	</div>
	<div class="pf-element pf-heading">
		<h1>Totals</h1>
	</div>
	<div class="pf-element pf-full-width" style="position: relative; padding-bottom: 75px;">
		<div class="pf-element">
			<span class="pf-label">Actual Count</span>
			<span class="pf-field">$<?php echo htmlspecialchars($this->entity->total_out); ?></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">- Float</span>
			<span class="pf-field">$<?php echo htmlspecialchars($this->entity->float); ?></span>
			<hr />
		</div>
		<div class="pf-element ui-state-highlight ui-corner-all" style="padding: .2em .5em;">
			<span class="pf-label">Total Received Cash</span>
			<span class="pf-field">$<?php echo htmlspecialchars($variance); ?></span>
		</div>
		<div class="ui-state-highlight ui-corner-all total">
			<div>Cash Received</div>
			<div>$<?php echo htmlspecialchars($variance); ?></div>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="comments" <?php echo $this->entity->cashed_out ? 'readonly="readonly"' : ''; ?>><?php echo $this->entity->comments; ?></textarea></div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } if (!$this->entity->cashed_out) { ?>
		<input type="hidden" id="p_muid_save" name="save" value="" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Cash Out" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'cashcount/list')); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'cashcount/list')); ?>');" value="&laquo; Close" />
		<?php } ?>
	</div>
</form>