<?php
/**
 * Select a location.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper() )
	punt_user(null, pines_url('com_sales', 'forms/locationselect'));

$pines->com_sales->location_select_form($_REQUEST['location'], ($_REQUEST['descendants'] == 'true'));

?>