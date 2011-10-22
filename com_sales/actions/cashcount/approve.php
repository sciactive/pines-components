<?php
/**
 * Provide a form to close out a cash count.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper('com_sales/approvecashcount') )
	punt_user(null, pines_url('com_sales', 'cashcount/approve', array('id' => $_REQUEST['id'])));

if (!isset($_REQUEST['id'])) {
	pines_error('Requested cash count id is not accessible.');
	$pines->com_sales->list_cashcounts();
	return;
}
$entity = com_sales_cashcount::factory((int) $_REQUEST['id']);
if (!$entity->final) {
	pines_notice('This cash count has not been committed.');
	$pines->com_sales->list_cashcounts();
	return;
}
$entity->print_review();
?>