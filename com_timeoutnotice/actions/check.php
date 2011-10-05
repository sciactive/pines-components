<?php
/**
 * Check the user's remaining session time.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
header('Content-Type: application/json');

if (isset($_SESSION['com_timeoutnotice__last_access'])) {
	// Print the amount of time remaining in seconds.
	$pines->page->override_doc(json_encode($pines->config->com_timeoutnotice->timeout - (time() - $_SESSION['com_timeoutnotice__last_access'])));
} else {
	$pines->page->override_doc('false');
}

?>