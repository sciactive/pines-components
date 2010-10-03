<?php
/**
 * com_packager's configuration defaults.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'global_packages',
		'cname' => 'Globalize Packages',
		'description' => 'Ensure that every user can access all packages by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'package_path',
		'cname' => 'Package Maker Path',
		'description' => 'The relative path of the directory where packages should be made. End this path with a slash!',
		'value' => $pines->config->upload_location.'packages/',
	),
);

?>