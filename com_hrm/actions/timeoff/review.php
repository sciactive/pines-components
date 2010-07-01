<?php
/**
 * Provide a list of time off requests.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/managerto') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'timeoff/review'));

$pines->com_hrm->review_timeoff();
?>