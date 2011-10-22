<?php
/**
 * com_about's information.
 *
 * @package Pines
 * @subpackage com_about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'About',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Configurable about page',
	'description' => 'Displays configurable information about Pines and your installation.',
	'depend' => array(
		'pines' => '<2'
	),
	'recommend' => array(
		'component' => 'com_jquery'
	),
	'abilities' => array(
		array('show', 'About Page', 'User can see the about page.')
	),
);

?>