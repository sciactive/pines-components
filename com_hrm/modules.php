<?php
/**
 * com_hrm's modules.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'clockin' => array(
		'cname' => 'Employee Clockin (Ability: com_hrm/clock)',
		'view' => 'employee/timeclock/clock',
	),
);

?>