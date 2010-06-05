<?php
/**
 * Select a location.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user('You don\'t have necessary permission.', pines_url('com_sales', 'locationselectform'));

$pines->com_sales->location_select_form($_REQUEST['location']);

?>
