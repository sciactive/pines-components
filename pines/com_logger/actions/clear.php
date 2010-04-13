<?php
/**
 * Clear the log.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_logger/clear') )
	punt_user('You don\'t have necessary permission.', pines_url('com_logger', 'clear'));

if (file_put_contents($pines->config->com_logger->path, '') !== false) {
	pines_notice('Log file cleared.');
} else {
	pines_error('Error writing to log file.');
}

action('com_logger', 'view');

?>