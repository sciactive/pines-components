<?php
/**
 * com_loan_loan class.
 *
 * @package Pines
 * @subpackage com_loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A loan.
 *
 * @package Pines
 * @subpackage com_loan
 */
class com_loan_loan extends entity {
	/**
	 * Load a loan.
	 * @param int $id The ID of the loan to load, 0 for a new loan.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_loan', 'loan');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->enabled = true;
		$this->attributes = array();
	}

	/**
	 * Create a new instance.
	 * @return com_loan_loan The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the loan.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted loan with ID $this->id.", 'notice');
		return true;
	}

	/**
	 * Save with an incremental ID.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		global $pines;
		if (!isset($this->id))
			$this->id = $pines->entity_manager->new_uid('com_loan_loan');
		return parent::save();
	}

	/**
	 * Print a form to edit the loan.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_loan', 'loan/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Creates and attaches a module which verifies a loan.
	 * @return module The module.
	 */
	public function verify_loan() {
		$module = new module('com_loan', 'loan/verify', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Creates and attaches a module which verifies a loan.
	 * @return module The module.
	 */
	public function print_overview() {
		$module = new module('com_loan', 'loan/overview', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Creates and attaches a module which edits payments.
	 * @return module The module.
	 */
	public function print_edit_payments() {
		$module = new module('com_loan', 'loan/editpayments', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a form to make a payment on a loan.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function makepayment_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/makepayment', 'content');
		$module->entity = $this;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Print a form to pay off a loan.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function payoff_form() {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/payoff', 'content');
		$module->entity = $this;

		$pines->page->override_doc($module->render());
		return $module;
	}

	/**
	 * Get pay off Amount.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function get_payoff() {
		$this->get_payments_array();

		// When paying off a loan, you'll have to pay for the next due interest and any past due
		// amounts before the rest can go towards principal.

		if ($this->payments[0]['unpaid_interest'] >= .01)
			$this->unpaid_interest = $this->payments[0]['unpaid_interest'];
		else
			$this->unpaid_interest = 0.00;

		$next_due_date = $this->payments[0]['next_payment_due'];
		foreach ($this->payments as $payment) {
			if ($payment['scheduled_date_expected'] == $next_due_date) {
				$due_interest = $payment['payment_interest_expected'];
				break;
			}
		}
		$this->payoff_amount = $this->unpaid_interest + $due_interest + $this->payments[0]['remaining_balance'];
		$this->due_interest = $due_interest;
		return $this->save();
	}

	/**
	 * Generates an array of payments if payments have been made or missed.
	 * @return array Payments array.
	 */
	public function get_payments_array() {
		global $pines;

		// Create array of scheduled payments.
		$scheduled_payment_dates = array();
		$c = 0;
		for ($c = 0; $c < $this->number_payments; $c++) {
			if ($c == 0)
				$scheduled_payment_dates[$c] = strtotime($this->first_payment_date);
			else
				$scheduled_payment_dates[$c] = strtotime('+1 month', $scheduled_payment_dates[$c-1]);
		}
		// Get today's date.
		$today = strtotime('today');
		$temp_payments = array();

		// Check paid array for any "partial not due" payments and change their status
		// to "partial payment" if it's past due at this point.
		// The partial-not-due payment status is acceptable, until another payment is made on it
		// with a late receive date. Then the whole payment is changed to a late one.
		if (!empty($this->paid)) {
			foreach ($this->paid as &$paid) {
				// Get num of extra payments, if any.
				$num_ex = count($paid['extra_payments']) - 1;
				// Check Parent Payments.
				if ($paid['payment_date_received'] > $paid['payment_date_expected'] && $paid['payment_status'] == "partial_not_due") {
					// Payments are late.
					$paid['payment_days_late'] = format_date_range($paid['payment_date_expected'], $paid['payment_date_received'], '#days#');
					$paid['payment_status'] = "partial";
				} elseif ($paid['extra_payments'][$num_ex]['payment_date_received'] > $paid['extra_payments'][$num_ex]['payment_date_expected']) {
					// This only checks the last extra payment on the whole payment, because it's what will determine
					// if the whole payment should now be considered late or partial late.
					// Find out short amount
					$parent_paid = $paid['payment_interest_paid'] + $paid['payment_principal_paid'];
					foreach ($paid['extra_payments'] as $extra_payment) {
						$extra_paid += $extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'];
					}
					$paid_amount = $parent_paid + $extra_paid;
					// Not going to worry about additional, because I just need to know the expected amounts are paid or not.
					$paid_expected = $paid['payment_interest_expected'] + $paid['payment_principal_expected'];

					$payment_short = $paid_expected - $paid_amount;

					if ($payment_short >= 0.01) {
						// If it's short, it must be partial.
						$paid['extra_payments'][$num_ex]['payment_status'] = "partial";
						$paid['payment_status'] = "partial";
					} else {
						// If no short amount exists, then it's paid_late
						$paid['extra_payments'][$num_ex]['payment_status'] = "paid_late";
						$paid['payment_status'] = "paid_late";
					}
					$paid['payment_days_late'] = format_date_range($paid['extra_payments'][$num_ex]['payment_date_expected'], $paid['extra_payments'][$num_ex]['payment_date_received'], '#days#');
				}
			}
			unset($paid);
		}
		// Create payments array if first payment was missed.
		// (If it was missed, the $this->paid array would not exist, and it would display the normal amortization table.
		if ($today > $scheduled_payment_dates[0] && empty($this->paid)) {
			// No payments have been made, but at least the first one is due.
			$this->missed_first_payment = true;
			// Now check possible other missed payments and create payments array to be used in the payments table.
			$c = 1;
			foreach ($scheduled_payment_dates as $due_date) {
				if ($today > $due_date) {
					// Payment not made (paid array does not exist).
					$temp_payments[$c]['payment_type'] = "none";
					$temp_payments[$c]['payment_status'] = 'missed';
					$temp_payments[$c]['payment_date_expected'] = $due_date;
					$temp_payments[$c]['payment_days_late'] = format_date_range($due_date, $today, '#days#');
					if (!isset($temp_payments[0]['first_payment_missed'])) {
						// Date missed first payment
						$temp_payments[0]['first_payment_missed'] = $due_date;
						$temp_payments[$c]['scheduled_current_balance'] = $this->principal;
					} else {
						$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
					}
					$temp_payments[$c]['payment_interest_expected'] = $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
					$temp_payments[$c]['payment_principal_expected'] = $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];
					$temp_payments[$c]['payment_interest_paid'] = 0.00; // Because it is missed and no payments have ever bee made.
					$temp_payments[$c]['payment_principal_paid'] = 0.00; // Because it is missed and no payments have ever bee made.
					$temp_payments[$c]['payment_amount_paid'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'];
					$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - $temp_payments[$c]['payment_principal_expected'];
					$temp_payments[$c]['remaining_balance'] = $this->principal - ($temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_interest_paid']);
					$temp_payments[$c]['payment_balance_unpaid'] = -1*($temp_payments[$c]['scheduled_balance'] - $temp_payments[$c]['remaining_balance']);
					$temp_payments[$c]['payment_interest_unpaid'] = $temp_payments[$c]['payment_interest_expected'] - $temp_payments[$c]['payment_interest_paid'];
					$temp_payments[$c]['payment_short'] = $this->frequency_payment - $temp_payments[$c]['payment_amount_paid'];
				}
				if ($today <= $due_date) {
					// payments are not due yet.
					if (!$temp_payments[0]['last_payment_made']) {
						$temp_payments[0]['last_payment_made'] = 'None Made';
						$temp_payments[0]['remaining_payments_due'] = $this->number_payments - ($c-1);
					}
					$temp_payments[$c]['payment_type'] = "scheduled";
					$temp_payments[$c]['payment_status'] = 'not due yet';
					$temp_payments[$c]['scheduled_date_expected'] = $due_date;
					$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
					$temp_payments[$c]['payment_interest_expected'] = $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
					// For scheduled balances, check if paying off last payment.
					if ($temp_payments[$c]['scheduled_current_balance'] < $this->frequency_payment) {
						$temp_payments[$c]['payment_principal_expected'] = $temp_payments[$c]['scheduled_current_balance'];
					} elseif (($temp_payments[$c]['scheduled_current_balance'] - $this->frequency_payment) <= 1) {
						$temp_payments[$c]['payment_principal_expected'] = $temp_payments[$c]['scheduled_current_balance'];
					} else {
						$temp_payments[$c]['payment_principal_expected'] = $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];
					}
					$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - $temp_payments[$c]['payment_principal_expected'];

					if (!$temp_payments[0]['next_payment_due']) {
							$temp_payments[0]['next_payment_due'] = $due_date;
							$temp_payments[0]['next_payment_due_amount'] = $temp_payments[$c]['payment_interest_expected'] + $temp_payments[$c]['payment_principal_expected'];
					}
				}
				if (isset($temp_payments[$c]['remaining_balance'])) {
					$temp_payments[0]['remaining_balance'] = $temp_payments[$c]['remaining_balance'];
					$temp_payments[0]['percentage_paid'] = (($this->principal - $temp_payments[$c]['remaining_balance']) / $this->principal) * 100;
					$temp_payments[0]['remaining_payments'] = ($this->number_payments * (1 - ($temp_payments[0]['percentage_paid'] / 100)));
				}
				if (isset($temp_payments[$c]['payment_balance_unpaid'])) {
					$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
					$this->missed_first_payment_unpaid_balance = $temp_payments[0]['unpaid_balance'];
				}
				if (isset($temp_payments[$c]['payment_interest_unpaid'])) {
					$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
					$this->missed_first_payment_unpaid_interest = $temp_payments[0]['unpaid_interest'];
				}
				if ($temp_payments[$c]['payment_short'] > 0) {
					$temp_payments[0]['sum_payment_short'] += $temp_payments[$c]['payment_short'];
				}
				$c++;
			}
			$temp_payments[0]['past_due'] = $temp_payments[0]['unpaid_interest'] + $temp_payments[0]['unpaid_balance'];

			ksort($temp_payments);
			$payments = $temp_payments;
			$this->payments = $payments;
			$this->past_due = $temp_payments[0]['past_due'];
			return $this->payments;
		}


		// Create payments array for when payments have been made.
		if (!empty($this->paid)) {
			// Now foreach through the due date. We will construct payments array from this.
			// Catches Missed Payments, and includes past_due payments made.
			// Temp_payments[0] contains general and summarizing information about the array.
			$c = 1;
			foreach ($scheduled_payment_dates as $due_date) {
				if ($due_date < $today) {
					// Payments history.
					foreach ($this->paid as $paid) {
						if ($due_date == $paid['payment_date_expected'] || strtotime('+1 day', $due_date) == $paid['payment_date_expected']) {
							// A payment was made.
							if ($paid['payment_type'] == "past_due") {
								// this count has to be here
								// Apparently not. -Hunter
//								if ($temp_payments[0]['first_payment_made']) {
//									$c++;
//								}
								// This payment is for a past_due balance
								$temp_payments[$c]['payment_type'] = $paid['payment_type'];
								$temp_payments[$c]['payment_status'] = $paid['payment_status'];
								// this might be different depending on if this was edited or the original..
								// check if its an edit by checking for the nonexistance of payment amount paid:
								if ($paid['payment_interest_paid'] >= .01 && empty($paid['payment_amount_paid'])) {
									$temp_payments[$c]['payment_date_expected'] = $paid['payment_date_expected'];
									$temp_payments[$c]['payment_date_received'] = $paid['payment_date_received'];
									$temp_payments[$c]['payment_date_recorded'] = $paid['payment_date_recorded'];
									$temp_payments[$c]['payment_id'] = $paid['payment_id'];
									// Calculate days payment late if paid_late.
									if (!$paid['payment_days_late'])
										$temp_payments[$c]['payment_days_late'] = (format_date_range($paid['payment_date_expected'], $paid['payment_date_received'], '#days#'));
									else
										$temp_payments[$c]['payment_days_late'] = $paid['payment_days_late'];
								} else {
									$temp_payments[$c]['payment_date_expected'] = strtotime('-1 day', $paid['payment_date_expected']);
									$temp_payments[$c]['payment_date_received'] = $paid['payment_date_received'];
									$temp_payments[$c]['payment_date_recorded'] = $paid['payment_date_recorded'];
									$temp_payments[$c]['payment_id'] = $paid['payment_id'];
									// Calculate days payment late if paid_late.
									if (!$paid['payment_days_late'])
										$temp_payments[$c]['payment_days_late'] = (format_date_range($paid['payment_date_expected'], $paid['payment_date_received'], '#days#'));
									else
										$temp_payments[$c]['payment_days_late'] = $paid['payment_days_late'];
								}
								// Checks for first payment made

								// Because sometimes a payment_amount_paid doesn't exist, add up paid amounts to get it.
								if ($paid['payment_interest_paid'] >= .01 && empty($paid['payment_amount_paid'])) {
									$paid_amount = $paid['payment_interest_paid'] + $paid['payment_principal_paid'] + $paid['payment_additional'];
								} else
									$paid_amount = $paid['payment_amount_paid'];
								if (!$temp_payments[0]['first_payment_made']) {
									$temp_payments[0]['first_payment_made'] = $temp_payments[$c]['payment_date_received'];
									if($c == 1) {
										$temp_payments[$c]['scheduled_current_balance'] = $pines->com_sales->round((float) $this->principal);
										$temp_payments[$c]['current_balance'] = $pines->com_sales->round((float) $this->principal);
									} else {
										$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
										$temp_payments[$c]['current_balance'] = $temp_payments[$c-1]['remaining_balance'];
									}
									// Establish expected interest and principal based off of unpaid balance & interest.
									// Because the payments array gets rewritten often, we had to save a static unpaid
									// interest and unpaid balance ONLY if the first payment was missed.
									$temp_payments[$c]['payment_interest_expected'] = $this->missed_first_payment_unpaid_interest;
									$temp_payments[$c]['payment_principal_expected'] = $this->missed_first_payment_unpaid_balance;

									// Get unpaid interest and unpaid balance.
									// Unpaid interest gets paid first.
									if ($this->missed_first_payment_unpaid_interest > 0 && $paid_amount >= $this->missed_first_payment_unpaid_interest) {
										// unpaid interest exists && is fully paid, you can also calculate the balance unpaid.
										$temp_payments[$c]['payment_interest_paid'] = $this->missed_first_payment_unpaid_interest;
										$temp_payments[$c]['payment_interest_unpaid'] = 0;
										// if there was leftover paid, put it towards principal.
										$temp_payments[$c]['payment_principal_paid'] = $paid_amount - $this->missed_first_payment_unpaid_interest;
										$temp_payments[$c]['payment_balance_unpaid'] = $pines->com_sales->round($this->missed_first_payment_unpaid_balance - $temp_payments[$c]['payment_principal_paid']);
									} elseif ($paid_amount < $this->missed_first_payment_unpaid_interest) {
										$temp_payments[$c]['payment_interest_paid'] = $paid_amount;
										$temp_payments[$c]['payment_balance_unpaid'] = $this->missed_first_payment_unpaid_balance;
										$temp_payments[$c]['payment_interest_unpaid'] = $this->missed_first_payment_unpaid_interest - $paid_amount;
									}
									// Maybe there was no interest unpaid, check for the existance of above variables, and if not, check unpaid balance.
									if (!isset($temp_payments[$c]['payment_interest_paid']) && $this->missed_first_payment_unpaid_balance > 0) {
										$temp_payments[$c]['payment_interest_paid'] = 0.00; // no unpaid interest to settle.
										$temp_payments[$c]['payment_principal_paid'] = $paid_amount;
										$temp_payments[$c]['payment_balance_unpaid'] = $pines->com_sales->round($this->missed_first_payment_unpaid_balance - $paid_amount);
									}
									$temp_payments[0]['inflated_expected_interest'] += (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
								} else {
									$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
									$temp_payments[$c]['current_balance'] = $temp_payments[$c-1]['remaining_balance'];
									// Establish expected interest and principal based off of unpaid balance & interest.
									$temp_payments[$c]['payment_interest_expected'] = (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
									$temp_payments[$c]['payment_principal_expected'] =  $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];
									// Get unpaid interest and unpaid balance.
									// Unpaid interest gets paid first.
									if ($temp_payments[$c]['payment_interest_expected'] >= .01 && $paid_amount >= $temp_payments[$c]['payment_interest_expected']) {
										// unpaid interest exists && is fully paid, you can also calculate the balance unpaid.
										$temp_payments[$c]['payment_interest_paid'] = $temp_payments[$c]['payment_interest_expected'];
										// if there was leftover paid, put it towards principal.
										$temp_payments[$c]['payment_principal_paid'] = $paid_amount - $temp_payments[$c]['payment_interest_expected'];
										$temp_payments[$c]['payment_balance_unpaid'] = $pines->com_sales->round($temp_payments[$c]['payment_principal_expected'] - $temp_payments[$c]['payment_principal_paid']);
									} elseif ($paid_amount < $temp_payments[$c]['payment_interest_expected']) {
										$temp_payments[$c]['payment_interest_paid'] = $paid_amount;
										$temp_payments[$c]['payment_principal_paid'] = 0;
										$temp_payments[$c]['payment_balance_unpaid'] = $temp_payments[$c]['payment_principal_expected'];
										$temp_payments[$c]['payment_interest_unpaid'] = $temp_payments[$c]['payment_interest_expected'] - $paid_amount;
									}
									// Maybe there was no interest unpaid, check for the existance of above variables, and if not, check unpaid balance.
									if (!isset($temp_payments[$c]['payment_interest_paid']) && $temp_payments[$c]['payment_principal_expected'] >= 0.01) {
										$temp_payments[$c]['payment_interest_paid'] = 0.00; // no unpaid interest to settle.
										$temp_payments[$c]['payment_principal_paid'] = $paid_amount;
										$temp_payments[$c]['payment_balance_unpaid'] = $pines->com_sales->round($temp_payments[$c-1]['payment_balance_unpaid'] - $paid_amount);
									}
									$temp_payments[0]['inflated_expected_interest'] += (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
//									$temp_payments[$c]['payment_interest_unpaid'] = ($temp_payments[$c-1]['payment_interest_unpaid'] - $temp_payments[$c]['payment_interest_paid']) + (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
								}
								// We keep these numbers even for partial past due, and we'll add extra payments if necessary to these amounts.
								if (!$temp_payments[$c]['payment_interest_paid'])
									$temp_payments[$c]['payment_interest_paid'] = $paid['payment_interest_paid'];
								if (!$temp_payments[$c]['payment_principal_paid'])
									$temp_payments[$c]['payment_principal_paid'] = $paid['payment_principal_paid'];
								if (!$temp_payments[$c]['payment_additional'])
									$temp_payments[$c]['payment_additional'] = $paid['payment_additional'];
								// Get extra payments for partial past due and add it to payments paid.
								if (!empty($paid['extra_payments'])) {
									// Define original values for interest and principal paid:
									$temp_payments[$c]['payment_interest_paid_orig'] = $paid['payment_interest_paid'];
									$temp_payments[$c]['payment_principal_paid_orig'] = $paid['payment_principal_paid'];
									$temp_payments[$c]['payment_additional_orig'] = $paid['payment_additional'];
									$temp_payments[$c]['payment_amount_paid_orig'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];

									$temp_payments[$c]['extra_payments'] = $paid['extra_payments'];
									if (!empty($temp_payments[$c]['extra_payments'])) {
										foreach ($temp_payments[$c]['extra_payments'] as $extra_payment) {
											$sum_int_paid += $extra_payment['payment_interest_paid'];
											$sum_bal_paid += $extra_payment['payment_principal_paid'];
											$temp_payments[$c]['payment_interest_paid'] += $extra_payment['payment_interest_paid'];
											$temp_payments[$c]['payment_principal_paid'] += $extra_payment['payment_principal_paid'];
											$temp_payments[$c]['payment_additional'] += $extra_payment['payment_additional'];
										}
										// Not necessary to compute the interest..
										$temp_payments[$c]['payment_balance_unpaid'] -= $sum_bal_paid;
										$temp_payments[$c]['payment_interest_unpaid'] -= $sum_int_paid;
									}
								}
								if (!isset($temp_payments[$c]['payment_interest_unpaid']))
									$temp_payments[$c]['payment_interest_unpaid'] -= $pines->com_sales->round((float)$sum_int_paid);
								if (!isset($temp_payments[$c]['payment_balance_unpaid']))
									$temp_payments[$c]['payment_balance_unpaid'] -= $pines->com_sales->round((float)$sum_bal_paid);
								$temp_payments[$c]['payment_amount_paid'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];
								if ($c == 1)
									$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - ($this->schedule[0]['payment_principal_expected']);
								else
									$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c-1]['remaining_balance'] - ($temp_payments[$c]['payment_principal_expected']);
								$temp_payments[$c]['remaining_balance'] = $temp_payments[$c]['current_balance'] - ($temp_payments[$c]['payment_principal_paid']);

								// Don't count short amount if the balance has been paid since, even partially.
								if (is_int($this->payments[0]['last_payment_made']) && $due_date < $this->payments[0]['last_payment_made']) {
									// Don't count short amount.
									$temp_payments[$c]['payment_short'] = 0.00;
								} else
									$temp_payments[$c]['payment_short'] = $pines->com_sales->round(($temp_payments[$c]['payment_interest_expected'] + $temp_payments[$c]['payment_principal_expected']) - $temp_payments[$c]['payment_amount_paid']);

								// This is the amount of interest unpaid that has been paid, and needs to be removed from unpaid_interest.
								$temp_payments[0]['subtract_unpaid_interest_paid'] = $temp_payments[$c]['payment_interest_paid'];
								if (isset($temp_payments[$c]['remaining_balance'])) {
									$temp_payments[0]['remaining_balance'] = $temp_payments[$c]['remaining_balance'];
									$temp_payments[0]['percentage_paid'] = (($this->principal - $temp_payments[$c]['remaining_balance']) / $this->principal) * 100;
									$temp_payments[0]['remaining_payments'] = ($this->number_payments * (1 - ($temp_payments[0]['percentage_paid'] / 100)));
								}
								if (isset($temp_payments[$c]['payment_balance_unpaid'])) {
									$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
								}
								if (isset($temp_payments[$c]['payment_interest_unpaid'])) {
									$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
								}
								if ($temp_payments[$c]['payment_short'] > 0)
									$temp_payments[0]['sum_payment_short'] += $temp_payments[$c]['payment_short'];
								$temp_payments[0]['total_principal_paid'] += $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];
								$temp_payments[0]['total_interest_paid'] += $temp_payments[$c]['payment_interest_paid'];
							} else {
								$temp_payments[$c]['payment_type'] = "payment";
								$temp_payments[$c]['payment_status'] = $paid['payment_status'];
								$temp_payments[$c]['payment_date_expected'] = $paid['payment_date_expected'];
								$temp_payments[$c]['payment_date_received'] = $paid['payment_date_received'];
								$temp_payments[$c]['payment_date_recorded'] = $paid['payment_date_recorded'];
								$temp_payments[$c]['payment_id'] = $paid['payment_id'];
								$temp_payments[$c]['payment_days_late'] = $paid['payment_days_late'];
								// Checks for first payment made
								if (!$temp_payments[0]['first_payment_made']) {
									$temp_payments[0]['first_payment_made'] = $temp_payments[$c]['payment_date_received'];
									$temp_payments[$c]['scheduled_current_balance'] = (float) $this->principal;
									$temp_payments[$c]['current_balance'] = (float) $this->principal;
								} else {
									$temp_payments[$c]['scheduled_current_balance'] = (float) $temp_payments[$c-1]['scheduled_balance'];
									$temp_payments[$c]['current_balance'] = (float) $temp_payments[$c-1]['remaining_balance'];
								}
								$temp_payments[$c]['payment_interest_expected'] = (float) $pines->com_sales->round(($temp_payments[$c]['current_balance'] * $this->rate_per_period) / 100, true);
								$temp_payments[$c]['payment_principal_expected'] = $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];

								// We keep these numbers even for partial not due, and we'll add extra payments if necessary to these amounts.
								$temp_payments[$c]['payment_interest_paid'] = $paid['payment_interest_paid'];
								$temp_payments[$c]['payment_principal_paid'] = $paid['payment_principal_paid'];
								$temp_payments[$c]['payment_additional'] = $paid['payment_additional'];

								// Get extra payments for partial not due and add it to payments paid.
								if (!empty($paid['extra_payments'])) {
									// Define original values for interest and principal paid:
									$temp_payments[$c]['payment_interest_paid_orig'] = $paid['payment_interest_paid'];
									$temp_payments[$c]['payment_principal_paid_orig'] = $paid['payment_principal_paid'];
									$temp_payments[$c]['payment_additional_orig'] = $paid['payment_additional'];
									$temp_payments[$c]['payment_amount_paid_orig'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];

									$temp_payments[$c]['extra_payments'] = $paid['extra_payments'];
									if (!empty($temp_payments[$c]['extra_payments'])) {
										foreach ($temp_payments[$c]['extra_payments'] as $extra_payment) {
											$temp_payments[$c]['payment_interest_paid'] += $extra_payment['payment_interest_paid'];
											$temp_payments[$c]['payment_principal_paid'] += $extra_payment['payment_principal_paid'];
											$temp_payments[$c]['payment_additional'] += $extra_payment['payment_additional'];
										}
									}
								}
								$temp_payments[$c]['payment_amount_paid'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];
								$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - ($temp_payments[$c]['payment_principal_expected'] + $temp_payments[$c]['payment_additional']);
								$temp_payments[$c]['remaining_balance'] = $temp_payments[$c]['current_balance'] - ($temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional']);
								$temp_payments[$c]['payment_balance_unpaid'] = $pines->com_sales->round(-1*($temp_payments[$c]['scheduled_balance'] - $temp_payments[$c]['remaining_balance']));
								$temp_payments[$c]['payment_interest_unpaid'] = $pines->com_sales->round($temp_payments[$c]['payment_interest_expected'] - $temp_payments[$c]['payment_interest_paid']);

								// Don't count short amount if the balance has been paid since, even partially.
								if (is_int($this->payments[0]['last_payment_made']) && $due_date < $this->payments[0]['last_payment_made']) {
									// Don't count short amount.
									$temp_payments[$c]['payment_short'] = 0.00;
								} else
									$temp_payments[$c]['payment_short'] = $pines->com_sales->round(($temp_payments[$c]['payment_interest_expected'] + $temp_payments[$c]['payment_principal_expected']) - $temp_payments[$c]['payment_amount_paid']);

								if (isset($temp_payments[$c]['remaining_balance'])) {
									$temp_payments[0]['remaining_balance'] = $temp_payments[$c]['remaining_balance'];
									$temp_payments[0]['percentage_paid'] = (($this->principal - $temp_payments[$c]['remaining_balance']) / $this->principal) * 100;
									$temp_payments[0]['remaining_payments'] = ($this->number_payments * (1 - ($temp_payments[0]['percentage_paid'] / 100)));
								}
								if (isset($temp_payments[$c]['payment_balance_unpaid'])) {
									$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
								}
								if (isset($temp_payments[$c]['payment_interest_unpaid']) && $temp_payments[$c]['payment_type'] == "past_due") {
									$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
								}
								if ($temp_payments[$c]['payment_short'] > 0)
									$temp_payments[0]['sum_payment_short'] += $temp_payments[$c]['payment_short'];
								$temp_payments[0]['total_interest_paid'] += $temp_payments[$c]['payment_interest_paid'];
								$temp_payments[0]['total_principal_paid'] += $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];
							}
						}
					}
					if (!$temp_payments[$c]['payment_status'] && !($due_date >= $today)) {
						// payment missed
						if (!$temp_payments[0]['last_payment_made']) {
							if (isset($temp_payments[$c-1]['extra_payments'])) {
								$num_extra = 0;
								$num_extra = count($temp_payments[$c-1]['extra_payments']) - 1;
								$temp_payments[0]['last_payment_made'] = $temp_payments[$c - 1]['extra_payments'][$num_extra]['payment_date_received'];
							} else
								$temp_payments[0]['last_payment_made'] = $temp_payments[$c-1]['payment_date_received'];
						}
						$temp_payments[$c]['payment_type'] = "none";
						$temp_payments[$c]['payment_status'] = 'missed';
						$temp_payments[$c]['payment_date_expected'] = $due_date;
						$temp_payments[$c]['payment_days_late'] = format_date_range($due_date, $today, '#days#');
						if ($c == 1) {
							// At least first two payments WERE missed, but we have a paid array now.
							$temp_payments[$c]['scheduled_current_balance'] = $this->principal;
							$temp_payments[0]['first_payment_missed'] = $due_date;
							$temp_payments[$c]['missed_current_balance'] = $this->principal;
							$temp_payments[$c]['current_balance'] = $this->principal;
						} else {
							$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
							if (isset($temp_payments[$c-1]['missed_remaining_balance']))
								$temp_payments[$c]['missed_current_balance'] = $temp_payments[$c-1]['missed_remaining_balance'];
							else
								$temp_payments[$c]['missed_current_balance'] = $temp_payments[$c-1]['remaining_balance'];
							$temp_payments[$c]['current_balance'] = $temp_payments[$c-1]['remaining_balance'];
						}
						$temp_payments[$c]['payment_interest_expected'] = (float) $pines->com_sales->round(($temp_payments[$c]['missed_current_balance'] * $this->rate_per_period) / 100, true);
						$temp_payments[$c]['payment_principal_expected'] = $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];
						$temp_payments[$c]['payment_interest_paid'] = 0.00; // Because it is missed and no payments have ever bee made.
						$temp_payments[$c]['payment_principal_paid'] = 0.00; // Because it is missed and no payments have ever bee made.
						$temp_payments[$c]['payment_amount_paid'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'];
						$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - $temp_payments[$c]['payment_principal_expected'];
						$temp_payments[$c]['remaining_balance'] =  $temp_payments[$c]['current_balance'] - $temp_payments[$c]['payment_principal_paid'];
						$temp_payments[$c]['missed_remaining_balance'] =  $temp_payments[$c]['missed_current_balance'] - $temp_payments[$c]['payment_principal_expected'];
						$temp_payments[$c]['payment_balance_unpaid'] = -1*($temp_payments[$c]['scheduled_balance'] - $temp_payments[$c]['remaining_balance']);
						$temp_payments[$c]['payment_interest_unpaid'] = $temp_payments[$c]['payment_interest_expected'] - $temp_payments[$c]['payment_interest_paid'];

						// Don't count short amount if the balance has been paid since, even partially.
						if (is_int($this->payments[0]['last_payment_made']) && $due_date < $this->payments[0]['last_payment_made']) {
							// Don't count short amount.
							$temp_payments[$c]['payment_short'] = 0.00;
						} else
							$temp_payments[$c]['payment_short'] = $pines->com_sales->round(($temp_payments[$c]['payment_interest_expected'] + $temp_payments[$c]['payment_principal_expected']) - $temp_payments[$c]['payment_amount_paid']);

						if (isset($temp_payments[$c]['remaining_balance'])) {
							$temp_payments[0]['remaining_balance'] = $temp_payments[$c]['remaining_balance'];
							$temp_payments[0]['percentage_paid'] = (($this->principal - $temp_payments[$c]['remaining_balance']) / $this->principal) * 100;
							$temp_payments[0]['remaining_payments'] = ($this->number_payments * (1 - ($temp_payments[0]['percentage_paid'] / 100)));
						}
						if (isset($temp_payments[$c]['payment_balance_unpaid'])) {
							$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
						}
						if (isset($temp_payments[$c]['payment_interest_unpaid'])) {
							$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
						}
						if ($temp_payments[$c]['payment_short'] > 0)
							$temp_payments[0]['sum_payment_short'] += $temp_payments[$c]['payment_short'];
					}
				}
				if ($due_date >= $today) {
					// Payments not due yet (but possibly paid).
					foreach ($this->paid as $paid) {
						if ($due_date == $paid['payment_date_expected']) {
							$temp_payments[$c]['payment_type'] = "payment";
							$temp_payments[$c]['payment_status'] = $paid['payment_status'];
							$temp_payments[$c]['payment_date_expected'] = $paid['payment_date_expected'];
							$temp_payments[$c]['payment_date_received'] = $paid['payment_date_received'];
							$temp_payments[$c]['payment_date_recorded'] = $paid['payment_date_recorded'];
							$temp_payments[$c]['payment_id'] = $paid['payment_id'];
							// Checks for first payment made
							if (!$temp_payments[0]['first_payment_made']) {
								$temp_payments[0]['first_payment_made'] = $temp_payments[$c]['payment_date_received'];
								$temp_payments[$c]['scheduled_current_balance'] = $this->principal;
								$temp_payments[$c]['current_balance'] = $this->principal;
							} else {
								$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
								$temp_payments[$c]['current_balance'] = $temp_payments[$c-1]['remaining_balance'];
							}
							$temp_payments[$c]['payment_interest_expected'] = $pines->com_sales->round(($temp_payments[$c]['current_balance'] * $this->rate_per_period) / 100, true);
							$temp_payments[$c]['payment_principal_expected'] = $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];

							// We keep these numbers even for partial not due, and we'll add extra payments if necessary to these amounts.
							$temp_payments[$c]['payment_interest_paid'] = $paid['payment_interest_paid'];
							$temp_payments[$c]['payment_principal_paid'] = $paid['payment_principal_paid'];
							$temp_payments[$c]['payment_additional'] = $paid['payment_additional'];

							// Get extra payments for partial not due and add it to payments paid.
							if (!empty($paid['extra_payments'])) {
								// Define original values for interest and principal paid:
								$temp_payments[$c]['payment_interest_paid_orig'] = $paid['payment_interest_paid'];
								$temp_payments[$c]['payment_principal_paid_orig'] = $paid['payment_principal_paid'];
								$temp_payments[$c]['payment_additional_orig'] = $paid['payment_additional'];
								$temp_payments[$c]['payment_amount_paid_orig'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];

								$temp_payments[$c]['extra_payments'] = $paid['extra_payments'];
								if (!empty($temp_payments[$c]['extra_payments'])) {
									foreach ($temp_payments[$c]['extra_payments'] as $extra_payment) {
										$temp_payments[$c]['payment_interest_paid'] += $extra_payment['payment_interest_paid'];
										$temp_payments[$c]['payment_principal_paid'] += $extra_payment['payment_principal_paid'];
										$temp_payments[$c]['payment_additional'] += $extra_payment['payment_additional'];
									}
								}
							}

							$temp_payments[$c]['payment_amount_paid'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];
							$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - $temp_payments[$c]['payment_principal_expected'];
							$temp_payments[$c]['remaining_balance'] =  $temp_payments[$c]['current_balance'] - ($temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional']);

							$temp_payments[$c]['payment_balance_unpaid'] = -1*($temp_payments[$c]['scheduled_balance'] - $temp_payments[$c]['remaining_balance']);
							$temp_payments[$c]['payment_interest_unpaid'] = $temp_payments[$c]['payment_interest_expected'] - $temp_payments[$c]['payment_interest_paid'];
							$temp_payments[$c]['payment_short'] = $this->frequency_payment - $temp_payments[$c]['payment_amount_paid'];

							if (isset($temp_payments[$c]['remaining_balance'])) {
								$temp_payments[0]['remaining_balance'] = $temp_payments[$c]['remaining_balance'];
								$temp_payments[0]['percentage_paid'] = (($this->principal - $temp_payments[$c]['remaining_balance']) / $this->principal) * 100;
								$temp_payments[0]['remaining_payments'] = ($this->number_payments * (1 - ($temp_payments[0]['percentage_paid'] / 100)));
							}
							if (isset($temp_payments[$c]['payment_balance_unpaid'])) {
								if($temp_payments[$c]['payment_status'] == "partial_not_due") {
									$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
									$temp_payments[0]['unpaid_balance_not_past_due'] = $temp_payments[$c]['payment_balance_unpaid'];
								} else
									$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
							}
							if (isset($temp_payments[$c]['payment_interest_unpaid'])) {
								if($temp_payments[$c]['payment_status'] == "partial_not_due") {
									$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
									$temp_payments[0]['unpaid_interest_not_past_due'] += $temp_payments[$c]['payment_interest_unpaid'];
								} else {
									$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
								}
							}
							if ($temp_payments[$c]['payment_short'] > 0)
								$temp_payments[0]['sum_payment_short'] += $temp_payments[$c]['payment_short'];
							$temp_payments[0]['total_interest_paid'] += $temp_payments[$c]['payment_interest_paid'];
							$temp_payments[0]['total_principal_paid'] += $temp_payments[$c]['payment_principal_paid'] + $temp_payments[$c]['payment_additional'];
						}
					}
					if (!$temp_payments[$c]['payment_date_expected']) {
						if (!$temp_payments[0]['last_payment_made']) {
							if (isset($temp_payments[$c-1]['extra_payments'])) {
								$num_extra = 0;
								$num_extra = count($temp_payments[$c-1]['extra_payments']) - 1;
								$temp_payments[0]['last_payment_made'] = $temp_payments[$c-1]['extra_payments'][$num_extra]['payment_date_received'];
							} else
								$temp_payments[0]['last_payment_made'] = $temp_payments[$c-1]['payment_date_received'];
							$temp_payments[0]['remaining_payments_due'] = $this->number_payments - ($c - 1);
						}
						if (!$temp_payments[0]['remaining_payments_due']) {
							$temp_payments[0]['remaining_payments_due'] = $this->number_payments - ($c - 1);
						}
						$temp_payments[$c]['payment_type'] = "scheduled";
						$temp_payments[$c]['payment_status'] = 'not due yet';
						$temp_payments[$c]['scheduled_date_expected'] = $due_date;
						// If additional payments have been made, interest needs to be calculated on the remaining balance.
						if (isset($temp_payments[$c-1]['remaining_balance']) && $temp_payments[$c-1]['scheduled_balance'] > $temp_payments[$c-1]['remaining_balance'])
							$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['remaining_balance'];
						else
							$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
						$temp_payments[$c]['payment_interest_expected'] = (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
						if ($temp_payments[$c]['scheduled_current_balance'] < $this->frequency_payment) {
							$temp_payments[$c]['payment_principal_expected'] = $temp_payments[$c]['scheduled_current_balance'];
						} else {
							$temp_payments[$c]['payment_principal_expected'] = $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];
						}
						$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - $temp_payments[$c]['payment_principal_expected'];

						if (!$temp_payments[0]['next_payment_due']) {
							if ($temp_payments[$c-1]['payment_status'] == "partial_not_due") {
								$temp_payments[0]['next_payment_due'] = $temp_payments[$c-1]['payment_date_expected'];
								$temp_payments[0]['next_payment_due_amount'] = ($temp_payments[$c-1]['payment_interest_expected'] + $temp_payments[$c-1]['payment_principal_expected']) - ($temp_payments[$c-1]['payment_interest_paid'] + $temp_payments[$c-1]['payment_principal_paid']);
							} else {
								$temp_payments[0]['next_payment_due'] = $due_date;
								$temp_payments[0]['next_payment_due_amount'] = $temp_payments[$c]['payment_interest_expected'] + $temp_payments[$c]['payment_principal_expected'];
							}
						}
						if (isset($temp_payments[$c]['remaining_balance'])) {
							$temp_payments[0]['remaining_balance'] = $temp_payments[$c]['remaining_balance'];
							$temp_payments[0]['percentage_paid'] = (($this->principal - $temp_payments[$c]['remaining_balance']) / $this->principal) * 100;
							$temp_payments[0]['remaining_payments'] = ($this->number_payments * (1 - ($temp_payments[0]['percentage_paid'] / 100)));
						}
						if (isset($temp_payments[$c]['payment_balance_unpaid'])) {
							if($temp_payments[$c]['payment_status'] == "partial_not_due") {
								$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
								$temp_payments[0]['unpaid_balance_not_past_due'] = $temp_payments[$c]['payment_balance_unpaid'];
							} else
								$temp_payments[0]['unpaid_balance'] = $temp_payments[$c]['payment_balance_unpaid'];
						}
						if (isset($temp_payments[$c]['payment_interest_unpaid'])) {
							if($temp_payments[$c]['payment_status'] == "partial_not_due") {
								$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
								$temp_payments[0]['unpaid_interest_not_past_due'] += $temp_payments[$c]['payment_interest_unpaid'];
							} else {
								$temp_payments[0]['unpaid_interest'] += $temp_payments[$c]['payment_interest_unpaid'];
							}
						}
						if ($temp_payments[$c]['payment_short'] > 0)
							$temp_payments[0]['sum_payment_short'] += $temp_payments[$c]['payment_short'];
					}
				}
				$c++;
			}

			// Remember that unless all scheduled payments are missed, there is a paid array.
			// If all are missed, then leaving the date range DUE DATE - TODAY is okay.

			// However, leaving the date range for missed payments at DUE DATE - TODAY is not okay
			// if payments have been made AFTER "missed" ones. The range should be:
			// DUE DATE - DATE OF FIRST PAYMENT RECEIVED AFTER MISSED PAYMENT.

			// but we can use DUE DATE- TODAY if a missed payment is the last thing in the array.

			// We can't calculate the days late accurately during array creation, because missed
			// payments come before we know the first payment received after a missed payment.

			// Thus we will iterate through the completed array and adjust the date ranges.
			$i = 0;
			foreach ($temp_payments as &$tpayment) {
				if ($tpayment['payment_status'] == "missed") {
					// Now check if the NEXT tpayment is missed still.
					$c = $i;
					$first_missed_interest = $tpayment['payment_interest_unpaid'];
					while($temp_payments[$c+1]['payment_status'] == "missed") {
						$sum_missed_interest += $temp_payments[$c+1]['payment_interest_unpaid'];
						$c++;
					}
					if ($temp_payments[$c+1]['payment_status'] == "not due yet") {
						// Leave the date range.
						// It is the last thing in the array before not due yets.
						// which means you can use DUE DATE - TODAY.
						break;
					}
					// Fix unpaid balance
					$sum_missed_interest += $first_missed_interest;
					$temp_payments[0]['unpaid_interest'] -= $sum_missed_interest;
					// Redo dates ranges.
					$first_last_payment_date = $temp_payments[$c+1]['payment_date_received'];
					$due_date = $temp_payments[$i]['payment_date_expected'];
					$temp_payments[$i]['payment_days_late'] = format_date_range($due_date, $first_last_payment_date, '#days#');

				}
				$i++;
			}
			unset($tpayment);
			$temp_payments[0]['unpaid_interest_stored'] = $temp_payments[0]['unpaid_interest'];
//			$temp_payments[0]['unpaid_interest'] -= $temp_payments[0]['subtract_unpaid_interest_paid'];
			$temp_payments[0]['past_due'] = ($temp_payments[0]['unpaid_interest'] + $temp_payments[0]['unpaid_balance']) - ($temp_payments[0]['unpaid_balance_not_past_due'] + $temp_payments[0]['unpaid_interest_not_past_due']);

			// Get summarizing info.
			foreach ($temp_payments as $payment)
				$sum_int = ($sum_int + $payment['payment_interest_expected']); // sum of interest payments.

			$sub_int = $temp_payments[0]['subtract_unpaid_interest_paid'];
			$inflated_expected_interest = $temp_payments[0]['inflated_expected_interest'];
			$sum_int = $sum_int - ($sub_int - $inflated_expected_interest);
			$this->new_total_payment_sum = $this->principal + $sum_int;
			$this->total_interest_sum = $sum_int;
			$this->est_interest_savings = $this->total_interest_sum_original - $this->total_interest_sum;
			$this->past_due = $temp_payments[0]['past_due'];
			$this->remaining_balance = $this->past_due = $temp_payments[0]['remaining_balance'];

			//Set Loan status!
			if (!isset($this->status))
				$this->status = "current";
			if ($this->remaining_balance < .01) {
				// Loan is paid off
				if ($this->status == "current")
					$this->status = "paid off";
			} else {
				// Loan is not paid off
				if ($this->status == "paid off")
					$this->status = "current";
			}
			// adjust remaining payments
			// Calculates the percentage paid on all charges using the current total (adjusted for interest changes).
			$percentage_paid = (($temp_payments[0]['total_interest_paid'] + $temp_payments[0]['total_principal_paid']) / $this->new_total_payment_sum);
			$temp_payments[0]['remaining_payments'] = $this->number_payments - ($this->number_payments * $percentage_paid);

			ksort($temp_payments);
			$payments = $temp_payments;
			$this->payments = $payments;
			return $this->payments;
		}
		// Assign the schedule into the payments array.
		// This will get overwritten if payments were made, or if the first payment was missed.
		$this->payments = $this->schedule;
		return $this->payments;
	}

	/**
	 * Deletes all payments, saves a restore point.
	 * 
	 * @param string $delete_all_payments_name The name of the delete history.
	 * @param string $delete_all_payments_reason The reason for the deletion.
	 */
	public function delete_all_payments($delete_all_payments_name, $delete_all_payments_reason) {
		// Create all payments history if it doesn't exist yet.
		if (!$this->history->all_payments)
			$this->history->all_payments = array();

		if (preg_match('/^Auto-save/', $_REQUEST['delete_all_payments_name'])) {
			pines_notice('Cannot save Delete as an Auto-save.');
			pines_redirect(pines_url('com_loan', 'loan/list'));
			return;
		}

		// Create delete info
		$delete_info = array();
		$delete_info['delete_date'] = strtotime('now');
		$delete_info['delete_name'] = $delete_all_payments_name;
		$delete_info['delete_reason'] = $delete_all_payments_reason;
		$delete_info['delete_user'] = $_SESSION['user']->username;
		$delete_info['delete_guid'] = $_SESSION['user_id'];
		$delete_info['delete_remaining_balance'] = $this->payments[0]['remaining_balance'];

		// Create Balance info.
		$all_balance_info = array();
		$all_balance_info['balance'] = $this->balance;
		$all_balance_info['unpaid_balance'] = $this->unpaid_balance;
		$all_balance_info['unpaid_interest'] = $this->unpaid_interest;
		$all_balance_info['past_due'] = $this->past_due;

		// Create the record, using a temp array.
		$delete_all = array();
		$delete_all['pay_by_date'] = $this->pay_by_date;
		$delete_all['all_payments'] = $this->payments;
		$delete_all['all_paid'] = $this->paid;
		$delete_all['all_balance_info'] = $all_balance_info;
		$delete_all['all_edit_payment_history'] = $this->history->edit_payments;
		$delete_all['all_delete'] = $delete_info;

		$this->history->all_payments[] = $delete_all;

		// Now unset everything.
		$this->balance = null;
		$this->pay_by_date = null;
		$this->unpaid_balance = null;
		$this->unpaid_interest = null;
		$this->past_due = null;
		$this->paid = null;
		$this->history->edit_payments = null;
	}

	/**
	 * Matches a payment ID to a payment in the PAID array.
	 * @return array Match info array.
	 */
	public function match_paid_id($edit_payment_id, $use_paid_array = null) {
		// The use_paid_array argument is for passing it any paid array, like an old one, and
		// searching through THAT paid array for a matching id. Used for creating
		// edit history!

		if(!empty($use_paid_array)) {
			//store a copy of the real paid array
			$real_paid = $this->paid;
			$this->paid = $use_paid_array;
		}
		// Find that payment in the paid array so we can get a num.
		// $c needs to start at one, because the [0] position of paid array is misc.
		$c = 0;
		foreach ($this->paid as $paid) {
			if ($paid['payment_id'] == $edit_payment_id) {
				$num = $c;
				$parent = true;
				$match = true;
				break;
			}
			$c++;
		}
		// $num could equal 0 even though no match was found. Wrong!
		// The payment ID either doesn't exist or is in an extra payment.
		if (!$match) {
			// r is 1 because the first entry in paid array is misc.
			$r = 0;
			foreach ($this->paid as $paid) {
				if (!$paid['extra_payments']) {
					$r++;
					continue;
				} else {
					foreach ($paid['extra_payments'] as $extra_payment) {
						if ($extra_payment['payment_id'] == $edit_payment_id) {
							$num = $r;
							$match = true;
							break;
						}
					}
					if ($match)
						break;
				}
				$r++;
			}
			if (!$match) {
				pines_notice('An error occurred. The payment does not exist.');
				$this->print_edit_payments();
				return;
			}
		}
		if ($real_paid)
			$this->paid = $real_paid;
		$this->match_info = array();
		$this->match_info['match'] = $match;
		$this->match_info['parent'] = $parent;
		$this->match_info['num'] = $num;
		return $this->match_info;
	}

	/**
	 * Matches a payment ID to a payment in the PAYMENT array.
	 * @return array One payment history array.
	 */
	public function match_payment_id($parent, $edit_payment_id, $use_payments_array = null) {
		// The use_payment_array argument is for passing it any payment array, like an old one, and
		// searching through THAT payment array for a matching id. Used for creating
		// edit history!

		if (!empty($use_payments_array)) {
			//store a copy of the real paid array
			$real_payments = $this->payments;
			$this->payments = $use_payments_array;
		}

		foreach ($this->payments as $payment) {
			if ($parent) {
				if ($payment['payment_id'] == $edit_payment_id) {
					// Edit payment is a parent, so we should find it in here.
					$store_payment = $payment;
					break;
				}
			} else {
				if (!$payment['extra_payments']) {
					// error
				} else {
					foreach ($payment['extra_payments'] as $extra_payment) {
						if ($extra_payment['payment_id'] == $edit_payment_id)
							$store_payment = $payment;
					}
				}
			}
		}
		if ($real_payments)
			$this->payments = $real_payments;
		$this->store_payment = $store_payment;
		return $this->store_payment;
	}

	/**
	 * Gets edit results of payments after all payments have been processed.
	 * @return array Payment edit history.
	 */
	public function get_edit_results($get_edit_results) {
		global $pines;

		foreach ($get_edit_results as $result) {
			$n = $result['n'];
			$payment_id = $result['payment_id'];
			$skip_logging = $result['skip_logging'];
			if ($skip_logging)
				continue;
			else {
				// this is where you search through all paid and all payments for the current payment.
				// Run Payments array to get Results.
				$this->get_payments_array();
				// Find the Payment in the NEW paid array.
				$this->match_paid_id($payment_id);
				$num = $this->match_info['num'];
				$parent = $this->match_info['parent'];
				$this->match_info = null;

				// Get values for amount of interest, principal, and additional payment.
				if ($parent) {
					$payment_interest = $pines->com_sales->round($this->paid[$num]['payment_interest_paid']);
					$payment_principal = $pines->com_sales->round($this->paid[$num]['payment_principal_paid']);
					$payment_additional = $pines->com_sales->round($this->paid[$num]['payment_additional']);
					if ($payment_additional < .01)
						$payment_additional = 0;
				} else {
					foreach ($this->paid[$num]['extra_payments'] as $extra_payment) {
						if ($extra_payment['payment_id'] == $payment_id) {
							$payment_interest = $pines->com_sales->round($extra_payment['payment_interest_paid']);
							$payment_principal = $pines->com_sales->round($extra_payment['payment_principal_paid']);
							$payment_additional = $pines->com_sales->round($extra_payment['payment_additional']);
							if ($payment_additional < .01)
								$payment_additional = 0;
							break;
						}
					}
				}
				$this->history->edit_payments[$n]['edit_info']['edit_interest'] = $pines->com_sales->round($payment_interest);
				$this->history->edit_payments[$n]['edit_info']['edit_principal'] = $pines->com_sales->round($payment_principal);
				$this->history->edit_payments[$n]['edit_info']['edit_additional'] = $pines->com_sales->round($payment_additional);

				$this->history->edit_payments[$n]['edit_results']['new_paid'] = $this->paid[$num];
				$this->match_payment_id($parent, $payment_id);
				$this->history->edit_payments[$n]['edit_results']['new_payment'] = $this->store_payment;
				$this->store_payment = null;
			}

		}
		return $this->history->edit_payments;
	}

	/**
	 * Gets delete results of payments after all payments have been processed.
	 * @return array Payment edit history.
	 */
	public function get_delete_results($get_delete_results) {
		foreach ($get_delete_results as $result) {
			$n = $result['n'];
			$date_expected_orig = $result['date_expected_orig'];

			// Run Payments array to get Results.
			$this->get_payments_array();
			// Get results here
			$results = array();
			// The payment will have been deleted.
			// We can look at the section for the date due and report back what that says now.
			foreach ($this->payments as $payment) {
				if ($payment['payment_date_expected'] == $date_expected_orig) {
					// This is for that payment section.
					// Get the status.
					$results['new_status'] = $payment['payment_status'];
					$results['new_payment_days_late'] = $payment['payment_days_late'];
					$results['remaining_balance'] = $payment['remaining_balance'];
				} elseif ($payment['scheduled_date_expected'] == $date_expected_orig) {
					$results['new_status'] = $payment['payment_status'];
					$results['new_payment_days_late'] = $payment['payment_days_late'];
					$results['remaining_balance'] = $payment['scheduled_current_balance'];
				}
				$this->history->edit_payments[$n]['delete_results'] = $results;
			}
		}
		return $this->history->edit_payments;
	}

	/**
	 * The process for editing a payment is completed here.
	 */
	public function edit_payment($date_received, $date_expected_orig, $date_receive_old, $date_record_old, $date_recorded, $payment_id, $payment_amount, $error_type, $payment_interest, $payment_principal, $payment_additional) {
		global $pines;

		// Check if anything about this payment has changed, because we don't want to clutter
		// the edit payment logs if it's not necessary.
		$skip_logging = false;
		$sum_old_amount = $pines->com_sales->round($payment_interest + $payment_principal + $payment_additional);
		if ($pines->com_sales->round($payment_amount) == $sum_old_amount && $date_receive_old == $date_received) {
			// No change made. We can avoid making logs and use the original record date.
			$date_recorded = $date_record_old;
			// Remember to delete store_payment and store_paid;
			$skip_logging = true;
		}
		// When making payments, this value will change if I don't save it in this variable.
		$edit_payment_amount = $payment_amount;
		// Change the pay_by_date array to reflect the change in dates and amounts.
		// Only want to change the payment we're going to edit...
		// It doesn't matter if it's a parent or a child payment...
		// in the PBD array, there is no hierarchy. We already know the id# anyway.
		// Keep the original date created. Get it before deleting it from pbd.
		foreach ($this->pay_by_date as $pbd) {
			if ($pbd['payment_id'] == $payment_id) {
				$date_created = $pbd['date_created'];
				break;
			}
		}
		$this->delete_pbd($payment_id);
		$unset = $this->unset;
		$this->unset = null;

		if ($unset) {
			$insert_pbd = array();
			$insert_pbd['date_received'] = (int) $date_received;
			$insert_pbd['date_recorded'] = (int) $date_recorded;
			$insert_pbd['date_created'] = (int) $date_created;
			$insert_pbd['payment_amount'] = $pines->com_sales->round($payment_amount);
			$insert_pbd['payment_id'] = $payment_id;
		} elseif (!$unset) {
			// We know the payment id was found in the paid array, so it's not bad.
			// We just can't find it in the PBD, so we'll create it.
			//
			// This happens when a PAST DUE payment was made with an additional amount.
			// (as you know, past due payments do not contain an additional amount
			// the excess amount generates a new payment.)
			//
			// (Remember - not edited -  with an additional amount, but CREATED!)
			// (when it was created, it was given a unique payment_id, it's just not in the PBD array)
			//
			// Why? It stays as a solid payment unless edited, in case things get changed, and it's not
			// a past due payment anymore, we want the payment to be applied as having an additional amount.
			//
			// But now that we are editing it though, we need to make it editable without changing the parent.
			// Which means we need to create its own seperate PBD and subtract the amount from the parent payment.
			// oh and it works for past due partial payments.
			foreach ($this->pay_by_date as &$pbd_change) {
				// parent_payment_ids are for subtracting the original excess amount from parent.
				// remember excess additional payments will never be an "extra" payment
				// so they are created fresh. (safe to use $this->paid[$num])
				if ($this->paid[$num]['parent_payment_id'] == $pbd_change['payment_id']) {
					$pbd_change['payment_amount'] -= $this->paid[$num]['payment_interest_paid'] + $this->paid[$num]['payment_principal_paid'];
					$changed_parent = true;
				}
			}
			unset($pbd_change);

			if ($changed_parent == true) {
				$insert_pbd = array();
				$insert_pbd['date_received'] = (int) $date_received;
				$insert_pbd['date_recorded'] = (int) $date_recorded;
				$insert_pbd['date_created'] = (int) $date_created;
				$insert_pbd['payment_amount'] = $payment_amount;
				$insert_pbd['payment_id'] = $payment_id;

				// We need to insert this now.

			} else {
				// There's the possibility that the pbd_change payment ID might not exist anymore
				// if it was deleted (when deleting any payment is available)
				// So if it has no match for its payment id, we can just create it (no subtracting necessary).

				$insert_pbd = array();
				$insert_pbd['date_received'] = (int) $date_received;
				$insert_pbd['date_recorded'] = (int) $date_recorded;
				$insert_pbd['date_created'] = (int) $date_created;
				$insert_pbd['payment_amount'] = $pines->com_sales->round($payment_amount);
				$insert_pbd['payment_id'] = $payment_id;
			}
		}

		// Now we need to insert insert_pbd because there might be other existing pbds.
		// And the date may have changed.
		$this->insert_pbd($insert_pbd);

		// Now make the payments again using the new PBD array.
		$this->run_make_payments();

		// Save history of deletes.
		$edit_payment = array();
		$edit_payment['edit_date_recorded'] = (int) $date_recorded;
		$edit_payment['edit_date_expected'] = (int) $date_expected_orig;
		$edit_payment['edit_date_received'] = (int) $date_received;
		$edit_payment['edit_date_received_orig'] = (int) $date_receive_old;
		$edit_payment['edit_date_recorded_orig'] = (int) $date_record_old;
		$edit_payment['edit_payment_id'] = $payment_id;
		$edit_payment['edit_user'] = $_SESSION['user']->username;
		$edit_payment['edit_user_guid'] = $_SESSION['user_id'];
		$edit_payment['edit_reason'] = $error_type;
		$edit_payment['edit_payment'] = $pines->com_sales->round($edit_payment_amount);

		$n = count($this->history->edit_payments) - 1;
		$this->history->edit_payments[$n]['edit_info'] = $edit_payment;
		if ($skip_logging)
			unset($this->history->edit_payments[$n]);

		// Create array to be used for getting edit results of all payments.
		$make_result_info = array();
		$make_result_info['n'] = $n;
		$make_result_info['payment_id'] = $payment_id;
		$make_result_info['skip_logging'] = $skip_logging;

		if (!$this->get_edit_results)
			$this->get_edit_results = array();
		$this->get_edit_results[] = $make_result_info;

		// Run Payments array again so that grid will update with correct information.
		$this->get_payments_array();
	}

	/**
	 * The process for deleting a payment is completed here.
	 */
	public function delete_payment($date_received, $date_expected_orig, $date_recorded, $payment_id, $payment_amount, $error_type, $payment_interest, $payment_principal, $payment_additional) {
		global $pines;

		// When we run make payments the payment amount will change to the last one in the PBD, so save original value.
		$delete_payment_amount = $payment_amount;

		// Find payment in the pay_by_date array and delete it
		$this->delete_pbd($payment_id);

		// Now make the payments again using the new PBD array.
		$this->run_make_payments();

		// Run Payments array again so that grid will update with correct information.
		$this->get_payments_array();

		// Save history of deletes.
		$date_recorded = strtotime('now');
		$delete_payment = array(
			'delete_date_recorded' => $date_recorded,
			'delete_date_expected' => $date_expected_orig,
			'delete_payment_received' => $date_received,
			'delete_payment_id' => $payment_id,
			'delete_user' => $this->user->name,
			'delete_user_guid' => $this->user->guid,
			'delete_reason' => $error_type,
			'delete_payment' => $pines->com_sales->round($delete_payment_amount),
			'delete_interest' => $pines->com_sales->round($payment_interest),
			'delete_principal' => $pines->com_sales->round($payment_principal),
			'delete_additional' => $pines->com_sales->round($payment_additional),
		);
		$n = count($this->history->edit_payments) - 1;
		$this->history->edit_payments[$n]['delete_info'] = $delete_payment;
		$this->history->edit_payments[$n]['delete_results'] = $results;

		// Create array to be used for getting edit results of all payments.
		$make_result_info = array(
			'n' => $n,
			'date_expected_orig' => $date_expected_orig,
		);

		if (!$this->get_delete_results)
			$this->get_delete_results = array();
		$this->get_delete_results[] = $make_result_info;
	}

	/**
	 * Reorganizes the pay_by_date array, renumbering the keys.
	 */
	public function reorder_pbds() {
		foreach ($this->pay_by_date as $pbd) {
			if (empty($re_order))
				$re_order = array();
			$re_order[] = $pbd;
		}

		$this->pay_by_date = $re_order;
	}

	/**
	 * Cleans up the pay by date array.
	 *
	 * Such as finding payments in the paid array that exist but don't exist in
	 * the pay by date array, making them nearly impossible to edit.
	 *
	 * @return array pay_by_date array.
	 */
	public function cleanup_pbds() {
		global $pines;

		if (empty($this->paid))
			return;

		// Finds all missing paid_by_date payments and stores them in $not_found
		foreach ($this->paid as $paid) {
			foreach ($this->pay_by_date as $pbd) {
				$match = false;
				if ($paid['payment_id'] == $pbd['pay_by_date']) {
					$match = true;
					break;
				}
			}
			if (!$match) {
				if (!$not_found)
					$not_found = array();
				if ($paid['parent_payment_id'])
					$not_found[] = $paid;
				else {
					// Found paid, but it doesn't have a parent ID, which means this is an error.
					// Don't make an error, just save it to a variable that we can var_dump later.
					if (!$this->missing_pbd_info)
						$this->missing_pbd_info = array();
					$this->missing_pbd_info[] = $paid;
				}
			}
		}

		if (!empty($not_found)) {
			// Iterates through $not_found and inserts pbd.
			foreach ($not_found as $missing) {
				$insert_pbd = array();
				$insert_pbd['date_received'] = $missing['payment_date_received'];
				$insert_pbd['date_recorded'] = $missing['payment_date_recorded'];
				$insert_pbd['date_created'] = $missing['payment_date_recorded'];
				$insert_pbd['payment_amount'] = $pines->com_sales->round($missing['payment_interest_paid'] + $missing['payment_principal_paid'] + $missing['payment_additional']);
				$insert_pbd['payment_id'] = $missing['payment_id'];
				$this->insert_pbd($insert_pbd);

				// Remember (this missing payment was created from past due additional amounts.)
				// Get information ready to subtract amount from its parent.
				if (!$subtract_these)
					$subtract_these = array();
				$subtract_info = array();
				$subtract_info['parent_payment_id'] = $missing['parent_payment_id'];
				$subtract_info['payment_amount'] = $insert_pbd['payment_amount'];
				$subtract_these[] = $subtract_info;
			}
			foreach ($this->pay_by_date as &$pbd) {
				foreach ($subtract_these as $subtract) {
					if ($pbd['payment_id'] == $subtract['parent_payment_id']) {
						// found the parent!
						// now subtract!
						$pbd['payment_amount'] = $pines->com_sales->round($pbd['payment_amount'] - $subtract['payment_amount']);
						break;
					}
				}
			}
			unset($pbd);
			// Now make the payments again using the clean PBD array.
			$this->run_make_payments();
		}
		return $this->pay_by_date;
	}

	/**
	 * Deletes a payment from the PBD array.
	 */
	public function delete_pbd($edit_payment_id) {
		$r = 0;
		$this->reorder_pbds();
		foreach ($this->pay_by_date as $pbd_change) {
			if ($pbd_change['payment_id'] == $edit_payment_id) {
				// We found a match. But we're going to delete it.
				unset($this->pay_by_date[$r]);
				$unset =  true;
				break;
				// Don't forget to insert this!
			}
			$r++;
		}
		$this->unset = $unset;
	}

	/**
	 * Runs make_payment function on all payments from the PBD array.
	 */
	public function run_make_payments() {
		$this->paid = null;
		// This destroys the current paid array, and makes all the payments all over again.
		foreach ($this->pay_by_date as $pbd) {
			// Run get_payments_array so we get an updated next-payment-due for date expected.
			$this->get_payments_array();
			if ($this->missed_first_payment == true && $first_time != true) {
				$date_expected = $this->payments[0]['first_payment_missed'];
				$first_time = true;
			} elseif (isset($this->payments[0]['next_payment_due']))
				$date_expected = $this->payments[0]['next_payment_due'];
			else
				$date_expected = strtotime($this->first_payment_date);
			$payment_amount = $pbd['payment_amount'];
			$date_received = $pbd['date_received'];
			$date_recorded = $pbd['date_recorded'];
			$payment_id = $pbd['payment_id'];
			if ($date_received < $date_expected) {
				$this->past_due = null;
			}
			$this->make_payment($payment_amount, $date_expected, $date_received, $date_recorded, $payment_id);
		}
	}


	/**
	 * Inserts a payment into the PBD array.
	 */
	public function insert_pbd($insert_pbd) {
		$temp_pay_by_date = $this->pay_by_date;
		$re_insert = array();
		foreach ($temp_pay_by_date as $pbd) {
			if ($insert_pbd['date_received'] > $pbd['date_received']) {
				// Greater one is more recent.
				// This area represents all of the payments that occur before the insert.
				// Even if it was in the right order, because we start at the beginning of the
				// pbd from the foreach, we'll hit this area.
				$re_insert[] = $pbd;
			} elseif ($insert_pbd['date_received'] == $pbd['date_received'] && $inserted != true) {
				if ($insert_pbd['date_created'] < $pbd['date_created']) {
					$re_insert[] = $insert_pbd;
					$re_insert[] = $pbd;
					$inserted = true;
				} else {
					// Only happens if the dates are equal, to preserve a consistent order and avoid confusion.
					$re_insert[] = $pbd;
					$re_insert[] = $insert_pbd;
					$inserted = true;
				}
			} elseif($insert_pbd['date_received'] < $pbd['date_received'] && $inserted != true) {
				// It could be going right back in it's place,
				// or it could be getting inserted from being out of order.
				$re_insert[] = $insert_pbd;
				$re_insert[] = $pbd;
				$inserted = true;
			} else {
				// this is for all the payments that are AFTER the insert.
				// notice how the inserts date would fail the first if.
				// and we don't want it to continue to insert, so once
				// inserted is true, we skip to here.
				$re_insert[] = $pbd;
			}
		}
		if (!$inserted) {
			// It was never inserted becaused it needs to be appended to the very end.
			$n = count($re_insert);
			$re_insert[$n] = $insert_pbd;
		}
		$temp_pay_by_date = $re_insert;
		$this->pay_by_date = $temp_pay_by_date;
	}

	/**
	 * Makes a payment and adds it to the paid array with information passed to it from pay_by_date array.
	 */
	public function make_payment($payment_amount, $date_expected, $date_received, $date_recorded, $payment_id) {
		global $pines;

		if (!empty($this->paid)) {
			foreach ($this->paid as $paid) {
				if (!empty($paid))
					$num++;
			}
			$num--;
		} else
			$num = 0;
		$this->past_due = $pines->com_sales->round($this->past_due );
		if ($date_received < $date_expected)
			$this->past_due = null;

		$make_additional_payment = false;
		// Create/Append to paid array.

		// This is for extra payments that have been made on a partial past due payment.
		if ($this->paid[$num]['payment_status'] == "partial") {
			// the last payment made was a past due amount, and it was only partially paid.
			// Needs to attach to previous paid array matching this date expected.
			foreach ($this->payments as $payment) {
				if ($payment['payment_type'] == "past_due" && $payment['payment_status'] == "partial") {
					// we need the payment date for a past due partial payment.
					// there should only ever be one of these, because only the last payment can be a partial payment.
					$partial_expected_date = strtotime("+1 day", $payment['payment_date_expected']);
				} elseif ($payment['payment_status'] == "partial")
					$partial_expected_date = $payment['payment_date_expected'];
			}
			foreach ($this->paid as &$paid) {
				$temp_extra = array();
				if ($paid['payment_date_expected'] == $partial_expected_date || $paid['payment_date_expected'] == strtotime("-1 day", $partial_expected_date)) {
					// get possible extra arrays that already exist
					// because we need to get how much we've paid off on this payment
					// to calculate how much is needed to finish the payment.
					if (!empty($paid['extra_payments'])) {
						// only if this exists, which it might not.
						foreach ($paid['extra_payments'] as $old_extra) {
							$interest_paid += $old_extra['payment_interest_paid'];
							$balance_paid += $old_extra['payment_principal_paid'];
						}
					}
					// The partial payment must also be added to paid amount.
					$interest_paid += $paid['payment_interest_paid'];
					$balance_paid += $paid['payment_principal_paid'];

					$unpaid_interest = (($paid['payment_interest_expected'] - $interest_paid) > 0) ? $paid['payment_interest_expected'] - $interest_paid : 0.00;
					$unpaid_balance = (($paid['payment_principal_expected'] - $balance_paid) > 0) ? $paid['payment_principal_expected'] - $balance_paid : 0.00;

					$extra = array();
					$extra['payment_type'] = "past_due";
					$extra['payment_date_expected'] = $paid['payment_date_expected'];
					$extra['payment_date_received'] = $date_received;
					$extra['payment_date_recorded'] = $date_recorded;
					$extra['payment_id'] = $payment_id;
					if ($unpaid_interest > 0 && $payment_amount <= $unpaid_interest) {
						// only potentially covers interest expected.
						$extra['payment_status'] = "partial";
						$extra['payment_interest_paid'] = $payment_amount;
						$extra['payment_principal_paid'] = 0;
					} else {
						$extra['payment_interest_paid'] = $unpaid_interest;
						if (($payment_amount - $unpaid_interest) >= $unpaid_balance) {
							// at least entire balance has to be paid for this to work here.
							$extra['payment_principal_paid'] = $unpaid_balance;
							$additional = $payment_amount - ($extra['payment_interest_paid'] + $extra['payment_principal_paid']);
							if ($additional >= .01)
								$make_additional_payment = true;
							$paid['payment_status'] = 'paid_late';
						} else {
							// balance not fully covered
							$extra['payment_principal_paid'] = $payment_amount - $unpaid_interest;
							$extra['payment_additional'] = 0;
							$paid['payment_status'] = 'partial';
						}

					}
				}
				if (!empty($extra)) {
					$paid['extra_payments'][] = $extra;
					$paid_array_adjusted = true;
				}
				// if final extra payment went over, into the next payment due.
				if ($make_additional_payment) {
					$new_payment = array();
					$new_payment['payment_status'] = "payment";
					$new_payment['payment_type'] = "payment";
					$new_payment['payment_date_expected'] = $date_expected;
					$new_payment['payment_date_received'] = $date_received;
					$new_payment['payment_date_recorded'] = strtotime("+1 second", $date_recorded);
					$new_payment['payment_id'] = uniqid();
					$new_payment['parent_payment_id'] = $payment_id;

					foreach ($this->payments as $payment) {
						if ($payment['scheduled_date_expected'] == $this->payments[0]['next_payment_due']) {
							if ($additional < $payment['payment_interest_expected']) {
								// interest not covered.
								$new_payment['payment_interest_paid'] = $additional;
								$new_payment['payment_principal_paid'] = 0.00;
								$partial = true;
							} else {
								$new_payment['payment_interest_paid'] = $payment['payment_interest_expected'];
								if (($additional - $payment['payment_interest_expected']) < $payment['payment_principal_expected']){
									// principal not covered
									$new_payment['payment_principal_paid'] = $additional - $new_payment['payment_interest_paid'];
									$partial = true;
								} else {
									// principal covered, possibly additional amount.
									$new_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
									$new_payment['additional_payment'] = $additional - ($new_payment['payment_interest_paid'] + $new_payment['payment_principal_paid']);
								}
							}

							$new_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
							$new_payment['payment_interest_expected'] = $payment['payment_interest_expected'];

							if ($partial)
								$new_payment['payment_status'] = 'partial_not_due';
							else
								$new_payment['payment_status'] = 'paid';
						}
					}
				}
			}
			unset($paid);
		}
		// only for when a partial not due payment has been MADE and an extra payment
		// is needed. This does not generate the partial payment, rather the extra payment.
		elseif ($this->paid[$num]['payment_status'] == "partial_not_due") {
			// The last payment was not due, but it was partial
			// Needs to attach to previous paid array matching this date expected
			foreach ($this->paid as &$paid) {
				$temp_extra = array();
				if ($this->payments[0]['next_payment_due'] == $paid['payment_date_expected']) {
					// get possible extra arrays that already exist
					// because we need to get how much we've paid off on this payment
					// to calculate how much is needed to finish the payment.
					if (!empty($paid['extra_payments'])) {
						// only if this exists, which it might not.
						foreach ($paid['extra_payments'] as $old_extra) {
							$interest_paid += $old_extra['payment_interest_paid'];
							$balance_paid += $old_extra['payment_principal_paid'];
						}
					}

					// The partial payment must also be added to paid amount.
					$interest_paid += $paid['payment_interest_paid'];
					$balance_paid += $paid['payment_principal_paid'];

					$unpaid_interest = (($paid['payment_interest_expected'] - $interest_paid) > 0) ? $paid['payment_interest_expected'] - $interest_paid : 0;
					$unpaid_balance = (($paid['payment_principal_expected'] - $balance_paid) > 0) ? $paid['payment_principal_expected'] - $balance_paid : 0;

					$extra = array();
					$extra['payment_type'] = "payment";
					$extra['payment_date_expected'] = $paid['payment_date_expected'];
					$extra['payment_date_received'] = $date_received;
					$extra['payment_date_recorded'] = $date_recorded;
					$extra['payment_id'] = $payment_id;
					if ($unpaid_interest > 0 && $payment_amount <= $unpaid_interest) {
						// only potentially covers interest expected.
						$extra['payment_status'] = "partial_not_due";
						$extra['payment_interest_paid'] = $payment_amount;
						$extra['payment_principal_paid'] = 0.00;
					} else {
						$extra['payment_interest_paid'] = $unpaid_interest;
						if (($pines->com_sales->round($payment_amount - $unpaid_interest)) >= $pines->com_sales->round($unpaid_balance)) {
							// at least entire balance has to be paid for this to work here.
							$extra['payment_principal_paid'] = $unpaid_balance;
							$extra['payment_additional'] = $pines->com_sales->round($payment_amount - ($extra['payment_interest_paid'] + $extra['payment_principal_paid']));
							if ($extra['payment_date_received'] > $extra['payment_date_expected']) {
								$additional = $extra['payment_additional'];
								if ($additional >= .01) {
									$additional = $extra['payment_additional'];
									$extra['payment_additional'] = 0;
									$make_additional_payment = true;
								}
							}
							$extra['payment_status'] = 'paid';
							$paid['payment_status'] = 'paid';
						//$this->payments[0]['next_payment_due'] = $scheduled_payment_dates[$c+1]['due_date'];
						} else {
							// balance not fully covered
							$extra['payment_principal_paid'] = $payment_amount - $unpaid_interest;
							$extra['payment_additional'] = 0;
							$extra['payment_status'] = 'partial_not_due';
							$paid['payment_status'] = 'partial_not_due';
						}

					}
				}
				if (!empty($extra)) {
					$paid['extra_payments'][] = $extra;
					$paid_array_adjusted = true;
				}
				// if final extra payment went over, into the next payment due.
				if ($make_additional_payment) {

					$new_date_expected = strtotime('+1 month', $date_expected);

					$new_payment = array();
					$new_payment['payment_status'] = "payment";
					$new_payment['payment_type'] = "payment";
					$new_payment['payment_date_expected'] = $new_date_expected;
					$new_payment['payment_date_received'] = $date_received;
					$new_payment['payment_date_recorded'] = strtotime("+1 second", $date_recorded);
					$new_payment['payment_id'] = uniqid();
					$new_payment['parent_payment_id'] = $payment_id;

					foreach ($this->payments as $payment) {
						if ($payment['scheduled_date_expected'] == $new_date_expected) {
							if ($additional < $payment['payment_interest_expected']) {
								// interest not covered.
								$new_payment['payment_interest_paid'] = $additional;
								$new_payment['payment_principal_paid'] = 0.00;
								$partial = true;
							} else {
								$new_payment['payment_interest_paid'] = $payment['payment_interest_expected'];
								if (($additional - $payment['payment_interest_expected']) < $payment['payment_principal_expected']){
									// principal not covered
									$new_payment['payment_principal_paid'] = $additional - $new_payment['payment_interest_paid'];
									$partial = true;
								} else {
									// principal covered, possibly additional amount.
									$new_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
									$new_payment['additional_payment'] = $additional - ($new_payment['payment_interest_paid'] + $new_payment['payment_principal_paid']);
								}
							}

							$new_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
							$new_payment['payment_interest_expected'] = $payment['payment_interest_expected'];

							if ($partial)
								$new_payment['payment_status'] = 'partial_not_due';
							else
								$new_payment['payment_status'] = 'paid';
						}
					}
				}
			}
			unset($paid);
		} elseif ($this->past_due >= .01 && $this->paid[$num]['payment_status'] != "partial") {
			// This creates a past due payment, but does not deal with extra_past_due payments.
			if ($payment_amount < $this->past_due) {
				// $payment amount is only partial.
				$temp_past_due_paid['payment_type'] = 'past_due';
				$temp_past_due_paid['payment_date_expected'] = strtotime('+1 day', $date_expected);
				$temp_past_due_paid['payment_date_received'] = $date_received;
				$temp_past_due_paid['payment_date_recorded'] = $date_recorded;
				$temp_past_due_paid['payment_id'] = $payment_id;
				$temp_past_due_paid['payment_amount_paid'] = $payment_amount;
				$temp_past_due_paid['payment_interest_expected'] = $this->payments[0]['unpaid_interest'];
				$temp_past_due_paid['payment_principal_expected'] = $this->payments[0]['unpaid_balance'];
				if ($payment_amount < $temp_past_due_paid['payment_interest_expected']) {
					// interest amount not fully covered
					$temp_past_due_paid['payment_interest_paid'] = $payment_amount;
					$temp_past_due_paid['payment_principal_paid'] = 0.00;
				} else {
					// interest is fully paid:
					$temp_past_due_paid['payment_interest_paid'] = $temp_past_due_paid['payment_interest_expected'];
					if (($payment_amount - $temp_past_due_paid['payment_interest_expected']) <  $temp_past_due_paid['payment_principal_expected']) {
						// Principal not covered
						$temp_past_due_paid['payment_principal_paid'] = ($payment_amount - $temp_past_due_paid['payment_interest_paid']);
					}
				}
				$temp_past_due_paid['payment_status'] = 'partial';
			} elseif ($payment_amount == $this->past_due) {
				// $payment amount is exact.
				$temp_past_due_paid['payment_type'] = 'past_due';
				$temp_past_due_paid['payment_date_expected'] = strtotime('+1 day', $date_expected);
				$temp_past_due_paid['payment_date_received'] = $date_received;
				$temp_past_due_paid['payment_date_recorded'] = $date_recorded;
				$temp_past_due_paid['payment_id'] = $payment_id;
				$temp_past_due_paid['payment_amount_paid'] = $this->past_due;
				$temp_past_due_paid['payment_status'] = 'paid_late';
			} else {
				// $payment amount will cover past due amount + more.
				// Past due payment.
				$temp_past_due_paid['payment_type'] = 'past_due';
				$temp_past_due_paid['payment_date_expected'] = strtotime('+1 day', $date_expected);
				$temp_past_due_paid['payment_date_received'] = $date_received;
				$temp_past_due_paid['payment_date_recorded'] = $date_recorded;
				$temp_past_due_paid['payment_id'] = $payment_id;
				$temp_past_due_paid['payment_amount_paid'] =  $this->past_due;
				$temp_past_due_paid['payment_status'] = 'paid_late';

				// The additional payment amount.
				$payment_amount -= $this->past_due;
				foreach ($this->payments as $payment) {
					if ($payment['scheduled_date_expected'] == $this->payments[0]['next_payment_due']) {
						if ($payment_amount <= $payment['payment_interest_expected']) {
							// only potentially covers interest expected.
							$temp_payment_paid['payment_interest_paid'] = $payment_amount;
							$temp_payment_paid['payment_principal_paid'] = 0.00;
							$partial = true;
						} else {
							// interest is covered + more.
							$temp_payment_paid['payment_interest_paid'] = $payment['payment_interest_expected'];
							if ($payment_amount <= $payment['payment_principal_expected']) {
								// only potentially covers principal expected.
								$temp_payment_paid['payment_principal_paid'] = $payment_amount - $temp_payment_paid['payment_interest_paid'];
								if ($temp_payment_paid['payment_principal_paid'] < $payment['payment_principal_expected'])
									$partial = true;
							} else {
								// principal is covered + more.
								$temp_payment_paid['payment_principal_paid'] = $payment['payment_principal_expected'];
								$temp_payment_paid['payment_additional'] = $pines->com_sales->round($payment_amount - ($temp_payment_paid['payment_interest_paid'] + $temp_payment_paid['payment_principal_paid']));
							}

						}
					}
				}
				if (!isset($temp_payment_paid['payment_additional']))
					$temp_payment_paid['payment_additional'] = 0.00;
				$temp_payment_paid['payment_date_expected'] = $this->payments[0]['next_payment_due'];;
				$temp_payment_paid['payment_date_received'] = $date_received;
				$temp_payment_paid['payment_date_recorded'] = $date_recorded;
				$temp_payment_paid['parent_payment_id'] = $payment_id;
				$temp_payment_paid['payment_id'] = uniqid();
				if ($partial)
					$temp_payment_paid['payment_status'] = 'partial_not_due';
				else
					$temp_payment_paid['payment_status'] = 'paid';
			}
		} else {
			// No past due amount exist, payment is normal.
			foreach ($this->payments as $payment) {
				if ($payment['scheduled_date_expected'] == $date_expected || $payment['payment_date_expected'] == $date_expected) {
					if ($payment_amount < $payment['payment_interest_expected']) {
						// does not cover full interest payment.
						$temp_payment_paid['payment_interest_paid'] = $payment_amount;
						$temp_payment_paid['payment_principal_paid'] = 0.00;
						$temp_payment_paid['payment_interest_expected'] = $payment['payment_interest_expected'];
						$temp_payment_paid['payment_principal_expected'] = $payment['payment_principal_expected'];
						$partial = true;
					} else {
						// interest is covered at least with possibly more.
						$temp_payment_paid['payment_interest_paid'] = $pines->com_sales->round((float) $payment['payment_interest_expected']);
						if (($payment_amount - $temp_payment_paid['payment_interest_paid']) < $payment['payment_principal_expected']) {
							// does not fully cover principal.
							$temp_payment_paid['payment_principal_paid'] = $payment_amount - $temp_payment_paid['payment_interest_paid'];
							if ($temp_payment_paid['payment_principal_paid'] < $payment['payment_principal_expected']) {
								$temp_payment_paid['payment_interest_expected'] = $payment['payment_interest_expected'];
								$temp_payment_paid['payment_principal_expected'] = $payment['payment_principal_expected'];
								$partial = true;
							}
						} else {
							// principal is covered with possibly more.
							$temp_payment_paid['payment_principal_paid'] = $payment['payment_principal_expected'];
							$temp_payment_paid['payment_additional'] =  $payment_amount - ($temp_payment_paid['payment_interest_paid'] + $temp_payment_paid['payment_principal_paid']);
						}

					}
				}
			}
			if (!isset($temp_payment_paid['payment_additional']))
				$temp_payment_paid['payment_additional'] = 0.00;
			$temp_payment_paid['payment_date_expected'] = $date_expected;
			$temp_payment_paid['payment_date_received'] = $date_received;
			$temp_payment_paid['payment_date_recorded'] = $date_recorded;
			$temp_payment_paid['payment_id'] = $payment_id;
			if (isset($partial) && $partial == true)
				$temp_payment_paid['payment_status'] = 'partial_not_due';
			else
				$temp_payment_paid['payment_status'] = 'paid';
		}

		// assign above payments to temporary paid array.
		$temp_paid = array();
		$temp_paid[0] = array();


		// Creating a Past Due payment
		if (!empty($temp_past_due_paid))
			$temp_paid[] = $temp_past_due_paid;
		// A normal Payment.
		if (!empty($temp_payment_paid))
			$temp_paid[] = $temp_payment_paid;


		// Assign paid array and save it!
		if (empty($this->paid)) {
			// create paid array
			$this->paid = array();
			$this->paid = $temp_paid;
		} elseif ($paid_array_adjusted) {
			// An additional payment after paid array has been altered with extra payments.
			if (!empty($new_payment))
				$this->paid[] = $new_payment;
			$this->paid = $this->paid;
		} else {
			// append to paid array
			if ($this->past_due > 0)
				$this->paid[] = $temp_past_due_paid;
			if (!empty($temp_payment_paid))
				$this->paid[] = $temp_payment_paid;
		}



		// Paid Info
		$num_payments_paid = count($this->paid) - 1;
		$this->paid[0] = null;
		if ($num_payments_paid == 0)
			unset($this->paid);
		else
			$this->paid[0]['num_payments_paid'] = $num_payments_paid;

		if (!$delete_payment)
			$this->paid = $this->array_values_recursive($this->paid);


		// Run Payments array again so that grid will update with correct information.
		$this->get_payments_array();
	}

	/**
	 * Reset array values recursively.
	 * @return array The array.
	 */
	public function array_values_recursive($arr) {
		$arr = array_values($arr);
		foreach($arr as $key => $val)
			if(array_values($val) === $val)
				$arr[$key] = array_values_recursive($val);

		return $arr;
	}
}

?>