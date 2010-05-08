<?php
/**
 * Provides a form for the user to skim off of a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Skim from Cash Count ['.htmlentities($this->entity->cashcount->guid).'] at '.$this->entity->cashcount->group->name;
$this->note = 'Count the cash as you take it out of the drawer and place it in a deposit pouch.';

$denom_counter = 0;
?>
<style type="text/css" >
	/* <![CDATA[ */
	#skim_details .amount {
		padding-left: 10px;
		font-weight: bold;
	}
	#skim_details .amt_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#skim_details .entry {
		width: 50px;
	}
	#skim_details .total {
		border: goldenrod dashed 2px;
		font-weight: bold;
		font-size: 18pt;
		position: absolute;
		left: 50%;
		top: 25%;
		padding-top: 40px;
		padding-bottom: 50px;
		width: 300px;
		text-align: center;
	}
	/* Add and Remove Classes to show recent changes. */
	#skim_details .added {
		border: green solid 1px;
		color: green;
	}
	#skim_details .removed {
		border: red solid 1px;
		color: red;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	var multiply = new Array();
	var cash_symbol = "<?php echo $this->entity->cashcount->currency_symbol; ?>";
	
	pines(function(){
		// Update the cash count as money is counted.
		$("#skim_details .entry").change(function(){
			update_total();
		}).focus(function(){
			$(this).select();
		});

		update_total();
	});

	function update_total() {
		var total_count = 0;
		$("#skim_details .entry").each(function() {
			//This looks complicated but it simply multiplies the number of
			//bills/coins for each denomition by its respective value.
			//ex: 5 x 0.25 for 5 quarters that have been counted
			total_count += parseInt($(this).val()) * parseFloat(multiply[$(this).attr("name").replace(/.*(\d).*/, "$1")]);
			$(this).removeClass('added removed');
		});
		$("#total_skim").html(cash_symbol+total_count.toFixed(2));
	}

	function clear_all() {
		if (confirm("Clear all entered cash counts?")) {
			$("#skim_details .entry").each(function() { $(this).val(0); });
			update_total();
		}
		$("#skim_details [name=clear_btn]").blur();
	}

	function add_amount(type) {
		var current = parseInt($("#skim_details [name=count["+type+"]]").val());
		$("#skim_details [name=count["+type+"]]").val(current+1);
		$("#skim_details [name=count["+type+"]]").change();
		$("#skim_details [name=count["+type+"]]").addClass('added');
		$("#skim_details [name=add_btn["+type+"]]").blur();
	}

	function remove_amount(type) {
		var current = parseInt($("#skim_details [name=count["+type+"]]").val());
		if (current > 0) {
			$("#skim_details [name=count["+type+"]]").val(current-1);
			$("#skim_details [name=count["+type+"]]").change();
			$("#skim_details [name=count["+type+"]]").addClass('removed');
		}
		$("#skim_details [name=remove_btn["+type+"]]").blur();
	}

	function verify() {
		if (confirm("You will not be able to change this information, are you sure?"))
			$("#skim_details").submit();
	}
	// ]]>
</script>
<form class="pf-form" method="post" id="skim_details" action="<?php echo htmlentities(pines_url('com_sales', 'savecashcount_skim')); ?>">
	<?php if (!empty($this->entity->cashcount->review_comments)) {?>
	<div class="pf-element pf-heading">
		<h1>Reviewer Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-field"><?php echo $this->entity->cashcount->review_comments; ?></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Cash being <strong>Removed</strong> from Drawer<button class="ui-state-default ui-corner-all" type="button" name="clear_btn" onclick="clear_all()" style="margin-left: 50px;"><span>Clear All</span></button></h1>
	</div>
	<div class="pf-group">
		<div>
			<?php foreach ($this->entity->cashcount->currency as $cur_denom) { ?>
			<script type="text/javascript">
				// <![CDATA[
				multiply.push(<?php echo $cur_denom; ?>);
				// ]]>
			</script>
			<div class="pf-element pf-group">
				<input class="pf-field ui-widget-content entry" type="text" name="count[<?php echo $denom_counter; ?>]" value="<?php echo '0'; ?>" />
				<button class="pf-field ui-state-default ui-corner-all" type="button" name="add_btn[<?php echo $denom_counter; ?>]" onclick="add_amount('<?php echo $denom_counter; ?>');"><span class="amt_btn picon_16x16_list-add"></span></button>
				<button class="pf-field ui-state-default ui-corner-all" type="button" name="remove_btn[<?php echo $denom_counter; ?>]" onclick="remove_amount('<?php echo $denom_counter; ?>');"><span class="amt_btn picon_16x16_list-remove"></span></button>
				<span class="label amount"><?php echo $this->entity->cashcount->currency_symbol . $cur_denom; ?></span>
			</div>
			<?php $denom_counter++; } ?>
		</div>
		<div>
			<div class="total ui-corner-all">
				<span>Skim Total</span><br/>
				<span id="total_skim"></span>
			</div>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-field"><textarea style="width: 98%;" rows="3" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea></span>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo $this->entity->cashcount->guid; ?>" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all submit_button" type="button" value="Finish Skim" onclick="verify();" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'listcashcounts')); ?>');" value="Cancel" />
	</div>
</form>