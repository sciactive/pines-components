<?php
/**
 * List employees' timeclocks.
 *
 * @package Components
 * @subpackage hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/viewclock') && !gatekeeper('com_hrm/manageclock') )
	punt_user(null, pines_url('com_hrm', 'employee/timeclock/list'));

$pines->com_hrm->list_timeclocks();
?>