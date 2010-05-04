<?php
/**
 * com_tangoicons's information.
 *
 * @package Pines
 * @subpackage com_tangoicons
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Tango Icon Theme',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'services' => array('icons'),
	'short_description' => 'Pines Icon theme using Tango icons',
	'description' => 'A Pines Icon theme using the Tango icon library.',
	'depend' => array(
		'pines' => '<2'
	),
);

?>