<?php
/**
 * Reset the timer.
 *
 * @package Components\timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( gatekeeper() ) {
	pines_session('write');
	if (
			($pines->request_component != 'com_timeoutnotice' || $pines->request_action != 'check') &&
			($pines->request_component != 'com_messenger' || $pines->request_action != 'xmpp_proxy')
		)
		$_SESSION['com_timeoutnotice__last_access'] = time();
	// This stores any custom config value.
	$_SESSION['com_timeoutnotice__timeout'] = $pines->config->com_timeoutnotice->timeout;
	pines_session('close');
}

?>