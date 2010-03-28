<?php
/**
 * com_example's configuration defaults.
 *
 * @package Pines
 * @subpackage com_example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'global_widgets',
		'cname' => 'Globalize Widgets',
		'description' => 'Ensure that every user can access all widgets by setting the "other" access control to read.',
		'value' => true,
	),
);

?>