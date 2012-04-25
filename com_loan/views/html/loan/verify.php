<?php
/**
 * Verifies a loan and displays an amortization schedule.
 *
 * @package Components
 * @subpackage loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Loan Overview';
?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h3>Summary</h3>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-label" style="width:280px;margin-right:60px;">
			<span style="font-weight:bold;">Principal Amount: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></span></span><br/>
			<span style="font-weight:bold;">APR: <span style="float:right;"><?php echo htmlspecialchars($this->entity->apr).'%'; ?></span></span><br/>
			<span style="font-weight:bold;">Term: <span style="float:right;"><?php echo htmlspecialchars($this->entity->term); echo " ".htmlspecialchars($this->entity->term_type); ?></span></span><br/>
			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>

			<span>First Payment: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></span></span><br/>
			<span>Payment Frequency: <span style="float:right;">
				<?php
				switch ($this->entity->payment_frequency) {
					case "12":
						echo "Monthly";
						$payment_frequency = "Monthly";
						break;
					case "1":
						echo "Annually";
						$payment_frequency = "Annually";
						break;
					case "2":
						echo "Semi-annually";
						$payment_frequency = "Semi-annually";
						break;
					case "4":
						echo "Quarterly";
						$payment_frequency = "Quarterly";
						break;
					case "6":
						echo "Bi-monthly";
						$payment_frequency = "Bi-monthly";
						break;
					case "24":
						echo "Semi-monthly";
						$payment_frequency = "Semi-monthly";
						break;
					case "26":
						echo "Bi-weekly";
						$payment_frequency = "Bi-weekly";
						break;
					case "52":
						echo "Weekly";
						$payment_frequency = "Weekly";
						break;
				}
				?>
			</span></span><br/>
			<span>Compound Frequency: <span style="float:right;">
				<?php
				switch ($this->entity->compound_frequency) {
					case "12":
						echo "Monthly";
						break;
					case "1":
						echo "Annually";
						break;
					case "2":
						echo "Semi-annually";
						break;
					case "4":
						echo "Quarterly";
						break;
					case "6":
						echo "Bi-monthly";
						break;
					case "24":
						echo "Semi-monthly";
						break;
					case "26":
						echo "Bi-weekly";
						break;
					case "52":
						echo "Weekly";
						break;
				}
				?>
				</span></span><br/>
			<span>Payment Type: <span style="float:right;">
				<?php
				switch ($this->entity->payment_type) {
					case "0":
						echo "End of Period";
						break;
					case "1":
						echo "Beginning of Period";
						break;
				}
				?>
				</span></span>
			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
		</div>
		<div class="pf-group" style="width:280px;margin-left:0px !important;float:left;">
			<span>Rate (Per Period): <span style="float:right;"><?php echo htmlspecialchars(round($this->entity->rate_per_period, 3)).'%'; ?></span></span><br/>
			<span>Payments Scheduled: <span style="float:right;"><?php echo htmlspecialchars($this->entity->number_payments); ?></span></span><br/>
			<span>Total Payments: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_payment_sum); ?></span></span><br/>
			<span>Total Interest: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_interest_sum); ?></span></span><br/>
			<span>Est. Interest Savings: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->est_interest_savings, true)); ?></span></span><br/><br/>

			<div style="border:1px solid #ccc; padding:3px;"><span style="font-weight:bold;font-size:1.1em;"><?php echo $payment_frequency;?> Payment: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->frequency_payment, true)); ?></span></span></div><br/>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h3>Amortization Schedule</h3>
	</div>
	<div class="pf-element pf-full-width" style="overflow: auto;">
		<table class="table" style="min-width:100%;font-size:.8em;">
			<thead>
				<tr>
					<th>Payment Due Date</th>
					<th>Payment</th>
					<th>Interest Payment</th>
					<th>Principal Payment</th>
					<th>Balance</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="4">Principal Balance</td>
					<td>$<?php echo htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></td>
				</tr>
				<?php foreach ($this->entity->schedule as $schedule) { ?>
				<tr>
					<td><?php echo htmlspecialchars(format_date($schedule['scheduled_date_expected'], "date_short")); ?></td>
					<td><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['payment_amount_expected'], true)); ?></td>
					<td><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['payment_interest_expected'], true)); ?></td>
					<td><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['payment_principal_expected'], true)); ?></td>
					<td><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['scheduled_balance'], true)); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-heading">
		<h3>Verify</h3>
		<p>Is this information correct?</p>
	</div>
	<div class="pf-element pf-buttons">
		<form class="pf-form" method="post" id="p_muid_loan_form" action="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/save')); ?>">
			<input type="hidden" name="customer" value="<?php echo htmlspecialchars($this->entity->customer->guid); ?>" />
			<input type="hidden" name="creation_date" value="<?php echo htmlspecialchars(format_date($this->entity->creation_date, "date_sort")); ?>" />
			<input type="hidden" name="principal" value="<?php echo htmlspecialchars($this->entity->principal); ?>" />
			<input type="hidden" name="apr" value="<?php echo htmlspecialchars($this->entity->apr); ?>" />
			<input type="hidden" name="apr_correct" value="ON" />
			<input type="hidden" name="term" value="<?php echo htmlspecialchars($this->entity->term); ?>" />
			<input type="hidden" name="term_type" value="<?php echo htmlspecialchars($this->entity->term_type); ?>" />
			<input type="hidden" name="first_payment_date" value="<?php echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_sort")); ?>" />
			<input type="hidden" name="payment_frequency" value="<?php echo htmlspecialchars($this->entity->payment_frequency); ?>" />
			<input type="hidden" name="compound_frequency" value="<?php echo htmlspecialchars($this->entity->compound_frequency); ?>" />
			<input type="hidden" name="payment_type" value="<?php echo ($this->entity->payment_type == 0) ? 'ending' : 'beginning'; ?>" />
			<?php if ( isset($this->entity->guid) ) { ?>
			<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
			<?php } ?>
			<input id="p_muid_loan_process_type" type="hidden" name="loan_process_type" value="none" />
			<input class="pf-button btn btn-primary" type="button" onclick="$('#p_muid_loan_process_type').val('submit'); $('#p_muid_loan_form').submit();" value="Yes, Continue." />
			<input class="pf-button btn" type="button" onclick="$('#p_muid_loan_process_type').val('go_back'); $('#p_muid_loan_form').submit();" value="No, Go Back." />
		</form>
	</div>
</div>