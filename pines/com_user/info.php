<?php
/**
 * com_user's information.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'User Manager',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'services' => array('user_manager', 'ability_manager'),
	'short_description' => 'Entity based user manager',
	'description' => 'Manages system users, groups, and abilities. Uses an entity manager as a storage backend.',
);

?>