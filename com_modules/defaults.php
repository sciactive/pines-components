<?php
/**
 * com_modules' configuration defaults.
 *
 * @package Pines
 * @subpackage com_modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'global_modules',
		'cname' => 'Globalize Modules',
		'description' => 'Ensure that every user can access all modules by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'show_modules',
		'cname' => 'Show Modules',
		'description' => 'Whether to show configured modules. If false, none of the modules will be shown.',
		'value' => true,
		'peruser' => true,
	),
);

?>