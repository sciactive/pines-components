<?php
/**
 * com_packager's buttons.
 *
 * @package Components\packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'packages' => array(
		'description' => 'Package list.',
		'text' => 'Packages',
		'class' => 'picon-utilities-file-archiver',
		'href' => pines_url('com_packager', 'package/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_packager/listpackages',
		),
	),
	'package_new' => array(
		'description' => 'New package.',
		'text' => 'Package',
		'class' => 'picon-package-x-generic',
		'href' => pines_url('com_packager', 'package/edit'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_packager/newpackage',
		),
	),
);

?>