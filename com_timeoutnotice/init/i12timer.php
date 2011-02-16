<?php
/**
 * Check the timer.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper() && isset($_SESSION['com_timeoutnotice__last_access']) && (time() - $_SESSION['com_timeoutnotice__last_access'] >= $pines->config->com_timeoutnotice->timeout) ) {
	pines_notice('Your session has expired.');
	$pines->user_manager->logout();
}

?>