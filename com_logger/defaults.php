<?php
/**
 * com_logger's configuration defaults.
 *
 * @package Components\logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$log_file = realpath(sys_get_temp_dir()).'/pines.log';

return array(
	array(
		'name' => 'path',
		'cname' => 'File Path',
		'description' => 'The file to which logs will be written. This file can be a URL, as long as it can be opened for writing.',
		'value' => $log_file,
	),
	array(
		'name' => 'read_pattern',
		'cname' => 'Log Files Pattern',
		'description' => 'This pattern is used to aggregate rotated log files. Files can be text or gzip encoded. So to match normally rotated files you could use something like "/tmp/pines.log*".',
		'value' => $log_file,
	),
	array(
		'name' => 'read_include_path',
		'cname' => 'Include Log Path',
		'description' => 'If the pattern matches rotated log files, but not the current log file, set this to true to include the log file path with the pattern matches.',
		'value' => false,
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
		'peruser' => true,
	),
	array(
		'name' => 'date_format',
		'cname' => 'Date Format',
		'description' => 'The date format for the logs. If you change this, the log file view will probably not work correctly. See http://us2.php.net/manual/en/function.date.php',
		'value' => 'c',
		'peruser' => true,
	),
	array(
		'name' => 'log_notices',
		'cname' => 'Log Displayed Notices',
		'description' => 'Log the notices that are displayed to users.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'log_errors',
		'cname' => 'Log Displayed Errors',
		'description' => 'Log the errors that are displayed to users.',
		'value' => true,
		'peruser' => true,
	),
);

?>