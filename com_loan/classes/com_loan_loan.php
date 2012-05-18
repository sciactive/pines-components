<?php
/**
 * com_loan_loan class.
 *
 * @package Components\loan
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
 * @package Components\loan
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

	public function info($type) {
		switch ($type) {
			case 'name':
				return "Loan $this->id";
			case 'type':
				return 'loan';
			case 'types':
				return 'loans';
			case 'url_view':
				if (gatekeeper('com_loan/viewloan'))
					return pines_url('com_loan', 'loan/overview', array('id' => $this->guid));
				break;
			case 'url_edit':
				if (gatekeeper('com_loan/editloan'))
					return pines_url('com_loan', 'loan/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_loan/listloans'))
					return pines_url('com_loan', 'loan/list');
				break;
			case 'icon':
				return 'picon-view-bank';
		}
		return null;
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
	 * Calculate the loan schedule.
	 * @throws com_loan_loan_terms_not_possible_exception If the loan terms are not possible.
	 */
	public function calculate_loan() {
		global $pines;
		// Calculate rate per period.
		$base = (1 + ($this->apr / $this->compound_frequency));
		$pow = ($this->compound_frequency / $this->payment_frequency);
		$this->rate_per_period = (pow($base, $pow)) - 1;


		// Calculate basics of amortization schedule.
		if ($this->term_type == "years")
			$nper = $this->payment_frequency * $this->term;
		elseif ($this->term_type == "months")
			$nper = $this->payment_frequency * (($this->term) / 12);

		// Calculate the Frequency Payment
		$frequency_payment = -1*($pines->com_financial->PMT(($this->apr / 100) / $this->payment_frequency, $nper, $this->principal, 0.0, $this->payment_type));
		$frequency_payment = round($frequency_payment, 2);
		$this->frequency_payment = $frequency_payment;

		// Create payments array for amortization table.
		$schedule = array();
		$sum_int = 0;
		$sum_prin = 0;
		$i = 0;
		// this loop is only for creating the variables for the amortization table.
		for ($i = 0; $i < $nper; $i++) {
			$schedule[$i] = array();
			if ($i == 0) {
				$schedule[$i]['scheduled_date_expected'] = $this->first_payment_date;
				$schedule[$i]['scheduled_current_balance'] = $this->principal;
				$schedule[$i]['payment_interest_expected'] = $pines->com_sales->round(($schedule[$i]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
				$schedule[$i]['payment_principal_expected'] = $frequency_payment - $schedule[$i]['payment_interest_expected'];
				$schedule[$i]['payment_interest_paid'] = 0.00; // no payments made at time of loan creation.
				$schedule[$i]['payment_principal_paid'] = 0.00; // no payments made at time of loan creation.
				$schedule[$i]['payment_amount_paid'] = 0.00; // no payments made at time of loan creation.
				$schedule[$i]['scheduled_balance'] = $schedule[$i]['scheduled_current_balance'] - $schedule[$i]['payment_principal_expected'];
				$schedule[$i]['payment_amount_expected'] = $schedule[$i]['payment_principal_expected'] + $schedule[$i]['payment_interest_expected'];
				$schedule[$i]['next_payment_due_amount'] = $schedule[$i]['payment_interest_expected'] + $schedule[$i]['payment_principal_expected'];
			} else {
				$schedule[$i]['scheduled_date_expected'] = strtotime('+1 month',$schedule[$i-1]['scheduled_date_expected']);
				$schedule[$i]['scheduled_current_balance'] = $schedule[$i - 1]['scheduled_balance'];
				$schedule[$i]['payment_interest_expected'] = $pines->com_sales->round(($schedule[$i]['scheduled_current_balance'] * $this->rate_per_period) / 100 , true);
				if ($schedule[$i]['scheduled_current_balance'] < $frequency_payment || ($schedule[$i]['scheduled_current_balance'] - $frequency_payment) <= 1) {
					$schedule[$i]['payment_principal_expected'] = $schedule[$i]['scheduled_current_balance'];
				} else {
					$schedule[$i]['payment_principal_expected'] = $frequency_payment - $schedule[$i]['payment_interest_expected'];
				}
				$schedule[$i]['payment_amount_expected'] = $schedule[$i]['payment_principal_expected'] + $schedule[$i]['payment_interest_expected'];
				$schedule[$i]['payment_amount_paid'] = 0.00; // no payments made at time of loan creation.
				$schedule[$i]['scheduled_balance'] = $schedule[$i]['scheduled_current_balance'] - $schedule[$i]['payment_principal_expected'];
			}
			$schedule[$i]['additional_payment'] = null;
			$schedule[$i]['payment_status'] = "not due yet" ;
			$sum_int = $sum_int + $schedule[$i]['payment_interest_expected'];
			$sum_prin = $sum_prin + $schedule[$i]['payment_principal_expected'];
		}
		$this->schedule = $schedule;
		// Calculate remaining variables.
		$this->number_payments = count($this->schedule); // needs to happen after payments array.
		$this->total_payment_sum = $sum_int + $sum_prin; //sum of all interests and all principals
		$this->total_interest_sum_original = $sum_int;
		$this->total_interest_sum = $sum_int;
		$this->est_interest_savings = $this->total_interest_sum_original - $this->total_interest_sum;

		$this->status = "current";

		if ($this->schedule[$this->number_payments - 1]['scheduled_balance'] >= .01)
			throw new com_loan_loan_terms_not_possible_exception();
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
				$scheduled_payment_dates[$c] = $this->first_payment_date;
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
//			foreach ($this->paid as &$paid) {
//				if ($paid['payment_date_expected'] < $today && $paid['payment_status'] == "partial_not_due") {
//					$paid['payment_status'] = "partial";
//				}
////				// Get num of extra payments, if any.
////				$num_ex = count($paid['extra_payments']) - 1;
////				// Check Parent Payments.
////				if ($paid['payment_date_received'] > $paid['payment_date_expected'] && $paid['payment_status'] == "partial_not_due") {
////					// Payments are late.
////					$paid['payment_days_late'] = format_date_range($paid['payment_date_expected'], $paid['payment_date_received'], '#days#');
////					$paid['payment_status'] = "partial";
////				} elseif ($paid['extra_payments'][$num_ex]['payment_date_received'] > $paid['extra_payments'][$num_ex]['payment_date_expected']) {
////					// This only checks the last extra payment on the whole payment, because it's what will determine
////					// if the whole payment should now be considered late or partial late.
////					// Find out short amount
////					$parent_paid = $paid['payment_interest_paid'] + $paid['payment_principal_paid'];
////					foreach ($paid['extra_payments'] as $extra_payment) {
////						$extra_paid += $extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'];
////					}
////					$paid_amount = $parent_paid + $extra_paid;
////					// Not going to worry about additional, because I just need to know the expected amounts are paid or not.
////					$paid_expected = $paid['payment_interest_expected'] + $paid['payment_principal_expected'];
////
////					$payment_short = $paid_expected - $paid_amount;
////
////					if ($payment_short >= 0.01) {
////						// If it's short, it must be partial.
////						$paid['extra_payments'][$num_ex]['payment_status'] = "partial";
////						$paid['payment_status'] = "partial";
////					} else {
////						// If no short amount exists, then it's paid_late
////						$paid['extra_payments'][$num_ex]['payment_status'] = "paid_late";
////						$paid['payment_status'] = "paid_late";
////					}
////					$paid['payment_days_late'] = format_date_range($paid['extra_payments'][$num_ex]['payment_date_expected'], $paid['extra_payments'][$num_ex]['payment_date_received'], '#days#');
////				}
//			}
//			unset($paid);
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
			ksort($temp_payments);
			$payments = $temp_payments;
			$this->payments = $payments;
			$past_due = $this->get_pastdue($this->payments, $this->paid);
			//$this->past_due = $past_due['interest'] + $past_due['principal'];
			// Get pastdue
			$past_due = $this->get_pastdue($temp_payments, $this->paid);
			$this->past_due = $temp_payments[0]['past_due'] = $pines->com_sales->round($past_due['interest'] + $past_due['principal']);
			$temp_payments[0]['unpaid_balance'] = $pines->com_sales->round($past_due['short_principal']);
			$temp_payments[0]['unpaid_interest'] = $pines->com_sales->round($past_due['short_interest']);
			$temp_payments[0]['sum_payment_short'] = $pines->com_sales->round($past_due['short_interest'] + $past_due['short_principal']);
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
						if ($due_date == $paid['payment_date_expected']) {
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
									if (empty($temp_payments[$c]['payment_id'])) {
										$temp_payments[$c]['payment_id_parent'] = $paid['payment_id_parent'];
										unset($temp_payments[$c]['payment_id']);
									}
									// Calculate days payment late if paid_late.
									if (!$paid['payment_days_late'])
										$temp_payments[$c]['payment_days_late'] = (format_date_range($paid['payment_date_expected'], $paid['payment_date_received'], '#days#'));
									else
										$temp_payments[$c]['payment_days_late'] = $paid['payment_days_late'];
								} else {
									$temp_payments[$c]['payment_date_expected'] = $paid['payment_date_expected'];
									$temp_payments[$c]['payment_date_received'] = $paid['payment_date_received'];
									$temp_payments[$c]['payment_date_recorded'] = $paid['payment_date_recorded'];
									$temp_payments[$c]['payment_id'] = $paid['payment_id'];
									if (empty($temp_payments[$c]['payment_id'])) {
										$temp_payments[$c]['payment_id_parent'] = $paid['payment_id_parent'];
										unset($temp_payments[$c]['payment_id']);
									}
									// Calculate days payment late if paid_late.
									if (!$paid['payment_days_late'])
										$temp_payments[$c]['payment_days_late'] = (format_date_range($paid['payment_date_expected'], $paid['payment_date_received'], '#days#'));
									else
										$temp_payments[$c]['payment_days_late'] = $paid['payment_days_late'];
								}
								// Checks for first payment made
								// Because sometimes a payment_amount_paid doesn't exist, add up paid amounts to get it.
								if (($paid['payment_interest_paid'] >= .01 || $paid['payment_principal_paid'] >= .01) && empty($paid['payment_amount_paid'])) {
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
//									$temp_payments[0]['inflated_expected_interest'] += (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
								} else {
									$temp_payments[$c]['scheduled_current_balance'] = $temp_payments[$c-1]['scheduled_balance'];
									$temp_payments[$c]['current_balance'] = $temp_payments[$c-1]['remaining_balance'];
									// Establish expected interest and principal based off of unpaid balance & interest.
									
									//$temp_payments[0]['inflated_expected_interest'] += (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
//									$temp_payments[$c]['payment_interest_unpaid'] = ($temp_payments[$c-1]['payment_interest_unpaid'] - $temp_payments[$c]['payment_interest_paid']) + (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
								}
								// Get Expected values
								$temp_payments[$c]['payment_interest_expected'] = $paid['payment_interest_expected'];
								$temp_payments[$c]['payment_principal_expected'] = $paid['payment_principal_expected'];
								$temp_payments[$c]['payment_interest_unpaid_expected'] = $paid['payment_interest_unpaid_expected'];
								$temp_payments[$c]['payment_principal_unpaid_expected'] = $paid['payment_principal_unpaid_expected'];
								// Get paid and unpaid amounts.
								$temp_payments[$c]['payment_interest_paid'] = $paid['payment_interest_paid'];
								$temp_payments[$c]['payment_interest_unpaid'] = $paid['payment_interest_unpaid_remainder'];
								$temp_payments[$c]['payment_principal_paid'] = $paid['payment_principal_paid'];
								$temp_payments[$c]['payment_balance_unpaid'] = $paid['payment_principal_unpaid_remainder'];
								// We keep these numbers even for partial past due, and we'll add extra payments if necessary to these amounts.
//								if (!$temp_payments[$c]['payment_interest_paid'])
//									$temp_payments[$c]['payment_interest_paid'] = $paid['payment_interest_paid'];
//								if (!$temp_payments[$c]['payment_principal_paid'])
//									$temp_payments[$c]['payment_principal_paid'] = $paid['payment_principal_paid'];
//								if (!$temp_payments[$c]['payment_additional'])
//									$temp_payments[$c]['payment_additional'] = $paid['payment_additional'];
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
								else {
									if ($temp_payments[$c-1]['payment_type'] != 'past_due')
										$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c-1]['remaining_balance'] - ($temp_payments[$c]['payment_principal_expected']);
									else
										$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c-1]['scheduled_balance'] - ($temp_payments[$c]['payment_principal_expected']);
								}
								$temp_payments[$c]['remaining_balance'] = $temp_payments[$c]['current_balance'] - ($temp_payments[$c]['payment_principal_paid']);
								
								// Again, why would we not count this?
								// Don't count short amount if the balance has been paid since, even partially.
//								if (is_int($this->payments[0]['last_payment_made']) && $due_date < $this->payments[0]['last_payment_made']) {
//									// Don't count short amount.
//									$temp_payments[$c]['payment_short'] = 0.00;
//								} else
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
								if (empty($temp_payments[$c]['payment_id'])) {
									$temp_payments[$c]['payment_id_parent'] = $paid['payment_id_parent'];
									unset($temp_payments[$c]['payment_id']);
								}
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
						$temp_payments[$c]['payment_interest_expected'] = (float) $pines->com_sales->round(($temp_payments[$c]['scheduled_current_balance'] * $this->rate_per_period) / 100, true);
						$temp_payments[$c]['payment_principal_expected'] = $this->frequency_payment - $temp_payments[$c]['payment_interest_expected'];
						$temp_payments[$c]['payment_interest_paid'] = 0.00; // Because it is missed and no payments have ever bee made.
						$temp_payments[$c]['payment_principal_paid'] = 0.00; // Because it is missed and no payments have ever bee made.
						$temp_payments[$c]['payment_amount_paid'] = $temp_payments[$c]['payment_interest_paid'] + $temp_payments[$c]['payment_principal_paid'];
						$temp_payments[$c]['scheduled_balance'] = $temp_payments[$c]['scheduled_current_balance'] - $temp_payments[$c]['payment_principal_expected'];
						$temp_payments[$c]['remaining_balance'] =  $temp_payments[$c]['current_balance'] - $temp_payments[$c]['payment_principal_paid'];
						$temp_payments[$c]['missed_remaining_balance'] =  $temp_payments[$c]['missed_current_balance'] - $temp_payments[$c]['payment_principal_expected'];
						$temp_payments[$c]['payment_balance_unpaid'] = -1*($temp_payments[$c]['scheduled_balance'] - $temp_payments[$c]['remaining_balance']);
						$temp_payments[$c]['payment_interest_unpaid'] = $temp_payments[$c]['payment_interest_expected'] - $temp_payments[$c]['payment_interest_paid'];

						// Why would we not count this?
						// Don't count short amount if the balance has been paid since, even partially.
//						if (is_int($this->payments[0]['last_payment_made']) && $due_date < $this->payments[0]['last_payment_made']) {
//							// Don't count short amount.
//							$temp_payments[$c]['payment_short'] = 0.00;
//						} else
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
							if (empty($temp_payments[$c]['payment_id'])) {
								$temp_payments[$c]['payment_id_parent'] = $paid['payment_id_parent'];
								unset($temp_payments[$c]['payment_id']);
							}
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
							$temp_payments[$c]['payment_short'] = ($temp_payments[$c]['payment_interest_expected'] + $temp_payments[$c]['payment_principal_expected']) - $temp_payments[$c]['payment_amount_paid'];

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
					while ($temp_payments[$c+1]['payment_status'] == "missed") {
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
				} elseif ($tpayment['payment_status'] == "partial" || $tpayment['payment_status'] == "paid_late") {
					if ($temp_payments[$i]['extra_payments']) {
						$r = count($temp_payments[$i]['extra_payments']) - 1;
						$last_receive = $temp_payments[$i]['extra_payments'][$r]['payment_date_received'];
					} else
						$last_receive = $temp_payments[$i]['payment_date_received'];
					// Make last receive a midnight time to get an accurate days late.
					$last_receive = strtotime("00:00:00", $last_receive);
					$due_date = $temp_payments[$i]['payment_date_expected'];
					if ($tpayment['payment_status'] == "partial")
						$temp_payments[$i]['payment_days_late'] = abs(format_date_range($last_receive, $due_date, '#days#'));
					else
						$temp_payments[$i]['payment_days_late'] = abs(format_date_range($due_date, $last_receive, '#days#'));
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
			
			// Get pastdue
			$past_due = $this->get_pastdue($temp_payments, $this->paid);
			$temp_payments[0]['past_due'] = $pines->com_sales->round($past_due['interest'] + $past_due['principal']);
			$temp_payments[0]['unpaid_balance'] = $pines->com_sales->round($past_due['short_principal']);
			$temp_payments[0]['unpaid_interest'] = $pines->com_sales->round($past_due['short_interest']);
			$temp_payments[0]['sum_payment_short'] = $pines->com_sales->round($past_due['short_interest'] + $past_due['short_principal']);
			
			$sub_int = $temp_payments[0]['subtract_unpaid_interest_paid'];
			$inflated_expected_interest = $temp_payments[0]['inflated_expected_interest'];
			$sum_int = $sum_int - ($sub_int - $inflated_expected_interest);
			$this->new_total_payment_sum = $this->principal + $sum_int;
			$this->total_interest_sum = $sum_int;
			$this->est_interest_savings = $this->total_interest_sum_original - $this->total_interest_sum;
			//$this->past_due = $temp_payments[0]['past_due'];
			$past_due = $this->get_pastdue($temp_payments, $this->paid);
			$this->past_due = $past_due['interest'] + $past_due['principal'];
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
		$delete_info = array(
			'delete_date' => strtotime('now'),
			'delete_name' => $delete_all_payments_name,
			'delete_reason' => $delete_all_payments_reason,
			'delete_user' => $_SESSION['user']->username,
			'delete_guid' => $_SESSION['user_id'],
			'delete_remaining_balance' => $this->payments[0]['remaining_balance'],
		);
		
		// Create Balance info.
		$all_balance_info = array(
			'balance' => $this->balance,
			'unpaid_balance' => $this->unpaid_balance,
			'unpaid_interest' => $this->unpaid_interest,
			'past_due' => $this->past_due,
		);
		
		// Create the record, using a temp array.
		$delete_all = array(
			'pay_by_date' => $this->pay_by_date,
			'all_payments' => $this->payments,
			'all_paid' => $this->paid,
			'all_balance_info' => $all_balance_info,
			'all_edit_payment_history' => $this->history->edit_payments,
			'all_delete' => $delete_info,
		);

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
	 * 
	 * @param string $edit_payment_id The payment id being edited.
	 * @param array $use_paid_array The paid array to search through.
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
		$this->match_info = array(
			'match' => $match,
			'parent' => $parent,
			'num' => $num,
		);
		return $this->match_info;
	}

	/**
	 * Matches a payment ID to a payment in the PAYMENT array.
	 * 
	 * @param boolean $parent True if the paid match is a top level payment.
	 * @param string $edit_payment_id The payment id being edited.
	 * @param array $use_payments_array The payment array to search through.
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
	 * @param array $get_edit_results Contains info about payments that need results in history.
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
	 * @param array $get_delete_results Contains info about payments that need results in history.
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
	 * 
	 * @param int $date_received Date the edit payment was received.
	 * @param int $date_expected_orig Date the edit payment was originally expected.
	 * @param int $date_receive_old Date the edit payment was originally received.
	 * @param int $date_record_old Date the edit payment was previously recorded.
	 * @param int $date_recorded Date the edit payment was recorded.
	 * @param string $payment_id The payment id of the edit payment.
	 * @param float $payment_amount The payment amount of the edit payment.
	 * @param string $error_type The reason or error type of the edit payment.
	 * @param float $payment_interest The interest payment of the edit payment.
	 * @param float $payment_principal The principal payment of the edit payment.
	 * @param float $payment_additional The additional payment of the edit payment.
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
			$insert_pbd = array(
				'date_received' => (int) $date_received,
				'date_recorded' => (int) $date_recorded,
				'date_created' => (int) $date_created,
				'payment_amount' => $pines->com_sales->round($payment_amount),
				'payment_id' => $payment_id,
			);
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
				$insert_pbd = array(
					'date_received' => (int) $date_received,
					'date_recorded' => (int) $date_recorded,
					'date_created' => (int) $date_created,
					'payment_amount' => $pines->com_sales->round($payment_amount),
					'payment_id' => $payment_id,
				);

				// We need to insert this now.

			} else {
				// There's the possibility that the pbd_change payment ID might not exist anymore
				// if it was deleted (when deleting any payment is available)
				// So if it has no match for its payment id, we can just create it (no subtracting necessary).

				$insert_pbd = array(
					'date_received' => (int) $date_received,
					'date_recorded' => (int) $date_recorded,
					'date_created' => (int) $date_created,
					'payment_amount' => $pines->com_sales->round($payment_amount),
					'payment_id' => $payment_id,
				);
			}
		}

		// Now we need to insert insert_pbd because there might be other existing pbds.
		// And the date may have changed.
		$this->insert_pbd($insert_pbd);

		// Now make the payments again using the new PBD array.
		$this->run_make_payments();

		// Save history of deletes.
		$edit_payment = array(
			'edit_date_recorded' => (int) $date_recorded,
			'edit_date_expected' => (int) $date_expected_orig,
			'edit_date_received' => (int) $date_received,
			'edit_date_received_orig' => (int) $date_receive_old,
			'edit_date_recorded_orig' => (int) $date_record_old,
			'edit_payment_id' => $payment_id,
			'edit_user' => $_SESSION['user']->username,
			'edit_user_guid' => $_SESSION['user_id'],
			'edit_reason' => $error_type,
			'edit_payment' => $pines->com_sales->round($edit_payment_amount),
		);
		
		$n = count($this->history->edit_payments) - 1;
		$this->history->edit_payments[$n]['edit_info'] = $edit_payment;
		if ($skip_logging)
			unset($this->history->edit_payments[$n]);

		// Create array to be used for getting edit results of all payments.
		$make_result_info = array(
			'n' => $n,
			'payment_id' => $payment_id,
			'skip_logging' => $skip_logging,
		);
		
		if (!$this->get_edit_results)
			$this->get_edit_results = array();
		$this->get_edit_results[] = $make_result_info;

		// Run Payments array again so that grid will update with correct information.
		$this->get_payments_array();
	}

	/**
	 * The process for deleting a payment is completed here.
	 * 
	 * @param int $date_received The date the delete payment was received.
	 * @param int $date_expected_orig The date the delete payment was originally expected.
	 * @param int $date_recorded The date the delete payment was recorded.
	 * @param string $payment_id The payment id of the deleted payment.
	 * @param float $payment_amount The payment amount of the deleted payment.
	 * @param string $error_type The reason or error type of the deleted payment.
	 * @param float $payment_interest The interest payment of the deleted payment.
	 * @param float $payment_principal The principal payment of the deleted payment.
	 * @param float $payment_additional The additional payment of the deleted payment.
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
				$insert_pbd = array(
					'date_received' => $missing['payment_date_received'],
					'date_recorded' => $missing['payment_date_recorded'],
					'date_created' => $missing['payment_date_recorded'],
					'payment_amount' => $pines->com_sales->round($missing['payment_interest_paid'] + $missing['payment_principal_paid'] + $missing['payment_additional']),
					'payment_id' => $missing['payment_id'],
				);
				$this->insert_pbd($insert_pbd);

				// Remember (this missing payment was created from past due additional amounts.)
				// Get information ready to subtract amount from its parent.
				if (!$subtract_these)
					$subtract_these = array();
				$subtract_info = array(
					'parent_payment_id' => $missing['parent_payment_id'],
					'payment_amount' => $insert_pbd['payment_amount'],
				);
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
	 * 
	 * @param string $edit_payment_id The payment id of the edit payment.
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
//			if ($this->missed_first_payment == true && $first_time != true) {
//				$date_expected = $this->payments[0]['first_payment_missed'];
//				$first_time = true;
//			} elseif (isset($this->payments[0]['next_payment_due']))
//				$date_expected = $this->payments[0]['next_payment_due'];
//			else
//				$date_expected = $this->first_payment_date;
			if (!empty($this->paid))
				$c = $this->paid[0]['num_payments_paid'];
			else
				$c = 0;
			$date_expected = $this->schedule[$c]['scheduled_date_expected'];
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
	 * 
	 * @param array The pay by date payment to be inserted.
	 */
	public function insert_pbd($insert_pbd) {
		$temp_pay_by_date = $this->pay_by_date;
		$re_insert = array();
		$c = 0;
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
					// Problem is I want the insert_pbd to come in last.
					// However if there are multiple PBDs with this date, I need it to be
					// THE LAST ONE.
					$n = $c;
					while ($temp_pay_by_date[$n+1]['date_received'] == $insert_pbd['date_received']) {
						$n++;
					}
					if ($n == $c) {
						// No other payments were made this same day besides this one.
						$re_insert[] = $pbd;
						$re_insert[] = $insert_pbd;
						$inserted = true;
					} else {
						$re_insert[] = $pbd;
					}
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
			$c++;
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
	 * 
	 * @param float $payment_amount The payment amount to be made.
	 * @param int $date_expected The date expected of the payment to be made.
	 * @param int $date_received The date received of the payment to be made.
	 * @param int $date_recorded The date recorded of the payment to be made.
	 * @param string $payment_id The payment id of the payment to be made.
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
		
		// Get today's date.
		$today = strtotime('today');
		
		$past_due = $this->get_pastdue($this->payments, $this->paid, $date_received);
		$this->past_due = $past_due['interest'] + $past_due['principal'];
		
		// if at the time we received it, it may not have been past due... so need to be
		// careful about how we determine it was past due or not.
		if ($date_received < $date_expected) {
			$this->past_due = null;
		}
		
		// Create/Append to paid array.
		
		// change partial_not_due into partial payment when we get a payment with a later receive date.
		if ($this->paid[$num]['payment_status'] == "partial_not_due" && $date_received > $this->paid[$num]['payment_date_expected']) {
			// previous paid is partial not due, but the date we are receiving payment now is late.
			// therefore, this partial_not_due is actually a partial. so let's change it.
			$this->paid[$num]['payment_status'] = "partial";
			// now the payment we are making will get paid through the next if statement!
		}

		// This is for extra payments that have been made on a partial past due payment.
		// Rewrite how past_due/partial payments are handled.
		if ($this->paid[$num]['payment_status'] == "partial") {
			// create an extra payment for this. 
			
			// Because remainder values are updated to include payments with extra payments,
			// it's safe to use these without checking extra payments.
			$unpaid_interest = $this->paid[$num]['payment_interest_unpaid_remainder'];
			if (!isset($this->paid[$num]['payment_interest_unpaid_remainder'])) {
				$unpaid_interest = $this->paid[$num]['payment_interest_expected'] - $this->paid[$num]['payment_interest_paid'];
				$unpaid_principal = $this->paid[$num]['payment_principal_expected'] - $this->paid[$num]['payment_principal_paid'];
			}
			
			// The payment amount might exceed exactly what is needed to fill in this partial payment.
			// or it might not even cover it. 
			if ($this->paid[$num]['extra_payments'])
				$total_paid = $this->paid[$num]['payment_interest_paid_total'] + $this->paid[$num]['payment_principal_paid_total'];
			else
				$total_paid = $this->paid[$num]['payment_interest_paid'] + $this->paid[$num]['payment_principal_paid'];
			if (!isset($this->paid[$num]['payment_total_expected'])) {
				$this->paid[$num]['payment_total_expected'] = $this->paid[$num]['payment_interest_expected'] + $this->paid[$num]['payment_principal_expected'];
			}
			$total_expected = $this->paid[$num]['payment_total_expected'];
			$partial_expected = $total_expected - $total_paid;
			
			if ($partial_expected <= $payment_amount) {
				// the payment amount will cover this partial payment and it might create
				// another payment, either past due or normal.
				$extra_payment_amount = $partial_expected;
				
				if ($payment_amount - $partial_expected >= .01)
					$rollover = true;
				
				$extra = array();
				$extra['payment_type'] = "past_due";
				$extra['payment_date_expected'] = $this->paid[$num]['payment_date_expected'];
				$extra['payment_date_received'] = $date_received;
				$extra['payment_date_recorded'] = $date_recorded;
				$extra['payment_id'] = $payment_id;
				$extra['payment_status'] = 'paid_late';
				$this->paid[$num]['payment_status'] = 'paid_late';
				if ($unpaid_interest > $partial_expected) {
					// we still haven't paid off the unpaid interest.
					$extra['payment_interest_paid'] = $partial_expected;
					$extra['payment_principal_paid'] = 0.00;
				} else {
					// unpaid interest can be paid with partial expected
					$extra['payment_interest_paid'] = $unpaid_interest;
					$extra['payment_principal_paid'] = $extra_payment_amount - $unpaid_interest;
				}
				$extra['payment_additional'] = 0;
				
				// Update remainder values now.
				$this->paid[$num]['payment_interest_unpaid_remainder'] -= $extra['payment_interest_paid'];
				$this->paid[$num]['payment_principal_unpaid_remainder'] -= $extra['payment_principal_paid'];
				
				// Create/Update total paid
				if ($this->paid[$num]['extra_payments']) {
					$this->paid[$num]['payment_interest_paid_total'] += $extra['payment_interest_paid'];
					$this->paid[$num]['payment_principal_paid_total'] += $extra['payment_principal_paid'];
				} else {
					$this->paid[$num]['payment_interest_paid_total'] = $this->paid[$num]['payment_interest_paid'] + $extra['payment_interest_paid'];
					$this->paid[$num]['payment_principal_paid_total'] = $this->paid[$num]['payment_principal_paid'] + $extra['payment_principal_paid'];
				}
				
				// Update rollover amount (if the original payment had no rollover, but the extra
				// payment creates one, we need a rollover amount.
				$this->paid[$num]['payment_paid_rollover'] = $payment_amount - $partial_expected;
				
				
				// Create the rollover payment
				// Figure out if it's a next due payment or a rollover past due
				// if any principal is not paid, then there's still a past due amount.
				if ($this->paid[$num]['payment_principal_unpaid_remainder'] >= .01) {
					// ROLLOVER
					// Set up the rollover
					if ($rollover)
						$this->paid[$num]['payment_paid_rollover'] = $payment_amount - $extra_payment_amount;
					$rollover_payments = array();
					$c = $num;
					$i = 0;
					$new_date_received = $date_received;
					$new_date_recorded = $date_recorded;
					while ($rollover == true && $this->payments[$c+1]['payment_date_expected'] && $this->payments[$c+1]['payment_status'] == "missed" && $this->schedule[$c]['scheduled_date_expected'] < $new_date_received) {
						$new_rollover_payment = array();
						$new_rollover_payment['payment_type'] = 'past_due';
						$new_rollover_payment['payment_date_expected'] = $this->schedule[$c]['scheduled_date_expected'];
						$new_rollover_payment['payment_date_received'] = $new_date_received;
						$new_rollover_payment['payment_date_recorded'] = $new_date_recorded;
						$new_rollover_payment['payment_id_parent'] = $payment_id;
						
						if (!$new_payment_amount) {
							$new_payment_amount = $this->paid[$num]['payment_paid_rollover'];
							$new_interest_unpaid_expected = $this->paid[$num]['payment_interest_unpaid_remainder'];
							$new_principal_unpaid_expected = $this->paid[$num]['payment_principal_unpaid_remainder'];
						} else {
							$new_payment_amount = $rollover_payments[$i-1]['payment_paid_rollover'];
							$new_interest_unpaid_expected = $rollover_payments[$i-1]['payment_interest_unpaid_remainder'];
							$new_principal_unpaid_expected = $rollover_payments[$i-1]['payment_principal_unpaid_remainder'];
						}
					
						$new_rollover_payment['payment_interest_unpaid_expected'] = $new_interest_unpaid_expected;
						$new_rollover_payment['payment_principal_unpaid_expected'] = $new_principal_unpaid_expected;

						$new_rollover_payment['payment_interest_expected'] = $this->schedule[$c]['payment_interest_expected'];
						$new_rollover_payment['payment_principal_expected'] = $this->schedule[$c]['payment_principal_expected'];
						$total_expected = $new_rollover_payment['payment_interest_expected'] + $new_rollover_payment['payment_principal_expected'];
						$new_rollover_payment['payment_total_expected'] = $total_expected;
						
						
						if (($new_payment_amount - $total_expected) >= 0.01) {
							$new_rollover_payment['payment_status'] = 'paid_late';
							// $new_payment_amount exceeds $total_expected, rollover will happen again.
							// But what if that roll over needs to become a next payment due?
							$rollover = true;
							if ($new_interest_unpaid_expected > $new_payment_amount) {
								// not done paying off interest with rollover.
								$new_rollover_payment['payment_interest_paid'] = $total_expected;
								$new_rollover_payment['payment_principal_paid'] = 0.00;
								$new_rollover_payment['payment_interest_unpaid_remainder'] = $new_interest_unpaid_expected - $total_expected;
								$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected;
							} else {
								// the $new_payment_amount DOES cover the interest, but the interest amount may cause a rollover.
								// if it doesn't cause a rollover on the interest, it does on the principal.
								if ($new_interest_unpaid_expected > $total_expected) {
									// interest causes rollover
									$new_rollover_payment['payment_interest_paid'] = $total_expected;
									$new_rollover_payment['payment_principal_paid'] = 0.00;
									$new_rollover_payment['payment_interest_unpaid_remainder'] = $new_interest_unpaid_expected - $total_expected;
									$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected;
								} else {
									// interest covered, principal causes rollover.
									$new_rollover_payment['payment_interest_paid'] = $new_interest_unpaid_expected;
									$new_rollover_payment['payment_principal_paid'] = $total_expected - $new_interest_unpaid_expected;
									$new_rollover_payment['payment_interest_unpaid_remainder'] = 0.00;
									$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected - $new_rollover_payment['payment_principal_paid'];
								}
							}
							// The statements above all have this in common because a rollover is created.
							$new_rollover_payment['payment_paid_rollover'] = $new_payment_amount - $total_expected;
						} else {
							$rollover = false;
							// Could be false because it didn't finish paying off unpaid, OR because it DID.
							// this is the end of the rollover.
							if ($new_payment_amount < $new_interest_unpaid_expected) {
								// interest not done. unpaid not finished.
								$new_rollover_payment['payment_interest_paid'] = $new_payment_amount;
								$new_rollover_payment['payment_principal_paid'] = 0.00;
								$new_rollover_payment['payment_interest_unpaid_remainder'] = $new_interest_unpaid_expected - $new_payment_amount;
								$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected;
								$new_rollover_payment['payment_status'] = 'partial';
							} elseif ($new_payment_amount > $new_interest_unpaid_expected) {
								// possibly unpaid could be paid off.
								$new_rollover_payment['payment_interest_paid'] = $new_interest_unpaid_expected;
								$new_rollover_payment['payment_principal_paid'] = $new_payment_amount - $new_interest_unpaid_expected;
								$new_rollover_payment['payment_interest_unpaid_remainder'] = 0.00;
								$new_rollover_payment['payment_principal_unpaid_remainder'] = $pines->com_sales->round($new_principal_unpaid_expected - $new_rollover_payment['payment_principal_paid']);
								if ($new_rollover_payment['payment_principal_unpaid_remainder'] >= 0.01) {
									$new_rollover_payment['payment_status'] = 'partial';
								} else 
									$new_rollover_payment['payment_status'] = 'paid_late';
							}
						}
						
						$rollover_payments[] = $new_rollover_payment;
						$c++;
						$i++;
					}
				
					// This is for adjusting the LAST rollover amount.
					// 
					// If the rollover was always going to generate a next payment due,
					// It would happen in the ELSE of this if statement.
					// 
					// This happens if someone pays MORE than the past due amount
					// We don't want to apply it as an additional amount. 
					// We want the rollover amount to make the next due payment
					// IF more than the next due payment exists, THEN it would become
					// an additional amount on THAT next due payment.
					$cr = count($rollover_payments) - 1;
					if ($rollover_payments[$cr]['payment_paid_rollover'] >= .01) {
						unset($rollover_payments[$cr]['payment_paid_rollover']);
						// We want to make this towards the next due payment.
						$payment_amount = $rollover_payments[$cr]['payment_paid_rollover'];
						// find the next due payment.
						$next_due_payment = array();
						
						if ($this->schedule[$c]['scheduled_date_expected'] > $new_date_received) {
							// Earlier, rollovers don't happen if the receive date ended up being before the payment was due.
							// Here we check to see if the receive date was indeed before the payment due, and then we create a
							// next payment due type payment for it.
							foreach ($this->schedule as $payment) {
								if ($payment['scheduled_date_expected'] == $this->schedule[$c]['scheduled_date_expected']) {
									if ($payment_amount < $payment['payment_interest_expected']) {
										// does not cover full interest payment.
										$next_due_payment['payment_interest_paid'] = $payment_amount;
										$next_due_payment['payment_principal_paid'] = 0.00;
										$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
										$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
										$partial = true;
									} else {
										// interest is covered at least with possibly more.
										$next_due_payment['payment_interest_paid'] = $pines->com_sales->round((float) $payment['payment_interest_expected']);
										if (($payment_amount - $next_due_payment['payment_interest_paid']) < $payment['payment_principal_expected']) {
											// does not fully cover principal.
											$next_due_payment['payment_principal_paid'] = $payment_amount - $next_due_payment['payment_interest_paid'];
											if ($next_due_payment['payment_principal_paid'] < $payment['payment_principal_expected']) {
												$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
												$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
												$partial = true;
											}
										} else {
											// principal is covered with possibly more.
											$next_due_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
											$next_due_payment['payment_additional'] =  $pines->com_sales->round($payment_amount - ($next_due_payment['payment_interest_paid'] + $next_due_payment['payment_principal_paid']));
										}
									}
									break;
								}
							}
						} else {
							foreach ($this->payments as $payment) {
								if ($payment['scheduled_date_expected'] == $this->payments[0]['next_payment_due']) {
									if ($payment_amount < $payment['payment_interest_expected']) {
										// does not cover full interest payment.
										$next_due_payment['payment_interest_paid'] = $payment_amount;
										$next_due_payment['payment_principal_paid'] = 0.00;
										$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
										$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
										$partial = true;
									} else {
										// interest is covered at least with possibly more.
										$next_due_payment['payment_interest_paid'] = $pines->com_sales->round((float) $payment['payment_interest_expected']);
										if (($payment_amount - $next_due_payment['payment_interest_paid']) < $payment['payment_principal_expected']) {
											// does not fully cover principal.
											$next_due_payment['payment_principal_paid'] = $payment_amount - $next_due_payment['payment_interest_paid'];
											if ($next_due_payment['payment_principal_paid'] < $payment['payment_principal_expected']) {
												$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
												$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
												$partial = true;
											}
										} else {
											// principal is covered with possibly more.
											$next_due_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
											$next_due_payment['payment_additional'] =  $pines->com_sales->round($payment_amount - ($next_due_payment['payment_interest_paid'] + $next_due_payment['payment_principal_paid']));
										}
									}
									break;
								}
							}
						}
						if (!isset($next_due_payment['payment_additional']))
							$next_due_payment['payment_additional'] = 0.00;
						$next_due_payment['payment_date_expected'] = $payment['scheduled_date_expected'];
						$next_due_payment['payment_date_received'] = $date_received;
						$next_due_payment['payment_date_recorded'] = $date_recorded;
						$next_due_payment['payment_id_parent'] = $payment_id;
						if (!isset($next_due_payment['payment_status'])) {
							if (isset($partial) && $partial == true)
								$next_due_payment['payment_status'] = 'partial_not_due';
							else
								$next_due_payment['payment_status'] = 'paid';
						}

						unset($rollover_payments[$cr]['payment_paid_rollover']);
					}
				} else {
					// NEXT DUE PAYMENT
					// This happens if someone pays MORE than the past due amount
					// We don't want to apply it as an additional amount. 
					// We want the rollover amount to make the next due payment
					// IF more than the next due payment exists, THEN it would become
					// an additional amount on THAT next due payment.
					if ($this->paid[$num]['payment_paid_rollover'] >= .01) {
						// We want to make this towards the next due payment.
						$payment_amount = $this->paid[$num]['payment_paid_rollover'];
						// find the next due payment.
						$next_due_payment = array();
						if ($this->schedule[$num]['scheduled_date_expected'] > $date_received) {
							foreach ($this->schedule as $payment) {
								if ($payment['scheduled_date_expected'] == $this->schedule[$num]['scheduled_date_expected']) {
									if ($payment_amount < $payment['payment_interest_expected']) {
										// does not cover full interest payment.
										$next_due_payment['payment_interest_paid'] = $payment_amount;
										$next_due_payment['payment_principal_paid'] = 0.00;
										$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
										$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
										$partial = true;
									} else {
										// interest is covered at least with possibly more.
										$next_due_payment['payment_interest_paid'] = $pines->com_sales->round((float) $payment['payment_interest_expected']);
										if (($payment_amount - $next_due_payment['payment_interest_paid']) < $payment['payment_principal_expected']) {
											// does not fully cover principal.
											$next_due_payment['payment_principal_paid'] = $payment_amount - $next_due_payment['payment_interest_paid'];
											if ($next_due_payment['payment_principal_paid'] < $payment['payment_principal_expected']) {
												$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
												$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
												$partial = true;
											}
										} else {
											// principal is covered with possibly more.
											$next_due_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
											$next_due_payment['payment_additional'] =  $pines->com_sales->round($payment_amount - ($next_due_payment['payment_interest_paid'] + $next_due_payment['payment_principal_paid']));
										}
									}
									break;
								}
							}
						} else {
							foreach ($this->payments as $payment) {
								if ($payment['scheduled_date_expected'] == $this->payments[0]['next_payment_due']) {
									if ($payment_amount < $payment['payment_interest_expected']) {
										// does not cover full interest payment.
										$next_due_payment['payment_interest_paid'] = $payment_amount;
										$next_due_payment['payment_principal_paid'] = 0.00;
										$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
										$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
										$partial = true;
									} else {
										// interest is covered at least with possibly more.
										$next_due_payment['payment_interest_paid'] = $pines->com_sales->round((float) $payment['payment_interest_expected']);
										if (($payment_amount - $next_due_payment['payment_interest_paid']) < $payment['payment_principal_expected']) {
											// does not fully cover principal.
											$next_due_payment['payment_principal_paid'] = $payment_amount - $next_due_payment['payment_interest_paid'];
											if ($next_due_payment['payment_principal_paid'] < $payment['payment_principal_expected']) {
												$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
												$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
												$partial = true;
											}
										} else {
											// principal is covered with possibly more.
											$next_due_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
											$next_due_payment['payment_additional'] =  $pines->com_sales->round($payment_amount - ($next_due_payment['payment_interest_paid'] + $next_due_payment['payment_principal_paid']));
										}
									}
									break;
								}
							}
						}
						if (!isset($next_due_payment['payment_additional']))
							$next_due_payment['payment_additional'] = 0.00;
						$next_due_payment['payment_date_expected'] = $payment['scheduled_date_expected'];
						$next_due_payment['payment_date_received'] = $date_received;
						$next_due_payment['payment_date_recorded'] = $date_recorded;
						$next_due_payment['payment_id_parent'] = $payment_id;
						if (!isset($next_due_payment['payment_status'])) {
							if (isset($partial) && $partial == true) {
								if ($next_due_payment['payment_date_expected'] < $today) {
									$next_due_payment['payment_status'] = 'partial';
								} else {
									$next_due_payment['payment_status'] = 'partial_not_due';
								}
							} else
								$next_due_payment['payment_status'] = 'paid';
						}

						unset($this->paid[$num]['payment_paid_rollover']);
					}
				}
			} else {
				// If the extra payment is still partial for this payment.
				$extra = array();
				$extra['payment_type'] = "past_due";
				$extra['payment_date_expected'] = $this->paid[$num]['payment_date_expected'];
				$extra['payment_date_received'] = $date_received;
				$extra['payment_date_recorded'] = $date_recorded;
				$extra['payment_id'] = $payment_id;
				$extra['payment_status'] = 'partial';
				if ($unpaid_interest > $payment_amount) {
					// we still haven't paid off the unpaid interest.
					$extra['payment_interest_paid'] = $payment_amount;
					$extra['payment_principal_paid'] = 0.00;
				} else {
					// unpaid interest can be paid with partial expected
					$extra['payment_interest_paid'] = $unpaid_interest;
					$extra['payment_principal_paid'] = $payment_amount - $unpaid_interest;
				}
				$extra['payment_additional'] = 0;
				
				// Update remainder values now.
				$this->paid[$num]['payment_interest_unpaid_remainder'] -= $extra['payment_interest_paid'];
				$this->paid[$num]['payment_principal_unpaid_remainder'] -= $extra['payment_principal_paid'];
				
				// Create/Update total paid
				if ($this->paid[$num]['extra_payments']) {
					$this->paid[$num]['payment_interest_paid_total'] += $extra['payment_interest_paid'];
					$this->paid[$num]['payment_principal_paid_total'] += $extra['payment_principal_paid'];
				} else {
					$this->paid[$num]['payment_interest_paid_total'] = $this->paid[$num]['payment_interest_paid'] + $extra['payment_interest_paid'];
					$this->paid[$num]['payment_principal_paid_total'] = $this->paid[$num]['payment_principal_paid'] + $extra['payment_principal_paid'];
				}
			}
			// This is where you assign the extra payment!
			if (!empty($this->paid[$num]['extra_payments']))
				$this->paid[$num]['extra_payments'][] = $extra;
			else {
				$this->paid[$num]['extra_payments'] = array();
				$this->paid[$num]['extra_payments'][] = $extra;
			}
				
		}
		// only for when a partial not due payment has been MADE and an extra payment
		// is needed. This does not generate the partial payment, rather the extra payment.
		elseif ($this->paid[$num]['payment_status'] == "partial_not_due") {
			// The last payment was not due, but it was partial
			// Needs to become an extra payment, while possible other extra payments exist
			
			// Needs to decide whether it's still partial not due, or partial/paid_late.
			
//			// The last payment was not due, but it was partial
//			// Needs to attach to previous paid array matching this date expected
//			foreach ($this->paid as &$paid) {
//				$temp_extra = array();
//				if ($this->payments[0]['next_payment_due'] == $paid['payment_date_expected']) {
//					// get possible extra arrays that already exist
//					// because we need to get how much we've paid off on this payment
//					// to calculate how much is needed to finish the payment.
//					if (!empty($paid['extra_payments'])) {
//						// only if this exists, which it might not.
//						foreach ($paid['extra_payments'] as $old_extra) {
//							$interest_paid += $old_extra['payment_interest_paid'];
//							$balance_paid += $old_extra['payment_principal_paid'];
//						}
//					}
//
//					// The partial payment must also be added to paid amount.
//					$interest_paid += $paid['payment_interest_paid'];
//					$balance_paid += $paid['payment_principal_paid'];
//
//					$unpaid_interest = (($paid['payment_interest_expected'] - $interest_paid) > 0) ? $paid['payment_interest_expected'] - $interest_paid : 0;
//					$unpaid_balance = (($paid['payment_principal_expected'] - $balance_paid) > 0) ? $paid['payment_principal_expected'] - $balance_paid : 0;
//
//					$extra = array();
//					$extra['payment_type'] = "payment";
//					$extra['payment_date_expected'] = $paid['payment_date_expected'];
//					$extra['payment_date_received'] = $date_received;
//					$extra['payment_date_recorded'] = $date_recorded;
//					$extra['payment_id'] = $payment_id;
//					if ($unpaid_interest > 0 && $payment_amount <= $unpaid_interest) {
//						// only potentially covers interest expected.
//						$extra['payment_status'] = "partial_not_due";
//						$extra['payment_interest_paid'] = $payment_amount;
//						$extra['payment_principal_paid'] = 0.00;
//					} else {
//						$extra['payment_interest_paid'] = $unpaid_interest;
//						if (($pines->com_sales->round($payment_amount - $unpaid_interest)) >= $pines->com_sales->round($unpaid_balance)) {
//							// at least entire balance has to be paid for this to work here.
//							$extra['payment_principal_paid'] = $unpaid_balance;
//							$extra['payment_additional'] = $pines->com_sales->round($payment_amount - ($extra['payment_interest_paid'] + $extra['payment_principal_paid']));
//							if ($extra['payment_date_received'] > $extra['payment_date_expected']) {
//								$additional = $extra['payment_additional'];
//								if ($additional >= .01) {
//									$additional = $extra['payment_additional'];
//									$extra['payment_additional'] = 0;
//									$make_additional_payment = true;
//								}
//							}
//							$extra['payment_status'] = 'paid';
//							$paid['payment_status'] = 'paid';
//						//$this->payments[0]['next_payment_due'] = $scheduled_payment_dates[$c+1]['due_date'];
//						} else {
//							// balance not fully covered
//							$extra['payment_principal_paid'] = $payment_amount - $unpaid_interest;
//							$extra['payment_additional'] = 0;
//							$extra['payment_status'] = 'partial_not_due';
//							$paid['payment_status'] = 'partial_not_due';
//						}
//
//					}
//				}
//				if (!empty($extra)) {
//					$paid['extra_payments'][] = $extra;
//					$paid_array_adjusted = true;
//				}
//				// if final extra payment went over, into the next payment due.
//				if ($make_additional_payment) {
//
//					$new_date_expected = strtotime('+1 month', $date_expected);
//
//					$new_payment = array();
//					$new_payment['payment_status'] = "payment";
//					$new_payment['payment_type'] = "payment";
//					$new_payment['payment_date_expected'] = $new_date_expected;
//					$new_payment['payment_date_received'] = $date_received;
//					$new_payment['payment_date_recorded'] = strtotime("+1 second", $date_recorded);
//					$new_payment['payment_id'] = uniqid();
//					$new_payment['parent_payment_id'] = $payment_id;
//
//					foreach ($this->payments as $payment) {
//						if ($payment['scheduled_date_expected'] == $new_date_expected) {
//							if ($additional < $payment['payment_interest_expected']) {
//								// interest not covered.
//								$new_payment['payment_interest_paid'] = $additional;
//								$new_payment['payment_principal_paid'] = 0.00;
//								$partial = true;
//							} else {
//								$new_payment['payment_interest_paid'] = $payment['payment_interest_expected'];
//								if (($additional - $payment['payment_interest_expected']) < $payment['payment_principal_expected']){
//									// principal not covered
//									$new_payment['payment_principal_paid'] = $additional - $new_payment['payment_interest_paid'];
//									$partial = true;
//								} else {
//									// principal covered, possibly additional amount.
//									$new_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
//									$new_payment['additional_payment'] = $additional - ($new_payment['payment_interest_paid'] + $new_payment['payment_principal_paid']);
//								}
//							}
//
//							$new_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
//							$new_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
//
//							if ($partial)
//								$new_payment['payment_status'] = 'partial_not_due';
//							else
//								$new_payment['payment_status'] = 'paid';
//						}
//					}
//				}
//			}
//			unset($paid);
		} else {
			// Past due payments and normal payments are now really similar.
			// Past due may contain ROLLOVER amounts which will be dealt with in the get payments array?
			$rollover = null;
			$rollover_payments = array();
			foreach ($this->payments as $payment) {
				if ($payment['scheduled_date_expected'] == $date_expected || $payment['payment_date_expected'] == $date_expected) {
					if ($this->past_due >= .01 && $date_received > $payment['payment_date_expected']) {
						// This is where we deal with past_due payments.
						// We now will set an amount of unpaid expected.
						$temp_payment_paid['payment_type'] = "past_due";
						$temp_payment_paid['payment_interest_unpaid_expected'] = $past_due['interest'];
						$temp_payment_paid['payment_principal_unpaid_expected'] = $past_due['principal'];
						
						$total_expected = $pines->com_sales->round($payment['payment_interest_expected'] + $payment['payment_principal_expected']);
						// So with past due amounts, we want to pay the interest first.
						// But we want to look at the overview and see the frequency payment amount for each
						// date or time that they make payments. In other words, it should CAP off at the amount.
						// We are going to construct something so that ADDITIONAL amounts just call the make payment function
						// on the left over amount for past dues.. instead of changing the PBD array.
						
						// Let's get the amount that would be expected this month.
						$temp_payment_paid['payment_total_expected'] = $total_expected;
					
						if ($payment_amount < $temp_payment_paid['payment_interest_unpaid_expected']) {
							$temp_payment_paid['payment_interest_unpaid_remainder'] = $temp_payment_paid['payment_interest_unpaid_expected'] - $payment_amount;
							$temp_payment_paid['payment_principal_unpaid_remainder'] = $temp_payment_paid['payment_principal_unpaid_expected'];
							// past due amounts remain.
							
							if (($pines->com_sales->round($temp_payment_paid['payment_interest_unpaid_expected'] + $temp_payment_paid['payment_principal_unpaid_expected'])) == $pines->com_sales->round($temp_past_due_paid['payment_total_expected'])) {
								// They missed one payment.
								// Now adjust payment down to frequency payment.
								$temp_payment_paid['payment_interest_paid'] = $payment_amount;
								$temp_payment_paid['payment_principal_paid'] = 0.00;
								$temp_payment_paid['payment_status'] = "partial";
							} else {
								// They missed multiple payments.
								// Now adjust payment down to frequency payment.
								if ($payment_amount <= $temp_payment_paid['payment_total_expected']) {
									$temp_payment_paid['payment_interest_paid'] = $payment_amount;
									$temp_payment_paid['payment_status'] = "partial";
								} else {
									$temp_payment_paid['payment_interest_paid'] = $temp_payment_paid['payment_total_expected'];
									$temp_payment_paid['payment_paid_rollover'] = $payment_amount - $temp_payment_paid['payment_interest_paid'];
									$rollover = true;
									$temp_payment_paid['payment_status'] = "paid_late";
								}
								// since in this if section, unpaid interest was not paid, no amount can come off the principal unpaid.
								$temp_payment_paid['payment_principal_paid'] = 0.00;
							}
							$temp_payment_paid['payment_interest_expected'] = $payment['payment_interest_expected'];
							$temp_payment_paid['payment_principal_expected'] = $payment['payment_principal_expected'];
						} else {
							$temp_payment_paid['payment_status'] = "paid_late";
							// unpaid interest amount was covered with possibly more.
							if ($temp_payment_paid['payment_interest_unpaid_expected'] < $temp_payment_paid['payment_total_expected']) {
								// the unpaid interest is LESS than the normal frequency payment.
								// this could happen from just missing one payment.
								if (($pines->com_sales->round($temp_payment_paid['payment_interest_unpaid_expected'] + $temp_payment_paid['payment_principal_unpaid_expected'])) == $pines->com_sales->round($temp_past_due_paid['payment_total_expected'])) {
									// They missed one payment.
									// Now adjust payment down to frequency payment.
									$temp_payment_paid['payment_interest_paid'] = $temp_payment_paid['payment_interest_unpaid_expected'];
									$temp_payment_paid['payment_principal_paid'] = $payment_amount - $temp_payment_paid['payment_interest_unpaid_expected'];
									$temp_payment_paid['payment_interest_expected'] = $payment['payment_interest_expected'];
									$temp_payment_paid['payment_principal_expected'] = $payment['payment_principal_expected'];
								} else {
									// Missed only a few payments to where the interest unpaid is less than the monthly amount.
									// So interest was covered, and part of principal - (possibly all of the principal?)
									$temp_payment_paid['payment_interest_paid'] = $temp_payment_paid['payment_interest_unpaid_expected'];
									$temp_payment_paid['payment_principal_paid'] = $total_expected - $temp_payment_paid['payment_interest_unpaid_expected'];
									$temp_payment_paid['payment_interest_expected'] = $payment['payment_interest_expected'];
									$temp_payment_paid['payment_principal_expected'] = $payment['payment_principal_expected'];
									$temp_payment_paid['payment_paid_rollover'] = $payment_amount - ($temp_payment_paid['payment_interest_paid'] + $temp_payment_paid['payment_principal_paid']);
									if ($temp_payment_paid['payment_paid_rollover'] < .01)
										unset($temp_payment_paid['payment_paid_rollover']);
									else
										$rollover = true;
									// impossible to pay the interest and all the principal in this payment if in this else.
									$temp_payment_paid['payment_interest_unpaid_remainder'] = $temp_payment_paid['payment_interest_unpaid_expected'] - $temp_payment_paid['payment_interest_paid'];
									$temp_payment_paid['payment_principal_unpaid_remainder'] = $temp_payment_paid['payment_principal_unpaid_expected'] - $temp_payment_paid['payment_principal_paid'];
								}
							} else {
								// past due amounts remain.
								// Now adjust payment down to frequency payment.
								$temp_payment_paid['payment_interest_paid'] = $temp_payment_paid['payment_total_expected'];
								$temp_payment_paid['payment_principal_paid'] = 0.00;
								
								// unpaid interest exceeds the frequency payment. (ie multiple missed payments).
								// Now, there technically isn't any remainder for interest at least, but the thing is...
								// we want to max out this payment at the frequency amount and just make more payments from the leftover amount.
								// The remainder amounts are kind of "wrong" because we only took off the frequency amount.
								// We keep track of how much to pay still with future payments with ROLLOVER.
								$temp_payment_paid['payment_interest_unpaid_remainder'] = $temp_payment_paid['payment_interest_unpaid_expected'] - $temp_payment_paid['payment_total_expected'];
								$temp_payment_paid['payment_paid_rollover'] = $payment_amount - $temp_payment_paid['payment_interest_paid'];
								$temp_payment_paid['payment_principal_unpaid_remainder'] = $temp_payment_paid['payment_principal_unpaid_expected'];
								$rollover = true;
								
								$temp_payment_paid['payment_interest_expected'] = $payment['payment_interest_expected'];
								$temp_payment_paid['payment_principal_expected'] = $payment['payment_principal_expected'];
							}
						}
					} elseif ($payment_amount < $payment['payment_interest_expected']) {
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
					continue;
				}
				// As long as there is a rollover amount to create another payment and it is LESS than the next payment due... it will do this.
				if ($rollover && $payment['payment_date_expected'] && $payment['payment_status'] == "missed") {
					if (!$new_payment_amount) {
						$new_payment_amount = $temp_payment_paid['payment_paid_rollover'];
						$new_interest_unpaid_expected = $temp_payment_paid['payment_interest_unpaid_remainder'];
						$new_principal_unpaid_expected = $temp_payment_paid['payment_principal_unpaid_remainder'];
					} else {
						$new_payment_amount = $new_rollover_payment['payment_paid_rollover'];
						$new_interest_unpaid_expected = $new_rollover_payment['payment_interest_unpaid_remainder'];
						$new_principal_unpaid_expected = $new_rollover_payment['payment_principal_unpaid_remainder'];
					}
					$new_date_expected = $payment['payment_date_expected'];
					$new_date_received = $date_received;
					$new_date_recorded = $date_recorded;

					// Find out what is expected this period, and determine if rollover amount covers it.
					$total_expected = $pines->com_sales->round($payment['payment_interest_expected'] + $payment['payment_principal_expected']);

					$new_rollover_payment['payment_type'] = 'past_due';
					$new_rollover_payment['payment_date_expected'] = $new_date_expected;
					$new_rollover_payment['payment_date_received'] = $new_date_received;
					$new_rollover_payment['payment_date_recorded'] = $new_date_recorded;
					$new_rollover_payment['payment_id_parent'] = $payment_id;
					$new_rollover_payment['payment_status'] = 'paid_late';

					$new_rollover_payment['payment_interest_unpaid_expected'] = $new_interest_unpaid_expected;
					$new_rollover_payment['payment_principal_unpaid_expected'] = $new_principal_unpaid_expected;

					$new_rollover_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
					$new_rollover_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
					$new_rollover_payment['payment_total_expected'] = $total_expected;

					if (($new_payment_amount - $total_expected) >= 0.01) {
						// $new_payment_amount exceeds $total_expected, rollover will happen again.
						// We need to be careful, because this rollover might create a past due payment
						// but it might really need to go into a next payment due.
						$rollover = true;
						// You would think that rollover wouldnt happen because it checks for the payment
						// status to be missed and for it to have a payment date expected...
						// But it could look late before it really is.. so that doesn't stop the rollover
						// from happening..
						// So we make ^^^ rollover false in the one section this could be true.
						if ($new_interest_unpaid_expected > $new_payment_amount) {
							// not done paying off interest with rollover.
							$new_rollover_payment['payment_interest_paid'] = $total_expected;
							$new_rollover_payment['payment_principal_paid'] = 0.00;
							$new_rollover_payment['payment_interest_unpaid_remainder'] = $new_interest_unpaid_expected - $total_expected;
							$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected;
						} else {
							// the $new_payment_amount DOES cover the interest, but the interest amount may cause a rollover.
							// if it doesn't cause a rollover on the interest, it does on the principal.
							if ($new_interest_unpaid_expected > $total_expected) {
								// interest causes rollover
								$new_rollover_payment['payment_interest_paid'] = $total_expected;
								$new_rollover_payment['payment_principal_paid'] = 0.00;
								$new_rollover_payment['payment_interest_unpaid_remainder'] = $new_interest_unpaid_expected - $total_expected;
								$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected;
							} else {
								// interest covered, principal causes rollover.
								$new_rollover_payment['payment_interest_paid'] = $new_interest_unpaid_expected;
								$new_rollover_payment['payment_principal_paid'] = $total_expected - $new_interest_unpaid_expected;
								$new_rollover_payment['payment_interest_unpaid_remainder'] = 0.00;
								$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected - $new_rollover_payment['payment_principal_paid'];
								if ($new_rollover_payment['payment_principal_unpaid_remainder'] < .01) {
									// Make rollover false
									$rollover = false;
								}
									
							}
						}
						// The statements above all have this in common because a rollover is created.
						$new_rollover_payment['payment_paid_rollover'] = $new_payment_amount - $total_expected;
					} else {
						unset($new_rollover_payment['payment_paid_rollover']);
						$rollover = false;
						// Could be false because it didn't finish paying off unpaid, OR because it DID.
						// this is the end of the rollover.
						if ($new_payment_amount < $new_interest_unpaid_expected) {
							// interest not done. unpaid not finished.
							$new_rollover_payment['payment_interest_paid'] = $new_payment_amount;
							$new_rollover_payment['payment_principal_paid'] = 0.00;
							$new_rollover_payment['payment_interest_unpaid_remainder'] = $new_interest_unpaid_expected - $new_payment_amount;
							$new_rollover_payment['payment_principal_unpaid_remainder'] = $new_principal_unpaid_expected;
							$new_rollover_payment['payment_status'] = 'partial';
						} elseif ($new_payment_amount > $new_interest_unpaid_expected) {
							// possibly unpaid could be paid off.
							$new_rollover_payment['payment_interest_paid'] = $new_interest_unpaid_expected;
							$new_rollover_payment['payment_principal_paid'] = $new_payment_amount - $new_interest_unpaid_expected;
							$new_rollover_payment['payment_interest_unpaid_remainder'] = 0.00;
							$new_rollover_payment['payment_principal_unpaid_remainder'] = $pines->com_sales->round($new_principal_unpaid_expected - $new_rollover_payment['payment_principal_paid']);
							if ($new_rollover_payment['payment_principal_unpaid_remainder'] >= 0.01) {
								$new_rollover_payment['payment_status'] = 'partial';
							} else 
								$new_rollover_payment['payment_status'] = 'paid_late';
						}
					}
				$rollover_payments[] = $new_rollover_payment;
				}
			}
			if (!isset($temp_payment_paid['payment_additional']))
				$temp_payment_paid['payment_additional'] = 0.00;
			$temp_payment_paid['payment_date_expected'] = $date_expected;
			$temp_payment_paid['payment_date_received'] = $date_received;
			$temp_payment_paid['payment_date_recorded'] = $date_recorded;
			$temp_payment_paid['payment_id'] = $payment_id;
			if (!isset($temp_payment_paid['payment_status'])) {
				if (isset($partial) && $partial == true)
					$temp_payment_paid['payment_status'] = 'partial_not_due';
				else
					$temp_payment_paid['payment_status'] = 'paid';
			}
		}
		
		// This is for adjusting the LAST rollover amount.
		// This happens if someone pays MORE than the past due amount
		// We don't want to apply it as an additional amount. 
		// We want the rollover amount to make the next due payment
		// IF more than the next due payment exists, THEN it would become
		// an additional amount on THAT next due payment.
		$cr = count($rollover_payments) - 1;
		if ($rollover_payments[$cr]['payment_paid_rollover'] >= .01) {
			// We want to make this towards the next due payment.
			$payment_amount = $rollover_payments[$cr]['payment_paid_rollover'];
			// find the next due payment.
			$next_due_payment = array();
			$paid_num = ($cr + 1) + 1 + $num;
			if ($this->schedule[$paid_num]['scheduled_date_expected'] > $date_received) {
				foreach ($this->schedule as $payment) {
					if ($payment['scheduled_date_expected'] == $this->schedule[$paid_num]['scheduled_date_expected']) {
						if ($payment_amount < $payment['payment_interest_expected']) {
							// does not cover full interest payment.
							$next_due_payment['payment_interest_paid'] = $payment_amount;
							$next_due_payment['payment_principal_paid'] = 0.00;
							$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
							$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
							$partial = true;
						} else {
							// interest is covered at least with possibly more.
							$next_due_payment['payment_interest_paid'] = $pines->com_sales->round((float) $payment['payment_interest_expected']);
							if ($pines->com_sales->round($payment_amount - $next_due_payment['payment_interest_paid']) < $pines->com_sales->round($payment['payment_principal_expected'])) {
								// does not fully cover principal.
								$next_due_payment['payment_principal_paid'] = $payment_amount - $next_due_payment['payment_interest_paid'];
								if ($next_due_payment['payment_principal_paid'] < $payment['payment_principal_expected']) {
									$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
									$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
									$partial = true;
								}
							} else {
								// principal is covered with possibly more.
								$next_due_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
								$next_due_payment['payment_additional'] =  $pines->com_sales->round($payment_amount - ($next_due_payment['payment_interest_paid'] + $next_due_payment['payment_principal_paid']));
							}
						}
						break;
					}
				}
			} else {
				foreach ($this->payments as $payment) {
					if ($payment['scheduled_date_expected'] == $this->payments[0]['next_payment_due']) {
						if ($payment_amount < $payment['payment_interest_expected']) {
							// does not cover full interest payment.
							$next_due_payment['payment_interest_paid'] = $payment_amount;
							$next_due_payment['payment_principal_paid'] = 0.00;
							$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
							$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
							$partial = true;
						} else {
							// interest is covered at least with possibly more.
							$next_due_payment['payment_interest_paid'] = $pines->com_sales->round((float) $payment['payment_interest_expected']);
							if (($payment_amount - $next_due_payment['payment_interest_paid']) < $payment['payment_principal_expected']) {
								// does not fully cover principal.
								$next_due_payment['payment_principal_paid'] = $payment_amount - $next_due_payment['payment_interest_paid'];
								if ($next_due_payment['payment_principal_paid'] < $payment['payment_principal_expected']) {
									$next_due_payment['payment_interest_expected'] = $payment['payment_interest_expected'];
									$next_due_payment['payment_principal_expected'] = $payment['payment_principal_expected'];
									$partial = true;
								}
							} else {
								// principal is covered with possibly more.
								$next_due_payment['payment_principal_paid'] = $payment['payment_principal_expected'];
								$next_due_payment['payment_additional'] =  $pines->com_sales->round($payment_amount - ($next_due_payment['payment_interest_paid'] + $next_due_payment['payment_principal_paid']));
							}
						}
						break;
					}
				}
			}
			if (!isset($next_due_payment['payment_additional']))
				$next_due_payment['payment_additional'] = 0.00;
			$next_due_payment['payment_date_expected'] = $payment['scheduled_date_expected'];
			$next_due_payment['payment_date_received'] = $date_received;
			$next_due_payment['payment_date_recorded'] = $date_recorded;
			$next_due_payment['payment_id_parent'] = $payment_id;
			if (!isset($next_due_payment['payment_status'])) {
				if (isset($partial) && $partial == true)
					$next_due_payment['payment_status'] = 'partial_not_due';
				else
					$next_due_payment['payment_status'] = 'paid';
			}
			
			unset($rollover_payments[$cr]['payment_paid_rollover']);
		}

		// assign above payments to temporary paid array.
		$temp_paid = array();
		if (empty($this->paid))
			$temp_paid[0] = array();

		// A normal Payment.
		if (!empty($temp_payment_paid)) {
			$temp_paid[] = $temp_payment_paid;
		}
		// Rollover Past Due Payments.
		if (!empty($rollover_payments)) {
			foreach ($rollover_payments as $rollover_payment) {
				$temp_paid[] = $rollover_payment;
			}
		}
		// Rollover Next Payment Due.
		if (!empty($next_due_payment)) {
			$temp_paid[] = $next_due_payment;
		}
		
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
				foreach ($temp_paid as $payment_paid) {
					if (empty($payment_paid))
						continue;
					$this->paid[] = $payment_paid;
				}
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
	 * 
	 * @param array $arr The array to be numerically reordered.
	 * @return array The array.
	 */
	public function array_values_recursive($arr) {
		$arr = array_values($arr);
		foreach($arr as $key => $val)
			if(array_values($val) === $val)
				$arr[$key] = array_values_recursive($val);

		return $arr;
	}
	
	/**
	 * Get the current past due amount.
	 * 
	 * @param array $payments_array The payments array to be used to get past due amount.
	 * @param array $paid_array The paid array to be used to get past due amount.
	 * @return array The pastdue amount.
	 */
	public function get_pastdue($payments_array, $paid_array, $date_received = null) {
		global $pines;
		$pastdue = array();
		
		// Find PAID amount
		$interest_paid = ($payments_array[0]['total_interest_paid']) ? $payments_array[0]['total_interest_paid'] : 0;
		$principal_paid = ($payments_array[0]['total_principal_paid']) ? $payments_array[0]['total_principal_paid'] : 0;
		
		// Find EXPECTED amount before receive date.
		if ($date_received) {
			foreach ($payments_array as $payment) {
				if ($payment['payment_date_expected'] != null && $payment['payment_date_expected'] < $date_received) {
					$interest_expected += $payment['payment_interest_expected'];
					$principal_expected +=  + $payment['payment_principal_expected'];
					$interest_not_due = 0;
					$principal_not_due = 0;
				}
//				if ($payment['payment_status'] == "partial_not_due") {
//					$interest_not_due += $payment['payment_interest_expected'];
//					$principal_not_due +=  + $payment['payment_principal_expected'];
//				}
			}
		} else {
			// Find EXPECTED amount (not scheduled).
			foreach ($payments_array as $payment) {
				if ($payment['payment_date_expected']) {
					$interest_expected += $payment['payment_interest_expected'];
					$principal_expected +=  + $payment['payment_principal_expected'];
				}
				if ($payment['payment_status'] == "partial_not_due") {
					$interest_not_due += $payment['payment_interest_expected'];
					$principal_not_due +=  + $payment['payment_principal_expected'];
				}
			}
		}
		
		// Figure out PAST DUE
		$pastdue_interest = $pines->com_sales->round($interest_expected - $interest_paid);
		$pastdue_principal = $pines->com_sales->round($principal_expected - $principal_paid);
		
		// Consider that I need a short amount.
		// This works because it DOES not REMOVE partial not due shortness.
		$short_interest = $pastdue_interest;
		$short_principal = $pastdue_principal;
		
		// But now we need to consider partial not due
		$pastdue_interest -= $interest_not_due;
		$pastdue_principal -= $principal_not_due;
		
		
		
		
		// Set past due amounts.
		if ($pastdue_interest >= .01)
			$pastdue['interest'] = $pastdue_interest;
		else 
			$pastdue['interest'] = 0;
		
		if ($pastdue_principal >= .01)
			$pastdue['principal'] = $pastdue_principal;
		else 
			$pastdue['principal'] = 0;
		
		// Also add in short amounts to this for fun
		if ($short_interest >= .01)
			$pastdue['short_interest'] = $short_interest;
		else 
			$pastdue['short_interest'] = 0;
		
		if ($short_principal >= .01)
			$pastdue['short_principal'] = $short_principal;
		else 
			$pastdue['short_principal'] = 0;
		
//		foreach ($paid_array as $paid_payment) {
//			if ($paid_payment['extra_payents']) {
//				foreach ($paid_payment['extra_payents'] as $extra_paid) {
//					$paid_interest += $paid_payment['payment_interest_paid'];
//					$paid_principal += $paid_payment['payment_interest_paid'] + $paid_payment['payment_additional'];
//				}
//			}
//			$paid_interest += $paid_payment['payment_interest_paid'];
//			$paid_principal += $paid_payment['payment_interest_paid'] + $paid_payment['payment_additional'];
//		}
		
//		if (empty($paid_array)) {
//			$c = 0;
//			$start_date = $payments_array[0]['first_payment_due'];
//		} else {
//			$c = $paid_array[0]['num_payments_paid'];
//			$start_date = $paid_array[$c]['payment_date_expected'];
//		}
//		$end_date = $payments_array[0]['next_payment_due'];
//		
//		// Find current missed payments
//		foreach ($payments_array as $payment) {
//			if ($payment['payment_date_expected'] > $start_date && $payment['payment_date_expected'] < $end_date) {
//				// This will get payments that are missed AFTER the last paid and BEFORE current due date.
//				$pastdue_interest += $payment['payment_interest_expected'];
//				$pastdue_principal +=  + $payment['payment_principal_expected'];
//			}
//		}
//		
//		// Get possible partial payment missing
//		// Be careful here. We need to do this different for past due payments.
//		if ($paid_array[$c]['payment_total_expected']) {
//			if ($paid_array[$c]['payment_interest_unpaid_remainder'] >= .01) {
//				// there is an unpaid interest remainder.
//				$pastdue_interest += $paid_array[$c]['payment_interest_unpaid_remainder'];
//			}
//			if ($paid_array[$c]['payment_principal_unpaid_remainder'] >= .01) {
//				// there is an unpaid interest remainder.
//				$pastdue_principal += $paid_array[$c]['payment_principal_unpaid_remainder'];
//			}
//		} else {
//			if ($paid_array[$c]['payment_interest_paid'] < $paid_array[$c]['payment_interest_expected']) {
//			$pastdue_interest += $paid_array[$c]['payment_interest_expected'] - $paid_array[$c]['payment_interest_paid'];
//		}
//			if ($paid_array[$c]['payment_principal_paid'] < $paid_array[$c]['payment_principal_expected']) {
//				$pastdue_principal += $paid_array[$c]['payment_principal_expected'] - $paid_array[$c]['payment_principal_paid'];
//			}
//		}
//		
//		$pastdue['interest'] = $pastdue_interest;
//		$pastdue['principal'] = $pastdue_principal;
		return $pastdue;
	}
}

/**
 * Loan terms not possible exception.
 *
 * @package Components\loan
 */
class com_loan_loan_terms_not_possible_exception extends Exception {}

?>