<?php
/**
 * com_loan class.
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
 * com_loan main class.
 *
 * @package Components\loan
 */
class com_loan extends component {
	/**
	 * Creates and attaches a module which lists loans.
	 * @return module The module.
	 */
	public function list_loans($show) {
		global $pines;

		$module = new module('com_loan', 'loan/list', 'content');
		$module->show = $show;
		return $module;
	}

	/**
	 * Print a form to select date timespan.
	 *
	 * @param bool $all_time Currently searching all records or a timespan.
	 * @param string $start The current starting date of the timespan.
	 * @param string $end The current ending date of the timespan.
	 * @return module The form's module.
	 */
	public function date_select_form($all_time = false, $start = null, $end = null) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/dateselect', 'content');
		$module->all_time = $all_time;
		$module->start_date = $start;
		$module->end_date = $end;

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Print a form to select a location.
	 *
	 * @param int $location The currently set location to search in.
	 * @param bool $descendants Whether to show descendant locations.
	 * @return module The form's module.
	 */
	public function location_select_form($location = null, $descendants = false) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/locationselect', 'content');
		if (!isset($location)) {
			$module->location = $_SESSION['user']->group->guid;
		} else {
			$module->location = $location;
		}
		$module->descendants = $descendants;

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	
	/**
	 * Print a form to select a status on loans to search by.
	 * @param array current state of the grid
	 * @return module The form's module.
	 */
	public function search_status_form($cur_state) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/searchstatus', 'content');
		$module->cur_state = json_decode($cur_state);
		
		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Print a form to make payments.
	 *
	 * Uses a page override to only print the form.
	 * 
	 * @param string $loan_ids A comma separated list of ids
	 * @return module The form's module.
	 */
	public function make_payments_form($loan_ids) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/makepayments', 'content');
		$module->loan_ids = $loan_ids;

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Print a form to change loan status.
	 *
	 * Uses a page override to only print the form.
	 * 
	 * @param string $loan_ids A comma separated list of ids
	 * @return module The form's module.
	 */
	public function loan_status_form($loan_ids) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/loanstatus', 'content');
		$module->loan_ids = $loan_ids;

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Print a form to view customer history.
	 *
	 * Uses a page override to only print the form.
	 * 
	 * @param string $loan_ids A comma separated list of ids
	 * @return module The form's module.
	 */
	public function cust_history_form($loan_ids) {
		global $pines;
		$pines->page->override = true;

		$module = new module('com_loan', 'form/cust_history', 'content');
		$module->loan_ids = $loan_ids;

		$pines->page->override_doc($module->render());
		return $module;
	}
	
	/**
	 * Print a form to add interaction.
	 *
	 * Uses a page override to only print the form.
	 *
	 * @return module The form's module.
	 */
	public function add_interaction_form() {
		global $pines;
		$pines->page->override = true;
		header('Content-Type: application/json');

		$module = new module('com_loan', 'form/add_interaction', 'content');
		$module->entity = $this;

		$pines->page->modules['head'] = array();
		$content = $module->render();
		// Render any modules placed into the head. (In case they add more.)
		foreach ($pines->page->modules['head'] as $cur_module)
			$cur_module->render();
		// Now get their content.
		$head = '';
		foreach ($pines->page->modules['head'] as $cur_module)
			$head .= $cur_module->render();

		$pines->page->override_doc($head.$content);
		return $module;
	}
	
	/**
	* Get Payment Amounts for a loan to determine Payment Due.
	*
	* @param int $loan_id The loan guid.
	*/
   public function get_payment_amounts($loan_id) {
		$cur_loan = com_loan_loan::factory((int) $loan_id); 
		
		// If any payments have been made.
		$cur_loan->temp_paid = (!empty($cur_loan->paid));
		// If it is paid off.
		$cur_loan->temp_paid_off = ($cur_loan->status == 'paid off');
		// If first payment is missed, set date and past due
		$missed = ($cur_loan->missed_first_payment && !$cur_loan->temp_paid);

		// If missed, set date and past due amount
		if ($missed) {
			$cur_loan->temp_past_due = $cur_loan->payments[0]['sum_payment_short'];
		}

		// If not missed or made, it's not past due yet.
		//if (!$paid && !$missed)
		//	$not_due = true;

		// Set Next Due
		$cur_loan->temp_next_due = $cur_loan->payments[0]['next_payment_due_amount'];
		// Set Past due
		if (!$cur_loan->temp_past_due)
			$cur_loan->temp_past_due = $cur_loan->payments[0]['past_due'];
		
		$cur_loan->temp_paid_off = ($cur_loan->status == 'paid off');
		
		$cur_loan->temp_next_due_date = (isset($cur_loan->payments[0]['next_payment_due'])) ? $cur_loan->payments[0]['next_payment_due'] : $cur_loan->first_payment_date;
		
		// Past Due by some amount of days..
		if ($missed || $cur_loan->temp_past_due > 0) {
			$cur_loan->temp_past_due_date = ($cur_loan->payments[0]['last_payment_made'] != 'None Made') ? $cur_loan->payments[0]['last_payment_made'] : $cur_loan->payments[0]['first_payment_missed'];
		}
		return $cur_loan;
   }
	
