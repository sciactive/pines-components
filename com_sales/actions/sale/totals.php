<?php
/**
 * Display sales total page.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/totalsales') )
	punt_user(null, pines_url('com_sales', 'sale/totals'));

$pines->com_sales->print_sales_total();
?>