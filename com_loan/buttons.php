<?php
/**
 * com_loan's button definitions.
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
	'loans' => array(
		'description' => 'Amortized Loans',
		'text' => 'Loans',
		'class' => 'picon-wallet-open',
		'href' => pines_url('com_loan', 'loan/list'),
		'depends' => array(
			'ability' => 'com_loan/listloans',
		),
	),
);
?>