	/**
	* Adds an interaction to a customer from a loan id.
	*
	* @param int $loan_id The loan guid.
	* @param int $employee The employee guid.
	* @param string $type The interaction type.
	* @param string $status Whether the status is open or closed.
	* @param string $comments Any comments on the interaction.
	* @return bool If the interactions were saved successfully.
	*/

	function add_interaction($loan_id, $employee, $type, $status, $comments) {
		global $pines;
		
		$loan = com_loan_loan::factory((int) $loan_id);
		$customer = $loan->customer;

		if (!isset($customer->guid)) {
			return false;
		}

		$employee = com_hrm_employee::factory((int) $employee);
		if (!isset($employee)) {
			return false;
		}

		$interaction = com_customer_interaction::factory();
		$interaction->customer = $customer;
		$interaction->employee = $employee;
		if (!empty($_REQUEST['date']) || !empty($_REQUEST['time'])) {
			// Change the timezone to enter the event with the supplied timezone or user's timezone.
			$cur_timezone = date_default_timezone_get();
			if (!empty($_REQUEST['timezone']))
				date_default_timezone_set($_REQUEST['timezone']);
			else
				date_default_timezone_set($_SESSION['user']->get_timezone());
			$interaction->action_date = strtotime($_REQUEST['date'].$_REQUEST['time']);
		} else {
			$interaction->action_date = time();
		}
		$interaction->type = $type;
		$interaction->status = $status;
		$interaction->comments = $comments;

		$existing_appt = $pines->entity_manager->get_entity(
			array('class' => com_customer_interaction),
			array('&',
				'data' => array('status', 'open'),
				'ref' => array('customer', $interaction->customer),
				'gte' => array('action_date', $interaction->action_date),
				'lte' => array('action_date', strtotime('+1 hour', $interaction->action_date))
			)
		);
		if (isset($existing_appt->guid) && $interaction->guid != $existing_appt->guid) {
			date_default_timezone_set($cur_timezone);
			return false;
		}

		if ($pines->config->com_customer->com_calendar) {
			// Create the interaction calendar event.
			$event = com_calendar_event::factory();
			$event->employee = $employee;
			$location = $employee->group;
			$event->appointment = true;
			$event->label = $interaction->type;
			foreach ($pines->config->com_customer->interaction_types as $cur_type) {
				if (strpos($cur_type, $interaction->type))
					$symbol = explode(':', $cur_type);
			}
			$event->title = $symbol[0] .' '. $customer->name;
			$event->private = true;
			$event->all_day = false;
			$event->start = $interaction->action_date;
			$event->end = strtotime('+1 hour', $interaction->action_date);
			switch ($interaction->status) {
				case 'open':
				default:
					$event->color = 'greenyellow';
					break;
				case 'canceled':
					$event->color = 'gainsboro';
					break;
				case 'closed':
					$event->color = 'blue';
					break;
			}
			$event->information = '('.$interaction->employee->name.') '.$interaction->comments;
			$event->ac->other = 2;
			if (!$event->save()) {
				date_default_timezone_set($cur_timezone);
				return false;
			}

			$interaction->event = $event;
		}

		$interaction->ac->other = 2;

		if ($interaction->save()) {
			if ($pines->config->com_customer->com_calendar) {
				$event->appointment = $interaction;
				$event->group = $location;
				$event->save();
			}
			$success = true;
		} else {
			$success = false;
		}
		date_default_timezone_set($cur_timezone);

		return $success;
	}
}

?>