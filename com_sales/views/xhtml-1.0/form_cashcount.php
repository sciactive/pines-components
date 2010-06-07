<?php
/**
 * Provides a form for the user to edit a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'New Cash Count' : (($this->entity->final) ? 'Viewing' : 'Editing').' Float for Cash Count ['.htmlentities($this->entity->guid).']';
if (isset($this->entity->guid))
	$this->note = 'Created by ' . $this->entity->user->name . ' on ' . date('Y-m-d', $this->entity->p_cdate) . ' - Last Modified on ' . date('Y-m-d', $this->entity->p_mdate);

$denom_counter = 0;
?>
<style type="text/css" >
	/* <![CDATA[ */
	#cashcount_details .amount {
		padding-left: 10px;
		font-weight: bold;
	}
	#cashcount_details .amt_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#cashcount_details .entry {
		width: 50px;
	}
	#cashcount_details .total {
		border: cornflowerblue dashed 2px;
		font-weight: bold;
		font-size: 18pt;
		position: absolute;
		left: 50%;
		top: 25%;
		padding-top: 50px;
		padding-bottom: 50px;
		width: 300px;
		text-align: center;
	}
	/* Add and Remove Classes to show recent changes. */
	#cashcount_details .added {
		border: green solid 1px;
		color: green;
	}
	#cashcount_details .removed {
		border: red solid 1px;
		color: red;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	
	var multiply = new Array();
	var cash_symbol = "<?php echo $this->entity->currency_symbol; ?>";
	
	pines(function(){
		// Update the cash count as money is counted.
		$("#cashcount_details .entry").change(function(){
			update_total();
		}).focus(function(){
			$(this).select();
		});

		update_total();
	});

	function update_total() {
		var total_count = 0;
		$("#cashcount_details .entry").each(function() {
			//This looks complicated but it simply multiplies the number of
			//bills/coins for each denomition by its respective value.
			//ex: 5 x 0.25 for 5 quarters that have been counted
			total_count += parseInt($(this).val()) * parseFloat(multiply[$(this).attr("name").replace(/.*(\d).*/, "$1")]);
			$(this).removeClass('added removed');
		});
		$("#total_cashcount").html(cash_symbol+total_count.toFixed(2));
	}

	function clear_all() {
		if (confirm("Clear all entered cash counts?")) {
			$("#cashcount_details .entry").each(function() { $(this).val(0); });
			update_total();
		}
		$("#cashcount_details [name=clear_btn]").blur();
	}

	function add_amount(type) {
		var current = parseInt($("#cashcount_details [name=count["+type+"]]").val());
		$("#cashcount_details [name=count["+type+"]]").val(current+1);
		$("#cashcount_details [name=count["+type+"]]").change();
		$("#cashcount_details [name=count["+type+"]]").addClass('added');
		$("#cashcount_details [name=add_btn["+type+"]]").blur();
	}
	function remove_amount(type) {
		var current = parseInt($("#cashcount_details [name=count["+type+"]]").val());
		if (current > 0) {
			$("#cashcount_details [name=count["+type+"]]").val(current-1);
			$("#cashcount_details [name=count["+type+"]]").change();
			$("#cashcount_details [name=count["+type+"]]").addClass('removed');
		}
		$("#cashcount_details [name=remove_btn["+type+"]]").blur();
	}
	// ]]>
</script>
<form class="pf-form" method="post" id="cashcount_details" action="<?php echo htmlentities(pines_url('com_sales', 'savecashcount')); ?>">
	<?php if (!empty($this->entity->review_comments)) {?>
	<div class="pf-element pf-heading">
		<h1>Reviewer Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-field"><?php echo $this->entity->review_comments; ?></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h1>Cash Drawer Contents<?php if (!$this->entity->final) { ?><button class="ui-state-default ui-corner-all" type="button" name="clear_btn" onclick="clear_all()" style="margin-left: 50px;"><span>Clear All</span></button><?php } ?></h1>
	</div>
	<div class="pf-group">
		<div>
			<?php foreach ($this->entity->currency as $cur_denom) { ?>
			<script type="text/javascript">
				// <![CDATA[
				multiply.push(<?php echo $cur_denom; ?>);
				// ]]>
			</script>
			<div class="pf-element pf-group">
				<input class="pf-field ui-widget-content entry" type="text" name="count[<?php echo $denom_counter; ?>]" value="<?php echo $this->entity->count[$denom_counter] ? $this->entity->count[$denom_counter] : '0'; ?>" <?php echo $this->entity->final ? "readonly='readonly'" : ""; ?>/>
				<?php if (!$this->entity->final) { ?>
				<button class="pf-field ui-state-default ui-corner-all" type="button" name="add_btn[<?php echo $denom_counter; ?>]" onclick="add_amount('<?php echo $denom_counter; ?>');"><span class="amt_btn picon picon_16x16_list-add"></span></button>
				<button class="pf-field ui-state-default ui-corner-all" type="button" name="remove_btn[<?php echo $denom_counter; ?>]" onclick="remove_amount('<?php echo $denom_counter; ?>');"><span class="amt_btn picon picon_16x16_list-remove"></span></button>
				<?php } ?>
				<span class="amount"><?php echo $this->entity->currency_symbol . $cur_denom; ?></span>
			</div>
			<?php $denom_counter++; } ?>
		</div>
		<div>
			<div class="total ui-corner-all">
				<span>Float Total</span><br/>
				<span id="total_cashcount"></span>
			</div>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h1>Comments</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea class="ui-widget-content" style="width: 100%;" rows="3" cols="35" name="comments" <?php echo $this->entity->final ? "readonly='readonly'" : ""; ?>><?php echo $this->entity->comments; ?></textarea></div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } if (!$this->entity->final) { ?>
		<input type="hidden" name="save" value="" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#cashcount_details input[name=save]').val('save');" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Commit" onclick="$('#cashcount_details input[name=save]').val('commit');" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'listcashcounts')); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'listcashcounts')); ?>');" value="&laquo; Close" />
		<?php } ?>
	</div>
</form>