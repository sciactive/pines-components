<?php
/**
 * Reset the timer.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper() && ($pines->request_component != 'com_timeoutnotice' || $pines->request_action != 'check') )
	$_SESSION['com_timeoutnotice__last_access'] = time();

?>