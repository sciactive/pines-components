<?php
/**
 * Select a start and end date.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/trackproducts') )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'listsales'));

$pines->page->override = true;
$pines->com_sales->location_select_form($_REQUEST['location']);

?>
