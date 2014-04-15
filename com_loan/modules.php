<?php
/**
 * com_loan's module definitions.
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
	'loancustsearch' => array(
		'cname' => 'Search for Loans',
		'description' => 'Search for a Loan.',
		'image' => 'includes/loan_search_screen.png',
		'view' => 'modules/loancustsearch',
		'type' => 'module widget',
		'widget' => array(
			'default' => false,
			'depends' => array(
				'ability' => 'com_loan/listloans&com_customer/listcustomers',
			),
		),
	),
);
?>