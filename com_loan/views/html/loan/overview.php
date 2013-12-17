<?php
/**
 * Overviews a loan and displays an amortization schedule.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Loan Overview';
$this->note =  'Customer: '.htmlspecialchars($this->entity->customer->name).'<span style="float:right;"> Loan ID: '.htmlspecialchars($this->entity->id).'</span>';
?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h3>Summary</h3>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-label" style="width:280px !important;margin-right:60px;margin-left:5px;">
			<span style="font-weight:bold;">Principal Amount: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></span></span><br/>
			<span style="font-weight:bold;">APR: <span style="float:right;"><?php echo htmlspecialchars($this->entity->apr).'%'; ?></span></span><br/>
			<span style="font-weight:bold;">Term: <span style="float:right;"><?php echo htmlspecialchars($this->entity->term); echo " ".htmlspecialchars($this->entity->term_type); ?></span></span><br/>
			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>

			<?php if($this->entity->missed_first_payment && !(!empty($this->entity->paid))) {
				?>
				<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></span></span><br/>
				<span>First Payment Missed: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['first_payment_missed'], "date_short")); ?></span></span><br/>
				<span>Last Payment Made: <span style="float:right;"><span style="color:#b30909;"><?php echo htmlspecialchars($this->entity->payments[0]['last_payment_made']); ?></span></span></span><br/>
				<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")); ?></span></strong></span><br/>
				<?php
			} elseif (!empty($this->entity->paid)) {
				?>
				<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></span></span><br/>
				<span>First Payment Made: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['first_payment_made'], "date_short")); ?></span></span><br/>
				<span>Last Payment Made: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['last_payment_made'], "date_short")); ?></span></span><br/>
				<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")); ?></span></strong></span><br/>
				<?php
			} else {
				?>
				<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></span></span><br/>
				<span>Last Payment Made: <span style="float:right;">n/a</span></span><br/>
				<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></span></strong></span><br/>
				<?php
			}
			?>
			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>

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
					htmlspecialchars($this->entity->payment_type);
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
			<span>Rate (Per Period): <span style="float:right;"><?php echo htmlspecialchars(round($this->entity->rate_per_period, 3)).'%'; ?></span></span><br/>
			<span>Payments Scheduled: <span style="float:right;"><?php echo htmlspecialchars($this->entity->number_payments); ?></span></span><br/>
			<span>Total Payments: <span style="float:right;"><?php echo ($this->entity->new_total_payment_sum) ? '$'.htmlspecialchars($pines->com_sales->round($this->entity->new_total_payment_sum)) : '$'.htmlspecialchars($pines->com_sales->round($this->entity->total_payment_sum)); ?></span></span><br/>

			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
			<span>Remaining Payments: <span style="float:right;">
					<?php
					echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars(round($this->entity->payments[0]['remaining_payments'], 2)) : htmlspecialchars($this->entity->number_payments);
				?>
			</span></span><br/>
			<span>Remaining Payment Due Dates: <span style="float:right;">
					<?php
					echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars($this->entity->payments[0]['remaining_payments_due']) : htmlspecialchars($this->entity->number_payments);
				?>
			</span></span><br/>
			<span>Percentage Principal Paid: <span style="float:right;">
					<?php
					echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['percentage_paid'], true)).'%' : '0.00%';
				?>
			</span></span>
			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
		</div>

		<div class="pf-group" style="width:280px !important;margin-left:5px !important;float:left;margin-right:60px !important;">
			<span>Total Initial Finance Charges: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_interest_sum_original); ?></span></span><br/>
			<span>Total Fees & Adjustments: <span style="float:right;"><?php echo (isset($this->entity->total_fees_adjustments)) ? '$'.htmlspecialchars($this->entity->total_fees_adjustments) : '$0.00'; ?></span></span><br/>
			<span>Total Current Finance Charges: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_interest_sum); ?></span></span><br/>
			<span>Est. Interest Savings: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->est_interest_savings, true)); ?></span></span><br/><br/>

			<div style="border:1px solid #ccc; padding:3px;"><span style="font-weight:bold;font-size:1.1em;"><?php echo $payment_frequency; ?> Payment: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->frequency_payment); ?></span></span></div><br/>
			<div style="border:1px solid #ccc; padding:3px;"><span style="font-weight:bold;font-size:1.1em;">Remaining Balance: <span style="float:right;"><?php echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? '$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['remaining_balance'], true)) : "$".htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></span></span></div><br/>

			<div>
				<span style="font-weight:bold;font-size:1.1em;">
					<?php if (isset($this->entity->payments[0]['sum_payment_short'])) { ?>
					<span style="font-size:.8em;">
						* Short Amount:
						<span style="float:right;">
							<?php
								echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['sum_payment_short'], true));
							?>
						</span>
					</span>
					<?php } ?>
					<?php
						if ($pines->com_sales->round($this->entity->payments[0]['unpaid_balance'] >= 0.01)) {
					?>
					<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
					<span style="font-size:.9em;">Unpaid Balance: </span>
					<span style="float:right;font-size:.9em;">
						<?php
							echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['unpaid_balance'], true));
						?>
					</span>
					<?php
						}
					?>
					<?php
						if ($this->entity->payments[0]['unpaid_interest'] > 0) {
					?>
					<span style="font-size:.9em;"><br/>Unpaid Interest:</span>
					<span style="float:right;font-size:.9em;">
						<?php
							echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['unpaid_interest'], true));
						?>
					</span>
					<?php
						}
					?>
					<?php
						if ($pines->com_sales->round($this->entity->payments[0]['past_due'] >= 0.01)) {
					?>
					<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
					<span style="color:#b30909;">Past Due:</span>
					<span style="float:right;color:#b30909;">
						<?php
							echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true));
						?>
					</span>
					<?php
						}
					?>
					<div class="alert clearfix <?php echo ($this->entity->payments[0]['past_due'] >= 0.01) ? 'alert-error' : 'alert-info'; ?>" style="margin-top:10px;">
						Next Payment:
						<span style="float:right;">
							<?php
							if ($this->entity->payments[0]['past_due'] >= 0.01) {
								?>
								<script type="text/javascript">
									pines(function(){
										$("#p_muid_next_payment").popover({
											trigger: 'hover',
											title: 'Next Payment Due: <span style="float:right;">$'+<?php echo json_encode(htmlspecialchars((string) ($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount']) + $pines->com_sales->round($this->entity->payments[0]['past_due'])))); ?>+'</span>',
											content: 'Past Due: <span style="float:right;">$'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true)));?>+'</span><br/>'+<?php echo json_encode(htmlspecialchars($payment_frequency));?>+' Payment: <span style="float:right;">$'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true))); ?>+'</span><br/><span style="font-size:.8em;">Past Due Amount is due immediately.</span>',
											placement: 'right'
										});
									});
								</script>
								<?php
							}
							echo ($this->entity->payments[0]['past_due'] >= 0.01) ? '<span style="cursor:pointer;" id="p_muid_next_payment">$'.(htmlspecialchars($pines->com_sales->round(($this->entity->payments[0]['next_payment_due_amount'] + $pines->com_sales->round($this->entity->payments[0]['past_due'])), true))).'</span>' : "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true));
							?>
						</span><br/>
						<span style="float:left;background:none; border:none;font-size:.8em;">Due:</span>
						<span style="float:right;background:none; border:none;font-size:.8em;"> <?php echo (isset($this->entity->payments[0]['next_payment_due'])) ? htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")) : htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></span>
						<span style="clear:both;float:left;background:none; border:none;font-size:.6em;"> <?php echo ($pines->com_sales->round($this->entity->payments[0]['past_due'] >= .01)) ? "Past Due Amount is due immediately." : ""; ?></span>
					</div>
				</span>
			</div>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h3>Payments</h3>
	</div>
	<div class="pf-element pf-full-width" style="overflow: auto;">
		<table class="table" style="min-width:100%;font-size:.8em;">
			<thead>
				<tr>
					<th>Payment Type</th>
					<th>Payment Due</th>
					<th>Payment Received</th>
					<th>Payment Status</th>
					<th>Payment</th>
					<th>Additional Payment</th>
					<th>Interest Payment</th>
					<th>Principal Payment</th>
					<th>Remaining Balance</th>
					<th>Scheduled Balance</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="9">Principal Balance</td>
					<td>$<?php echo htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></td>
				</tr>
				<?php
				$first_one = false;
				foreach ($this->entity->payments as $payment) {
					if (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) {
						if ($first_one == false) {
							$first_one = true;
							continue;
						}
					}
					if (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) {
						if ($payment['scheduled_date_expected'] == $this->entity->payments[0]['next_payment_due']) {
							?><tr class="alert-info"><?php
						} else {
							?><tr><?php
						}
					} else {
						// No payments made or due yet, so highlighted row should be the first one.
						if ($payment['scheduled_date_expected'] == $this->entity->first_payment_date) {
							?><tr class="alert-info"><?php
						} else {
							?><tr><?php
						}

					}
					?>
					<td>
						<?php
						switch ($payment['payment_type']) {
							case "past_due":
								echo '<span style="color:#0a2aab;">Past Due</span>';
								break;
							case "none":
								echo '<span style="color:#B30909;">Missed</span>';
								break;
							case "":
								echo '<span>Scheduled</span>';
								break;
							default:
								echo htmlspecialchars(ucwords($payment['payment_type']));
						}
						?>
					</td>
					<td><?php echo (isset($payment['payment_date_expected'])) ? htmlspecialchars(format_date($payment['payment_date_expected'], "date_short")) : htmlspecialchars(format_date($payment['scheduled_date_expected'], "date_short")); ?></td>
					<td>
						<?php
						if (isset($payment['payment_date_received'])) {
							if ($payment['extra_payments']) {
								echo htmlspecialchars(format_date($payment['payment_date_received'], "date_short"));
								foreach ($payment['extra_payments'] as $extra_payment) {
									echo "<br/>".htmlspecialchars(format_date($extra_payment['payment_date_received'], "date_short"));
								}
							} else
								echo htmlspecialchars(format_date($payment['payment_date_received'], "date_short"))."<br/>";
						}
						?>
					</td>
					<td>
						<?php
						switch (htmlspecialchars($payment['payment_status'])) {
							case "not due yet":
								echo '<span class="picon-view-calendar-day" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Not Due Yet</span>';
								break;
							case "paid":
								echo '<span class="picon-task-complete" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span>';
								break;
							case "paid_late":
								echo '<span class="picon-task-accepted" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span><br/><span style="display:inline-block;padding-left:18px;">'.htmlspecialchars($payment['payment_days_late']); echo ($payment['payment_days_late'] > 1 ? " days late" : " day late")."</span>";
								break;
							case "partial_not_due":
								echo '<span class="picon-task-recurring" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span>';
								break;
							case "partial":
								echo '<span class="picon-task-attempt" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment<br/>'.htmlspecialchars($payment['payment_days_late']); if(!empty($payment['payment_days_late'])) echo ($payment['payment_days_late'] > 1 ? " days late" : " day late"); echo "</span>";
								break;
							case "missed":
								echo '<span class="picon-task-reject" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Missed Payment<br/> '.htmlspecialchars($payment['payment_days_late']); echo ($payment['payment_days_late'] > 1 ? " days late" : " day late")."</span>";
								break;
						}
						?>
					</td>
					<td style="text-align:right;">
						<?php
						if (!empty($payment['extra_payments'])) {
							echo "$".htmlspecialchars($pines->com_sales->round($payment['payment_amount_paid_orig'], true));
							foreach ($payment['extra_payments'] as $extra_payment) {
								echo "<br/>$".htmlspecialchars($pines->com_sales->round(($extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'] + $extra_payment['payment_additional']), true));
							}
						} elseif ($payment['payment_status'] != "not due yet" && $payment['payment_short'] > 0)
							echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_amount_paid'], true)).'<span style="padding-left:3px;">*</span>';
						else
							echo "$".htmlspecialchars($pines->com_sales->round($payment['payment_amount_paid'], true));
						?>
					</td>
					<td>
						<?php
						if (!empty($payment['extra_payments'])) {
							echo ($pines->com_sales->round($payment['payment_additional_orig']) > 0) ? "$".htmlspecialchars($pines->com_sales->round($payment['payment_additional_orig'], true)) : "&nbsp;";
							foreach ($payment['extra_payments'] as $extra_payment) {
								echo ($pines->com_sales->round($extra_payment['payment_additional']) > 0) ? "<br/>$".htmlspecialchars($pines->com_sales->round(($extra_payment['payment_additional']), true)) : "<br/>&nbsp;";
							}
						} elseif ($payment['payment_status'] != "not due yet" && ($payment['payment_principal_expected'] + $payment['payment_interest_expected']) < $pines->com_sales->round($payment['payment_amount_paid']))
							echo ($pines->com_sales->round($payment['payment_additional']) > 0) ? '$'.htmlspecialchars($pines->com_sales->round($payment['payment_additional'], true)) : "&nbsp;";
						?>
					</td>
					<td>
						<?php
						if (($payment['payment_interest_expected'] - $payment['payment_interest_paid']) > 0 && $payment['payment_status'] != 'not due yet' && $payment['payment_status'] != 'partial_not_due') {
							// I need a tooltip to show unpaid interest and expected interest.
							$uniq2 = uniqid();
							?>
							<script type="text/javascript">
								pines(function(){
									$("#p_muid_tooltip_<?php echo htmlspecialchars($uniq2); ?>").popover({
										trigger: 'hover',
										title: 'Unpaid Interest: $'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($payment['payment_interest_unpaid'], true))); ?>,
										content: 'Expected Interest: <span style="float:right;">$'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($payment['payment_interest_expected'], true))); ?>+'</span><br/>Interest Paid: <span style="float:right;">$'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid'], true))); ?>+' </span><br/><span style="font-size:.8em;">Interest is calculated based on the terms of the loan at the time of payment.</span>',
										placement: "right",
										html: true
									});
								});
							</script>
							<?php

							if (!empty($payment['extra_payments'])) {
								echo '<span style="cursor:pointer;color:#b30909" id="p_muid_tooltip_'.htmlspecialchars($uniq2).'">$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid_orig'], true)).'</span>';
								foreach ($payment['extra_payments'] as $extra_payment) {
									echo ($pines->com_sales->round($extra_payment['payment_interest_paid']) > 0) ? "<br/>$".htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'], true)) : "<br/>-";
								}
							} else
								echo '<span style="cursor:pointer;color:#b30909" id="p_muid_tooltip_'.htmlspecialchars($uniq2).'">$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid'], true)).'</span>';
						} else {
							if (!empty($payment['extra_payments'])) {
								echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid_orig'], true));
								foreach ($payment['extra_payments'] as $extra_payment) {
									echo ($pines->com_sales->round($extra_payment['payment_interest_paid']) > 0) ? "<br/>$".htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'], true)) : "<br/>-";
								}
							} else
								echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid'], true));
						}
						?>
					</td>
					<td>
						<?php
						if (!empty($payment['extra_payments'])) {
							echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_principal_paid_orig'], true));
							foreach ($payment['extra_payments'] as $extra_payment) {
								echo "<br/>$".htmlspecialchars($pines->com_sales->round($extra_payment['payment_principal_paid'], true));
							}
						} else
							echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_principal_paid'], true));
						?>
					</td>

					<?php if (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) { ?>
					<td>
						<?php
						if ($pines->com_sales->round($payment['payment_balance_unpaid']) >= .01 && $payment['payment_status'] != 'partial_not_due') {
							// showing tooltip to show unpaid balance specific to this payment.
							// javascript to control tooltip:
							$uniq = uniqid();
							?>
							<script type="text/javascript">
								pines(function(){
									$("#p_muid_tooltip_<?php echo htmlspecialchars($uniq); ?>").popover({
										trigger: 'hover',
										title: 'Unpaid Balance: $'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($payment['payment_balance_unpaid'], true))); ?>,
										content: 'Previous Remaining Balance: <span style="float:right;">$'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($payment['remaining_balance'], true))); ?>+'</span><br/>Expected Balance: <span style="float:right;">$'+<?php echo json_encode(htmlspecialchars($pines->com_sales->round($payment['scheduled_balance'], true))); ?>+' </span><br/><span style="font-size:.8em;">Calculated unpaid balance based on the terms of the loan at the time of payment.</span>',
										placement: "right"
									});
								});
							</script>
							<?php
							echo '<span style="cursor:pointer;color:#b30909" id="p_muid_tooltip_'.htmlspecialchars($uniq).'">$'.htmlspecialchars($pines->com_sales->round($payment['remaining_balance'], true))."</span>";
						} elseif (isset($payment['remaining_balance'])) {
							echo '$'.htmlspecialchars($pines->com_sales->round($payment['remaining_balance'], true));
						}
						?>
					</td>
					<td><?php echo '$'.htmlspecialchars($pines->com_sales->round($payment['scheduled_balance'], true)); ?></td>
					<?php } else { ?>
					<td></td>
					<td><?php echo '$'.htmlspecialchars($pines->com_sales->round($payment['scheduled_balance'], true)); ?></td>
					<?php } ?>
				</tr>
				<?php } ?>
			</tbody>
		</table>
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
</div>