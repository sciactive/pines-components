<?php
/**
 * Display a form to make a payment.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

switch ($this->entity->payment_frequency) {
	case "12":
		$payment_frequency = "Monthly";
		break;
	case "1":
		$payment_frequency = "Annually";
		break;
	case "2":
		$payment_frequency = "Semi-annually";
		break;
	case "4":
		$payment_frequency = "Quarterly";
		break;
	case "6":
		$payment_frequency = "Bi-monthly";
		break;
	case "24":
		$payment_frequency = "Semi-monthly";
		break;
	case "26":
		$payment_frequency = "Bi-weekly";
		break;
	case "52":
		$payment_frequency = "Weekly";
		break;
}
?>
<script type="text/javascript">
	pines(function(){
		// Creation date Date Picker.
		var form = $('#p_muid_form');
		var payment_amount_input = form.find('[name=payment_amount]');
		var payment_date_input = form.find("[name=payment_date_input]");
		var payment_due_button = form.find('.payment-button');
		payment_date_input.datepicker({
			changeMonth: true,
			changeYear: true,
			yearRange: '-5:+5',
			dateFormat: 'yy-mm-dd',
			maxDate: '+1d'
		});
		payment_due_button.click(function(){
			payment_amount_input.val($(this).text());
		});
		payment_amount_input.keypress(function(e){
			if (e.keyCode == 13)
				$(this).closest(".ui-dialog-content").dialog("option", "buttons")["Make Payment"]();
		});
	});
</script>
<form class="pf-form" id="p_muid_form" action="">
	<div class="pf-element pf-full-width">
		<div class="pf-label" style="width:100%;">
			<span><?php echo (!empty($this->entity->past_due)) ? 'Past Due: <span style="float:right;"><span style="color:#B30909;">$'.htmlspecialchars($pines->com_sales->round($this->entity->past_due, true)).'</span></span><br/>' : ''; ?></span>
			<span><?php echo $payment_frequency; ?> Payment: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true)); ?></span></span><br/>
			<span>Fees: <span style="float:right;"><?php echo ($this->entity->payment_fees) ? htmlspecialchars($this->entity->payment_fees) : '$0.00'; ?></span></span><br/>
			<span>Adjustments: <span style="float:right;"><?php echo ($this->entity->payment_adjustments) ? htmlspecialchars($this->entity->payment_adjustments) : '$0.00'; ?></span></span><br/>
			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
			<span style="font-size:1.5em;">Payment Due: <?php echo ($this->entity->past_due > 0) ? '<span class="btn-danger pull-right btn payment-button">$'.htmlspecialchars($pines->com_sales->round($this->entity->balance, true)).'</span>' : '<span class="btn-success pull-right btn payment-button">$'.htmlspecialchars($pines->com_sales->round($this->entity->balance, true)).'</span>'; ?></span><br/>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Enter Payment Amount
			<span class="pf-note">Payment Due: <?php echo (isset($this->entity->payments[0]['next_payment_due'])) ? htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")) : htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></span>
		</span>
		<span class="pf-field">
			<input style="float:right;text-align:right;" class="pf-field" type="text" name="payment_amount" />
		</span>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Payment Date:
			<span class="pf-note">Date of payment received.</span>
		</span>
		<span class="pf-field">
			<input value="<?php echo htmlspecialchars(date('Y-m-d')); ?>" style="float:right;text-align:right;" class="pf-field" type="text" name="payment_date_input" />
		</span>
	</div>
</form>