<?php
/**
 * com_logger's configuration defaults.
 *
 * @package Pines
 * @subpackage com_logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'path',
		'cname' => 'File Path',
		'description' => 'The file to which logs will be written. This file can be a URL, as long as it can be opened for writing.',
		'value' => realpath(sys_get_temp_dir()).'/pines.log',
	),
	array(
		'name' => 'level',
		'cname' => 'Log Level',
		'description' => 'The level of logging. The most conservative is fatal. The least is debug. Choosing debug will cause a lot of things to be logged (like all method calls)!',
		'value' => 'notice',
		'options' => array(
			'debug',
			'info',
			'notice',
			'warning',
			'error',
			'fatal'
		),
	),
	array(
		'name' => 'date_format',
		'cname' => 'Date Format',
		'description' => 'The date format for the logs. See http://us2.php.net/manual/en/function.date.php',
		'value' => 'c',
	),
	array(
		'name' => 'log_notices',
		'cname' => 'Log Displayed Notices',
		'description' => 'Log the notices that are displayed to users.',
		'value' => false,
	),
	array(
		'name' => 'log_errors',
		'cname' => 'Log Displayed Errors',
		'description' => 'Log the errors that are displayed to users.',
		'value' => true,
	),
);

?>