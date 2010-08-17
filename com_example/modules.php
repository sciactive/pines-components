<?php
/**
 * com_example modules.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'example' => array(
		'cname' => 'Example Module',
		'view' => 'modules/example',
	),
	'example2' => array(
		'cname' => 'Widget Module',
		'view' => 'modules/widget',
		'form' => 'mod_forms/widget',
	),
);

?>