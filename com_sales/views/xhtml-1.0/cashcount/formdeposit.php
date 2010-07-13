<?php
/**
 * Provides a form for the user to deposit skims from a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Deposit from Cash Count ['.$this->entity->cashcount->guid.'] at '.$this->entity->cashcount->group->name;
$this->note = 'Count the cash as you put it into the drawer.';
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
		var cash_symbol = "<?php echo $this->entity->cashcount->currency_symbol; ?>";

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
			$("#p_muid_total_deposit").html(cash_symbol+total_count.toFixed(2));
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

		pines.com_sales_verify = function(){
			if (confirm("You will not be able to change this information, are you sure?"))
				$("#p_muid_form").submit();
		};

		update_total();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_sales', 'cashcount/savedeposit')); ?>">
	<?php if (!empty($this->entity->cashcount->review_comments)) {?>
	<div class="pf-element pf-heading">
		<h1>Reviewer Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-field"><?php echo $this->entity->cashcount->review_comments; ?></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<button class="ui-state-default ui-corner-all clear_btn" type="button" style="display: block; float: right;">Clear All</button>
		<h1>Cash being <strong>Deposited</strong> into Drawer</h1>
	</div>
	<div class="pf-element pf-full-width" style="position: relative;">
		<?php foreach ($this->entity->cashcount->currency as $key => $cur_denom) { ?>
		<div class="pf-element">
			<input class="pf-field ui-widget-content entry" type="text" name="count_<?php echo $key; ?>" title="<?php echo $cur_denom; ?>" value="0" />
			x <span class="amount"><?php echo $this->entity->cashcount->currency_symbol . $cur_denom; ?></span>
			<button class="pf-field ui-state-default ui-corner-all add_btn" type="button"><span class="amt_btn picon picon-list-add"></span></button>
			<button class="pf-field ui-state-default ui-corner-all remove_btn" type="button"><span class="amt_btn picon picon-list-remove"></span></button>
		</div>
		<?php } ?>
		<div class="ui-state-highlight ui-corner-all total">
			<div>Deposit Total</div>
			<div id="p_muid_total_deposit"></div>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea class="ui-widget-content" style="width: 100%;" rows="3" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea></div>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo $this->entity->cashcount->guid; ?>" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all submit_button" type="button" value="Finish Deposit" onclick="pines.com_sales_verify();" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'cashcount/list')); ?>');" value="Cancel" />
	</div>
</form>