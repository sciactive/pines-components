<?php
/**
 * com_pgrid's configuration defaults.
 *
 * @package Pines
 * @subpackage com_pgrid
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'styling',
		'cname' => 'Styling',
		'description' => 'The styling to use on the grid. "Touch" is much more touch screen friendly.',
		'value' => 'default',
		'options' => array(
			'Default' => 'default',
			'Touch' => 'touch'
		),
		'peruser' => true,
	),
	array(
		'name' => 'toolbar_target',
		'cname' => 'Default Toolbar Target',
		'description' => 'The default target for toolbar button links.',
		'value' => '_self',
		'options' => array(
			'Same Window' => '_self',
			'New Window' => '_blank'
		),
		'peruser' => true,
	),
);

?>