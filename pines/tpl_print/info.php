<?php
/**
 * tpl_print's information.
 *
 * @package Pines
 * @subpackage tpl_print
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Print Template',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'services' => array('template'),
	'short_description' => 'Simple template suitable for printing',
	'description' => 'This template only shows the content modules. It\'s suitable for letting the user print the page without any excess information.',
);

?>