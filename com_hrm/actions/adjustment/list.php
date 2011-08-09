<?php
/**
 * List adjustment types.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/listadjustments') )
	punt_user(null, pines_url('com_hrm', 'adjustment/list'));

$pines->com_hrm->list_adjustments();

?>