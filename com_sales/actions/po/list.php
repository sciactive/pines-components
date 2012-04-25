<?php
/**
 * List POs.
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

if ( !gatekeeper('com_sales/listpos') )
	punt_user(null, pines_url('com_sales', 'po/list', array('finished' => $_REQUEST['finished'])));

$pines->com_sales->list_pos($_REQUEST['finished'] == 'true');
?>