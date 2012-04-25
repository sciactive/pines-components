<?php
/**
 * Check the timer.
 *
 * @package Components
 * @subpackage timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper() ) {
	// Check for a custom config value.
	$calc_timeout = isset($_SESSION['com_timeoutnotice__timeout']) ? $_SESSION['com_timeoutnotice__timeout'] : $pines->config->com_timeoutnotice->timeout;
	if ( isset($_SESSION['com_timeoutnotice__last_access']) && (time() - $_SESSION['com_timeoutnotice__last_access'] >= $calc_timeout) ) {
		pines_notice('Your session has expired.');
		$pines->user_manager->logout();
	}
	unset($calc_timeout);
}

?>