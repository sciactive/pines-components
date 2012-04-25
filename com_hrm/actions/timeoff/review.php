<?php
/**
 * Provide a list of time off requests.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/managerto') )
	punt_user(null, pines_url('com_hrm', 'timeoff/review'));

$pines->com_hrm->review_timeoff();
?>