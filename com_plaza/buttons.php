<?php
/**
 * com_plaza's buttons.
 *
 * @package Components
 * @subpackage plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'getsoftware' => array(
		'description' => 'Get software.',
		'text' => 'Plaza',
		'class' => 'picon-repository',
		'href' => pines_url('com_plaza', 'package/repository'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_plaza/listpackages',
		),
	),
	'installed' => array(
		'description' => 'Installed software.',
		'text' => 'My Software',
		'class' => 'picon-applications-other',
		'href' => pines_url('com_plaza', 'package/list'),
		'default' => true,
		'depends' => array(
			'ability' => 'com_plaza/listpackages',
		),
	),
);

?>