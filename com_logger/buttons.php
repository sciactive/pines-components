<?php
/**
 * com_logger's buttons.
 *
 * @package Components
 * @subpackage logger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'view' => array(
		'description' => 'View log.',
		'text' => 'Log',
		'class' => 'picon-utilities-log-viewer',
		'href' => pines_url('com_logger', 'view'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_logger/view',
		),
	),
	'rawlog' => array(
		'description' => 'View raw log.',
		'text' => 'Raw Log',
		'class' => 'picon-text-x-generic',
		'href' => pines_url('com_logger', 'rawlog'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_logger/view',
		),
	),
	'clear' => array(
		'description' => 'Clear log file.',
		'text' => 'Clear Log',
		'class' => 'picon-edit-clear-list',
		'href' => pines_url('com_logger', 'clear'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_logger/clear',
		),
	),
);

?>