<?php
/**
 * List transfers.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/managestock') && !gatekeeper('com_sales/shipstock') )
	punt_user(null, pines_url('com_sales', 'transfer/list', array('finished' => $_REQUEST['finished'])));

$pines->com_sales->list_transfers($_REQUEST['finished'] == 'true', !gatekeeper('com_sales/managestock'));
?>