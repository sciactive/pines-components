<?php
/**
 * com_sales' mails.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'sale_receipt' => array(
		'cname' => 'Sale Receipt',
		'description' => 'A sale receipt email.',
		'view' => 'mails/receipt',
		'has_recipient' => true,
		'unsubscribe' => false,
		'macros' => array(
			'receipt' => 'The receipt content.',
			'sale_id' => 'The sale ID.',
			'sale_total' => 'The sale total.',
		),
	),
);

?>