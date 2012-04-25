<?php
/**
 * com_modules' buttons.
 *
 * @package Components\modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'modules' => array(
		'description' => 'Module list.',
		'text' => 'Modules',
		'class' => 'picon-view-file-columns',
		'href' => pines_url('com_modules', 'module/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_modules/listmodules',
		),
	),
	'module_new' => array(
		'description' => 'New module.',
		'text' => 'Module',
		'class' => 'picon-window-new',
		'href' => pines_url('com_modules', 'module/edit'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_modules/newmodule',
		),
	),
);

?>