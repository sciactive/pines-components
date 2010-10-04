<?php
/**
 * com_plaza's configuration defaults.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'repositories',
		'cname' => 'Repositories',
		'description' => 'The repositories to get software. In order of precedence. (Packages from the first repository will override packages with the same name from others.)',
		'value' => array($pines->config->full_location),
	),
);

?>