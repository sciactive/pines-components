<?php
/**
 * com_loan's configuration defaults.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrel <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'loan_search_limit',
		'cname' => 'Loan Search Limit',
		'description' => 'Limit the number of loans that can be searched at a time.',
		'value' => 20,
		'peruser' => true,
	),
	array(
		'name' => 'cust_hist_limit',
		'cname' => 'Customer History Limit',
		'description' => 'The limit for how many customer histories can be viewed at a time on loans.',
		'value' => 5,
		'peruser' => true,
	),
	array(
		'name' => 'add_interaction_limit',
		'cname' => 'Interaction Limit',
		'description' => 'The limit for how many customer interactions can be made at a time on loans.',
		'value' => 20,
		'peruser' => true,
	),
	array(
		'name' => 'payments_limit',
		'cname' => 'Payments Limit',
		'description' => 'The limit for how many customer payments can be made at a time on loans.',
		'value' => 20,
		'peruser' => true,
	),
	array(
		'name' => 'status_limit',
		'cname' => 'Status Limit',
		'description' => 'The limit for how many customers the loan status can be changed on at a time.',
		'value' => 20,
		'peruser' => true,
	),
	array(
		'name' => 'collections_codes',
		'cname' => 'Collections Status Codes',
		'description' => 'Keep track of status of loans for collections. Uses this format: "Abbreviation:Short Description".',
		'value' => array(
			'PNB:PRIORITY NEW BUSINESS',
			'ACT:ACTIVE STANDARD ACCOUNT ',
			'OTR:OUT TO RAISE / LOOKING FOR MONEY',
			'HOT:VERY GOOD LEAD / PRIORITY',
			'PPP:PARTIAL PAYMENT PROMISED',
			'PTP:PROMISE TO PAY',
			'SIF:SETTLED IN FULL',
			'BRK:BROKEN PROMISE',
			'SKP:SKIP TRACE',
			'RTP:REFUSAL TO PAY',
		),
		'peruser' => true,
	),
);

?>