<?php
/**
 * Save a paystub so it can be viewed later.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/editpayroll') )
	punt_user(null, pines_url('com_reports', 'reportpayroll'));

$start = strtotime($_REQUEST['start']);
$end = strtotime($_REQUEST['end']);

$pay_stub = $pines->entity_manager->get_entity(array('class' => com_reports_paystub),
		array('&',
			'tag' => array('com_reports', 'paystub'),
			'gte' => array('end', (int) $start)
		)
	);
if (isset($pay_stub->guid)) {
	pines_error('There is already a Paystub for this pay period.');
	return;
}

$pay_stub = com_reports_paystub::factory();
$pay_stub->start = strtotime($_REQUEST['start']);
$pay_stub->end = strtotime($_REQUEST['end']);
// Save a copy of everyone's payment information.
$pay_stub->total = 0;
$pay_stub->payroll = (array) json_decode($_REQUEST['totals']);
foreach ($pay_stub->payroll as &$cur_payment) {
	$employee = com_hrm_employee::factory((int) $cur_payment->key);
	$cur_payment = array(
		'employee' => $employee,
		'location' => $employee->group,
		'pay_type' => $cur_payment->values[2],
		'qty_sold' => (int) $cur_payment->values[3],
		'qty_returned' => (int) $cur_payment->values[4],
		'total_sold' => floatval(str_replace(array('$', ',', ' '), '', $cur_payment->values[5])),
		'total_returned' => floatval(str_replace(array('$', ',', ' '), '', $cur_payment->values[6])),
		'scheduled' => floatval(str_replace(array('$', ',', ' '), '', $cur_payment->values[7])),
		'worked' => floatval(preg_replace('/^[^0-9\.]/', '', $cur_payment->values[8])),
		'variance' => floatval(preg_replace('/^[^0-9\.]/', '', $cur_payment->values[9])),
		'commission' => floatval(str_replace(array('$', ',', ' '), '', $cur_payment->values[10])),
		'penalties' => floatval(str_replace(array('$', ',', ' '), '', $cur_payment->values[11])),
		'bonuses' => floatval(str_replace(array('$', ',', ' '), '', $cur_payment->values[12])),
		'total_pay' => floatval(str_replace(array('$', ',', ' '), '', $cur_payment->values[13]))
	);
	$pay_stub->total += $cur_payment['total_pay'];
	if (!isset($cur_payment['employee']->guid))
		$cur_payment['employee'] = null;
}
unset($cur_payment);

// Finalize the paystub so that it will not change anymore.
$pay_stub->final = true;

if ($pay_stub->save()) {
	pines_notice('Finalized Paystub ['.$pay_stub->guid.']');
} else {
	$ranking->print_form();
	pines_error('Error saving Paystub. Do you have permission?');
	return;
}

pines_redirect(pines_url('com_reports', 'listpaystubs'));

?>