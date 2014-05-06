<?php
/**
 * Check the user's remaining session time.
 * 
 * But do not go through inits. So this file is directly accessed.
 *
 * @package Components\timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>, Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */

header('Content-Type: application/json');
@session_start();
if (isset($_SESSION['com_timeoutnotice__last_access'])) {
	// Print the amount of time remaining in seconds.
	echo (json_encode((int) $_REQUEST['timeout'] - (time() - $_SESSION['com_timeoutnotice__last_access'])));
} else {
	echo 'false';
}
@session_write_close();
?>
