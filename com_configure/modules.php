<?php
/**
 * com_configure's modules.
 *
 * @package Components\configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'welcome' => array(
		'cname' => 'Welcome to Pines',
		'description' => 'A welcome message and a quick guide to setup.',
		'view' => 'modules/welcome',
		'type' => 'widget',
		'widget' => array(
			'default' => true,
			'depends' => array(
				'ability' => 'com_configure/edit',
			),
		),
	),
);

?>