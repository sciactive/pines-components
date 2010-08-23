<?php
/**
 * com_imodules' configuration defaults.
 *
 * @package Pines
 * @subpackage com_imodules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'parse_imodules',
		'cname' => 'Parse IModules',
		'description' => 'Whether to parse inline modules in content.',
		'value' => true,
		'peruser' => true,
	),
);

?>