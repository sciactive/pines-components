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
	#p_muid_form .amount {
		padding-left: 10px;
		font-weight: bold;
	}
	#p_muid_form .amt_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#p_muid_form .entry {
		width: 50px;
	}
	#p_muid_form .total {
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
	
	var multiply = new Array();
	var cash_symbol = "<?php echo $this->entity->currency_symbol; ?>";
	
	pines(function(){
		// Update the cash count as money is counted.
		$("#p_muid_form .entry").change(function(){
			update_total();
		}).focus(function(){
			$(this).select();
		});

		update_total();
	});

	function update_total() {
		var total_count = 0;
		$("#p_muid_form .entry").each(function() {
			//This looks complicated but it simply multiplies the number of
			//bills/coins for each denomition by its respective value.
			//ex: 5 x 0.25 for 5 quarters that have been counted
			total_count += parseInt($(this).val()) * parseFloat(multiply[$(this).attr("name").replace(/.*(\d).*/, "$1")]);
			$(this).removeClass('added removed');
		});
		$("#p_muid_total_cashcount").html(cash_symbol+total_count.toFixed(2));
	}

	function clear_all() {
		if (confirm("Clear all entered cash counts?")) {
			$("#p_muid_form .entry").each(function() { $(this).val(0); });
			update_total();
		}
		$("#p_muid_form [name=clear_btn]").blur();
	}

	function add_amount(type) {
		var current = parseInt($("#p_muid_form [name=count["+type+"]]").val());
		$("#p_muid_form [name=count["+type+"]]").val(current+1);
		$("#p_muid_form [name=count["+type+"]]").change();
		$("#p_muid_form [name=count["+type+"]]").addClass('added');
		$("#p_muid_form [name=add_btn["+type+"]]").blur();
	}
	function remove_amount(type) {
		var current = parseInt($("#p_muid_form [name=count["+type+"]]").val());
		if (current > 0) {
			$("#p_muid_form [name=count["+type+"]]").val(current-1);
			$("#p_muid_form [name=count["+type+"]]").change();
			$("#p_muid_form [name=count["+type+"]]").addClass('removed');
		}
		$("#p_muid_form [name=remove_btn["+type+"]]").blur();
	}
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_sales', 'cashcount/save')); ?>">
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
				<button class="pf-field ui-state-default ui-corner-all" type="button" name="add_btn[<?php echo $denom_counter; ?>]" onclick="add_amount('<?php echo $denom_counter; ?>');"><span class="amt_btn picon picon-list-add"></span></button>
				<button class="pf-field ui-state-default ui-corner-all" type="button" name="remove_btn[<?php echo $denom_counter; ?>]" onclick="remove_amount('<?php echo $denom_counter; ?>');"><span class="amt_btn picon picon-list-remove"></span></button>
				<?php } ?>
				<span class="amount"><?php echo $this->entity->currency_symbol . $cur_denom; ?></span>
			</div>
			<?php $denom_counter++; } ?>
		</div>
		<div>
			<div class="total ui-corner-all">
				<span>Float Total</span><br/>
				<span id="p_muid_total_cashcount"></span>
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
		<input type="hidden" id="p_muid_save" name="save" value="" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" onclick="$('#p_muid_save').val('save');" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Commit" onclick="$('#p_muid_save').val('commit');" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'cashcount/list')); ?>');" value="Cancel" />
		<?php } else { ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'cashcount/list')); ?>');" value="&laquo; Close" />
		<?php } ?>
	</div>
</form>