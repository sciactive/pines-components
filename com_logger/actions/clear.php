<?php
/**
 * Clear the log.
 *
 * @package Components\logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_logger/clear') )
	punt_user(null, pines_url('com_logger', 'clear'));

if (file_put_contents($pines->config->com_logger->path, '') !== false) {
	pines_notice('Log file cleared.');
} else {
	pines_error('Error writing to log file.');
}

pines_redirect(pines_url('com_logger', 'view'));

?>