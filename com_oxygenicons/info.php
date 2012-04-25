<?php
/**
 * com_oxygenicons' information.
 *
 * @package Components\oxygenicons
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Oxygen Icon Theme',
	'author' => 'SciActive',
	'version' => '1.0.1dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('icons'),
	'short_description' => 'Pines Icon theme using Oxygen icons',
	'description' => 'A Pines Icon theme using the Oxygen icon library.',
	'depend' => array(
		'pines' => '<2'
	),
);

?>