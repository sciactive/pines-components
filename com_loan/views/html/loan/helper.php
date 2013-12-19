<?php
/**
 * com_loan's helper view.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ($this->render == 'body' && gatekeeper('com_loan/listloans')) {
	global $pines;
	$module = new module('com_entityhelper', 'default_helper');
	$module->render = $this->render;
	$module->entity = $this->entity;
	echo $module->render();
	
	switch ($this->entity->payment_frequency) {
		case "12":
			$frequency = "Monthly";
			$payment_frequency = "Monthly";
			break;
		case "1":
			$frequency =  "Annually";
			$payment_frequency = "Annually";
			break;
		case "2":
			$frequency =  "Semi-annually";
			$payment_frequency = "Semi-annually";
			break;
		case "4":
			$frequency =  "Quarterly";
			$payment_frequency = "Quarterly";
			break;
		case "6":
			$frequency =  "Bi-monthly";
			$payment_frequency = "Bi-monthly";
			break;
		case "24":
			$frequency =  "Semi-monthly";
			$payment_frequency = "Semi-monthly";
			break;
		case "26":
			$frequency =  "Bi-weekly";
			$payment_frequency = "Bi-weekly";
			break;
		case "52":
			$frequency =  "Weekly";
			$payment_frequency = "Weekly";
			break;
	}
	$this->entity->get_payoff();
?>
<div style="clear:both;">
	<hr />
	<h3 style="margin:10px 0;">Properties</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">GUID</td>
				<td><?php echo htmlspecialchars($this->entity->guid); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Loan ID</td>
				<td><?php echo htmlspecialchars($this->entity->id); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Customer</td>
				<td>
					<a data-entity="<?php echo htmlspecialchars($this->entity->customer->guid); ?>" data-entity-context="com_customer_customer"><?php echo htmlspecialchars($this->entity->customer->name); ?></a>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Customer Phone</td>
				<td>
					<?php if (isset($this->entity->customer->phone)) { ?> <div>Phone: <?php echo htmlspecialchars(format_phone($this->entity->customer->phone)); ?></div>
					<?php } if (isset($this->entity->customer->phone_cell)) { ?> <div>Cell: <?php echo htmlspecialchars(format_phone($this->entity->customer->phone_cell)); ?></div>
					<?php } if (isset($this->entity->customer->phone_work)) { ?> <div>Work: <?php echo htmlspecialchars(format_phone($this->entity->customer->phone_work)); ?></div>
					<?php } if (isset($this->entity->customer->phone_home)) { ?> <div>Home: <?php echo htmlspecialchars(format_phone($this->entity->customer->phone_home)); ?></div><?php } ?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Location</td>
				<td><a data-entity="<?php echo htmlspecialchars($this->entity->group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($this->entity->group->name); ?></a></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Loan Creation Date (Date of Service)</td>
				<td><?php echo htmlspecialchars(format_date($this->entity->creation_date, 'date_sort')); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Date Loan Added</td>
				<td><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'date_sort')); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Status</td>
				<td><?php echo htmlspecialchars(ucwords($this->entity->status)); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Code</td>
				<?php if (isset($this->entity->collection_code)) {
					foreach ($pines->config->com_loan->collections_codes as $cur_code) {
						$cur_code = explode(':', $cur_code);
						if ($cur_code[0] == $this->entity->collection_code) {
							$code_description = $cur_code[1];
						}
					}
				} ?>
				<td><?php 
					if (isset($code_description)) { ?>
						<abbr title="<?php echo htmlspecialchars($code_description); ?>"><?php echo htmlspecialchars($this->entity->collection_code); ?></abbr>
					<?php } else { ?>
						<span><?php echo htmlspecialchars($this->entity->collection_code); ?></span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Principal</td>
				<td><?php echo htmlspecialchars('$'.number_format($this->entity->principal, 2, '.', '')); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Rate</td>
				<td><?php echo htmlspecialchars(($this->entity->apr).'%'); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Term</td>
				<td><?php echo htmlspecialchars($this->entity->term).' '.$this->entity->term_type; ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold;"><?php echo $frequency;?> Payment</td>
				<td><?php echo htmlspecialchars('$'.number_format($this->entity->frequency_payment, 2, '.', '')); ?></td>
			</tr>
			<?php 
			$tag_status = $this->entity->get_loan_status(true);
			$display_status = $this->entity->get_loan_status();
			?>
			<tr class="<?php echo ($this->entity->status == 'paid off' || $tag_status == 'paidoff') ? 'success' : (($tag_status == 'active') ? '' : 'warning') ?>">
				<td style="font-weight:bold;">Loan Status</td>
				<td>
					<?php echo $display_status; ?>
				</td>
			</tr>
		</tbody>
	</table>
	
	<hr />
	<h3 style="margin:10px 0;">Payment Information</h3>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">First Payment Due</td>
				<td><?php echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_short")); ?></td>
			</tr>
			<?php 
			$past_due = ($this->entity->payments[0]['past_due'] > 0) ? true: false;
			if ($this->entity->missed_first_payment && !(!empty($this->entity->paid))) {
				$missed_or_made = 'Missed';
				$missed_or_made_date = $this->entity->payments[0]['first_payment_missed'];
			} else if (!empty($this->entity->paid)) {
				$missed_or_made = 'Made';
				$missed_or_made_date = $this->entity->payments[0]['first_payment_made'];
			} else {
				$missed_or_made = 'Not Due Yet';
			}

			?>
			<tr>
				<td style="font-weight:bold;">First Payment <?php echo $missed_or_made; ?></td>
				<td <?php echo ($missed_or_made == 'Missed' || $past_due) ? 'class="text-error" style="font-weight:bold;"': ''; ?>>
					<?php echo (isset($missed_or_made_date)) ? htmlspecialchars(format_date($missed_or_made_date, "date_short")) : 'Not Due'; ?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Last Payment Made</td>
				<td <?php echo ($missed_or_made == 'Missed' || $past_due) ? 'class="text-error" style="font-weight:bold;"': ''; ?>>
					<?php echo (!empty($this->entity->paid)) ? htmlspecialchars(format_date($this->entity->payments[0]['last_payment_made'], "date_short")) : 'No Payments Made'; ?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Next Payment</td>
				<td <?php echo ($missed_or_made == 'Missed' || $past_due) ? 'class="text-error" style="font-weight:bold;"': ''; ?>>
					<?php 
					if ($this->entity->status == 'paid off') {
						echo '<span class="text-success"><strong>Paid Off</strong></span>';
					} else if (isset($this->entity->payments[0]['next_payment_due'])) {
						echo htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short"));
					} else {
						echo htmlspecialchars(format_date($this->entity->first_payment_date, "date_short"));
					} ?>
				</td>
			</tr>
		</tbody>
	</table>
	<hr/>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr class="info">
				<td style="font-weight:bold;">Next Payment Amount Due</td>
				<td <?php echo ($missed_or_made == 'Missed' || $past_due) ? 'class="text-error" style="font-weight:bold;"': ''; ?>>
					<?php if ($this->entity->status == 'paid off') {
						echo '<span class="text-success"><strong>Paid Off</strong></span>';
					} else if (isset($this->entity->payments[0]['next_payment_due_amount'])) {
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['next_payment_due_amount'], 2, '.', ''));
					} else {
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['next_payment_due_amount'], 2, '.', ''));
					} ?>
				</td>
			</tr>
			<tr class="<?php echo ($past_due) ? 'error' : '';?>">
				<td style="font-weight:bold;">Past Due Amount</td>
				<td <?php echo ($missed_or_made == 'Missed' || $past_due) ? 'class="text-error" style="font-weight:bold;"': ''; ?>>
					<?php if ($this->entity->status == 'paid off') {
						echo '<span class="text-success"><strong>Paid Off</strong></span>';
					} else if ($past_due) { 
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['past_due'], 2, '.', ''));
					} else if ($missed_or_made == 'Missed') {
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['sum_payment_short'], 2, '.', ''));
					} else {
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['past_due'], 2, '.', ''));
					} ?>
				</td>
			</tr>
			<tr class="<?php echo ($past_due) ? 'error' : '';?>">
				<td style="font-weight:bold;">Past Due + Next Due Payment</td>
				<td <?php echo ($missed_or_made == 'Missed' || $past_due) ? 'class="text-error" style="font-weight:bold;"': ''; ?>>
					<?php 
					if ($this->entity->status == 'paid off') 
						echo '<span class="text-success"><strong>Paid Off</strong></span>';
					else if ($missed_or_made == 'Missed')
						echo htmlspecialchars('$'.number_format($this->entity->balance + $this->entity->payments[0]['sum_payment_short'], 2, '.', ''));
					else
						echo htmlspecialchars('$'.number_format($this->entity->balance, 2, '.', ''));
					?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Last Paid Amount</td>
				<td>
					<?php if (!empty($this->entity->paid)) {
						$count = $this->entity->paid[0]['num_payments_paid'];
						$add = $this->entity->paid[$count]['payment_interest_paid'] + $this->entity->paid[$count]['payment_principal_paid'] + $this->entity->paid[$count]['payment_additional'];
						echo htmlspecialchars('$'.number_format($add, 2, '.', ''));
					} else {
						echo 'None Made';
					} ?>
				</td>
			</tr>
		</tbody>
	</table>
	<hr/>
	<table class="table table-bordered" style="clear:both;">
		<tbody>
			<tr>
				<td style="font-weight:bold;">Original Balance (Principal)</td>
				<td>
					<?php echo htmlspecialchars('$'.number_format($this->entity->principal, 2, '.', '')); ?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Total Paid (Includes Interest)</td>
				<td>
					<?php if (!empty($this->entity->paid)) {
						$total_paid = $this->entity->payments[0]['total_principal_paid'] + $this->entity->payments[0]['total_interest_paid'];
						echo htmlspecialchars('$'.number_format($total_paid, 2, '.', ''));
					} else
						echo 'No Payments Made';
					?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Total Principal Paid</td>
				<td>
					<?php if (!empty($this->entity->paid))
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['total_principal_paid'], 2, '.', ''));
					else
						echo 'No Payments Made';
					?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Total Interest Paid</td>
				<td>
					<?php if (!empty($this->entity->paid))
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['total_interest_paid'], 2, '.', ''));
					else
						echo 'No Payments Made';
					?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Remaining Balance (Principal Only)</td>
				<td>
					<?php if (!empty($this->entity->paid))
						echo htmlspecialchars('$'.number_format($this->entity->payments[0]['remaining_balance'], 2, '.', ''));
					else
						echo 'No Payments Made';
					?>
				</td>
			</tr>
			<tr>
				<td style="font-weight:bold;">Pay Off Amount</td>
				<td>
					<?php 
					if ($this->entity->status == 'paid off') 
						echo '<span class="text-success"><strong>Paid Off</strong></span>';
					else
						echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->payoff_amount, true)); 
					?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?php } elseif ($this->render == 'footer') { ?>
<a href="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/list', array('id' => $this->entity->id))); ?>" class="btn">View in List</a>
<?php if (gatekeeper('com_loan/viewloan')) { ?>
<a href="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/overview', array('id' => $this->entity->guid))); ?>" class="btn">Overview</a>
<?php } if (gatekeeper('com_loan/editpayments')) { ?>
<a href="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/editpayments', array('id' => $this->entity->guid))); ?>" class="btn">Edit Payments</a>
<?php } } ?>