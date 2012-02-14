<?php
/**
 * com_elfinder's buttons.
 *
 * @package Pines
 * @subpackage com_elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'file_manager' => array(
		'description' => 'File manager.',
		'text' => 'Files',
		'class' => 'picon-system-file-manager',
		'href' => pines_url('com_elfinder', 'finder'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_elfinder/finder|com_elfinder/finderself',
		),
	),
);

?>